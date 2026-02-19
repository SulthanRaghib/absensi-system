<?php

namespace App\Filament\Widgets;

use App\Models\Setting;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Filament\Widgets\Widget;

class AdminScheduleInfoWidget extends Widget
{
    protected static ?int $sort = 0; // renders first on admin dashboard

    protected int | string | array $columnSpan = 12;

    protected ?string $pollingInterval = null;

    protected string $view = 'filament.widgets.admin-schedule-info-widget';

    protected function getViewData(): array
    {
        $service  = new AttendanceService;
        $schedule = $service->getTodaySchedule();

        $default  = Setting::getDefaultSchedule();      // jam_masuk, jam_pulang, jam_pulang_jumat
        $ramadan  = Setting::getRamadanSettings();      // start_date, end_date, jam_masuk, jam_pulang

        $now      = Carbon::now();
        $today    = $now->copy()->startOfDay();

        // --- Ramadan progress info ---
        $daysRemaining = null;
        $daysTotal     = null;
        $ramadanActive = $schedule['is_ramadan'];

        if (
            $ramadan['start_date'] instanceof Carbon &&
            $ramadan['end_date'] instanceof Carbon
        ) {
            $daysTotal     = (int) $ramadan['start_date']->diffInDays($ramadan['end_date']) + 1;
            $daysRemaining = $ramadanActive
                ? max(0, (int) $today->diffInDays($ramadan['end_date']->copy()->endOfDay()) + 1)
                : null;
        }

        // --- Status label for current time vs schedule ---
        $jamMasukCarbon  = Carbon::createFromFormat('H:i', $schedule['jam_masuk']);
        $jamPulangCarbon = Carbon::createFromFormat('H:i', $schedule['jam_pulang']);

        if ($now->lt($jamMasukCarbon)) {
            $statusKey   = 'before';
            $statusLabel = 'Belum Jam Masuk';
            $statusColor = 'blue';
        } elseif ($now->gt($jamPulangCarbon)) {
            $statusKey   = 'after';
            $statusLabel = 'Sudah Jam Pulang';
            $statusColor = 'gray';
        } else {
            $statusKey   = 'working';
            $statusLabel = 'Jam Kerja Aktif';
            $statusColor = 'green';
        }

        // Friday pulang
        $isFriday        = $now->isFriday();
        $jamPulangDisplay = $isFriday
            ? ($ramadanActive ? $schedule['jam_pulang'] : $default['jam_pulang_jumat'])
            : $schedule['jam_pulang'];

        return [
            'schedule'          => $schedule,
            'default'           => $default,
            'ramadan'           => $ramadan,
            'ramadanActive'     => $ramadanActive,
            'now'               => $now,
            'isFriday'          => $isFriday,
            'jamPulangDisplay'  => $jamPulangDisplay,
            'daysRemaining'     => $daysRemaining,
            'daysTotal'         => $daysTotal,
            'statusKey'         => $statusKey,
            'statusLabel'       => $statusLabel,
            'statusColor'       => $statusColor,
            'hariBulan'         => $now->translatedFormat('l, d F Y'),
        ];
    }
}
