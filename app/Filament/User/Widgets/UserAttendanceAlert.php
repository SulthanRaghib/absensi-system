<?php

namespace App\Filament\User\Widgets;

use App\Models\Absence;
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
        // 1. Check if it is a weekday (Monday=1 to Friday=5)
        if (now()->isWeekend()) {
            return false;
        }

        // 2. Check if user has NO Absence record for today
        $hasAbsenceToday = Absence::where('user_id', Auth::id())
            ->whereDate('tanggal', now()->toDateString())
            ->exists();

        return ! $hasAbsenceToday;
    }
}
