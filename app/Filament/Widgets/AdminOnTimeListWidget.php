<?php

namespace App\Filament\Widgets;

use App\Models\Absence;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class AdminOnTimeListWidget extends Widget
{
    protected static ?int $sort = 2;

    protected ?string $pollingInterval = null;

    /** @var view-string */
    protected string $view = 'filament.widgets.admin-on-time-list';

    // one-third width in 3-column dashboard grid
    protected int | string | array $columnSpan = 1;

    public function getOnTimeRecords(): Collection
    {
        $today    = now()->toDateString();
        $schedule = (new AttendanceService)->getTodaySchedule();
        $threshold = $schedule['jam_masuk']; // 'HH:MM'

        $records = Absence::with('user')
            ->whereDate('tanggal', $today)
            ->whereNotNull('jam_masuk')
            ->get()
            ->filter(function (Absence $r) use ($threshold) {
                if (! $r->jam_masuk) return false;
                // Check if employee arrived on time or early
                $recordThreshold = $r->schedule_jam_masuk ?? $threshold;
                return $r->jam_masuk->format('H:i') <= $recordThreshold;
            })
            ->map(function (Absence $r) use ($threshold) {
                $recordThreshold = $r->schedule_jam_masuk ?? $threshold;
                [$th_h, $th_m] = explode(':', $recordThreshold);
                $timeStr = optional($r->jam_masuk)?->format('H:i') ?? '-';
                $diffMin = 0;
                if ($timeStr !== '-') {
                    [$jm_h, $jm_m] = explode(':', $timeStr);
                    // Calculate difference (negative = early, 0 = exactly on time)
                    $diffMin = (int)$jm_h * 60 + (int)$jm_m - ((int)$th_h * 60 + (int)$th_m);
                }

                // Use capture_image from today's attendance record
                $avatarUrl = $r->capture_image
                    ? Storage::url($r->capture_image)
                    : null;

                return (object) [
                    'name'       => optional($r->user)->name ?? '—',
                    'avatar'     => $avatarUrl,
                    'time'       => $timeStr,
                    'is_ramadan' => (bool) $r->is_ramadan,
                    'threshold'  => $recordThreshold,
                    'diff_min'   => $diffMin, // Can be negative (early) or 0 (exactly on time)
                ];
            })
            ->sortBy('diff_min'); // Sort by earliest first

        return $records->values();
    }

    public function getScheduleInfo(): array
    {
        return (new AttendanceService)->getTodaySchedule();
    }
}
