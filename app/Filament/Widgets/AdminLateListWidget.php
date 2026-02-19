<?php

namespace App\Filament\Widgets;

use App\Models\Absence;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class AdminLateListWidget extends Widget
{
    protected static ?int $sort = 2;

    protected ?string $pollingInterval = null;

    /** @var view-string */
    protected string $view = 'filament.widgets.admin-late-list';

    // half width in 2-column dashboard grid
    protected int | string | array $columnSpan = 1;

    public function getLateRecords(): Collection
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
                // Prefer the snapshotted per-record threshold (already stored at check-in).
                // For today's records it equals $threshold; for legacy records it may differ.
                $recordThreshold = $r->schedule_jam_masuk ?? $threshold;
                return $r->jam_masuk->format('H:i') > $recordThreshold;
            })
            ->map(fn($r) => (object) [
                'name'       => optional($r->user)->name ?? '—',
                'time'       => optional($r->jam_masuk)?->format('H:i') ?? '-',
                'is_ramadan' => $r->is_ramadan,
                'threshold'  => $r->schedule_jam_masuk ?? $threshold,
            ]);

        return $records->values();
    }

    public function getScheduleInfo(): array
    {
        return (new AttendanceService)->getTodaySchedule();
    }
}
