<?php

namespace App\Filament\User\Widgets;

use App\Models\Absence;
use App\Services\AttendanceService;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class UserAttendanceAlert extends Widget
{
    protected static ?string $heading = 'Peringatan Absensi';

    protected string $view = 'filament.user.widgets.user-attendance-alert';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = -1;

    public static function canView(): bool
    {
        // Only simple checks here to prevent blocking.
        // Holiday logic moved to view/render.

        // 1. Check if it is a weekday (Monday=1 to Friday=5)
        if (now()->isWeekend()) {
            return false;
        }

        // 2. Check if user has NO Absence record for today
        // This is a fast DB query on indexed column, acceptable.
        $hasAbsenceToday = Absence::where('user_id', Auth::id())
            ->whereDate('tanggal', now()->toDateString())
            ->exists();

        return ! $hasAbsenceToday;
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        // Use default render logic but ensure data is fetched safely
        return view($this->view, $this->getViewData());
    }

    protected function getViewData(): array
    {
        $holidayService = new \App\Services\HolidayService();
        $holidays = $holidayService->getHolidays(now()->year, now()->month);

        $schedule  = (new AttendanceService)->getTodaySchedule();

        return [
            'isHoliday'  => isset($holidays[now()->toDateString()]),
            'isRamadan'  => $schedule['is_ramadan'],
            'jamMasuk'   => $schedule['jam_masuk'],
        ];
    }
}
