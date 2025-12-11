<?php

namespace App\Filament\Widgets;

use App\Models\Absence;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class AdminLateListWidget extends Widget
{
    protected static ?int $sort = 4;

    protected ?string $pollingInterval = null;

    /** @var view-string */
    protected string $view = 'filament.widgets.admin-late-list';

    // full width
    protected int | string | array $columnSpan = 6;

    public function getLateRecords(): Collection
    {
        $today = now()->toDateString();

        // Work schedule
        $workStart = Carbon::createFromTimeString('07:30:00');
        // Grace minutes removed
        // $graceMinutes = config('filament.widgets.attendance_grace_minutes') ?? 10;

        $records = Absence::with('user')
            ->whereDate('tanggal', $today)
            ->whereNotNull('jam_masuk')
            ->get()
            ->filter(function ($r) use ($workStart) {
                if (! $r->jam_masuk) return false;
                $jm = Carbon::parse($r->jam_masuk->format('H:i:s'));
                return $jm->gt($workStart);
            })
            ->map(fn($r) => (object) [
                'name' => optional($r->user)->name ?? '—',
                'time' => optional($r->jam_masuk)?->format('H:i') ?? '-',
            ]);

        return $records->values();
    }
}
