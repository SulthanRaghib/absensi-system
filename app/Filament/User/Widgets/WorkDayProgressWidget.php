<?php

namespace App\Filament\User\Widgets;

use App\Services\AttendanceService;
use Carbon\Carbon;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class WorkDayProgressWidget extends Widget
{
    protected static ?string $heading = 'Progres Hari Kerja';

    protected string $view = 'filament.user.widgets.work-day-progress-widget';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 0;

    protected function getViewData(): array
    {
        $today = Carbon::today();
        $user  = Auth::user();

        // Check for holidays
        $holidayService = new \App\Services\HolidayService();
        $holidays       = $holidayService->getHolidays($today->year, $today->month);
        $todayHoliday   = $holidays[$today->toDateString()] ?? null;

        // Get Ramadan-aware work schedule
        $schedule  = (new AttendanceService)->getTodaySchedule();
        $isRamadan = $schedule['is_ramadan'];

        $workStart = Carbon::createFromFormat('H:i', $schedule['jam_masuk'])
            ->setDate($today->year, $today->month, $today->day);
        $workEnd   = Carbon::createFromFormat('H:i', $schedule['jam_pulang'])
            ->setDate($today->year, $today->month, $today->day);

        // Get today's attendance
        $attendance = \App\Models\Absence::where('user_id', $user->id)
            ->whereDate('tanggal', $today)
            ->first();

        $clockIn  = $attendance?->jam_masuk  ? Carbon::parse($attendance->jam_masuk)  : null;
        $clockOut = $attendance?->jam_pulang ? Carbon::parse($attendance->jam_pulang) : null;

        return [
            'start'        => $workStart,
            'end'          => $workEnd,
            'startIso'     => $workStart->toIso8601String(),
            'endIso'       => $workEnd->toIso8601String(),
            'clockInIso'   => $clockIn?->toIso8601String(),
            'clockOutIso'  => $clockOut?->toIso8601String(),
            'isCheckedIn'  => $clockIn  !== null,
            'isCheckedOut' => $clockOut !== null,
            'holiday'      => $todayHoliday,
            'isRamadan'    => $isRamadan,
        ];
    }
}
