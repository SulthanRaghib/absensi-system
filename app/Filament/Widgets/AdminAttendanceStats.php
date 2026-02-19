<?php

namespace App\Filament\Widgets;

use App\Models\Absence;
use App\Models\User;
use App\Services\AttendanceService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class AdminAttendanceStats extends BaseWidget
{
    protected static ?int $sort = 1;

    protected ?string $pollingInterval = null;

    // make it full width (dashboard grid is 2 columns)
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        return Cache::remember('admin_attendance_stats', 60, function () {
            $schedule     = (new AttendanceService)->getTodaySchedule();
            $threshold    = $schedule['jam_masuk']; // Ramadan-aware

            $totalUsers   = User::count();
            $today        = now()->toDateString();
            $presentToday = Absence::whereDate('tanggal', $today)->whereNotNull('jam_masuk')->count();

            // On-time: present AND not late
            $onTimeToday = Absence::whereDate('tanggal', $today)
                ->whereNotNull('jam_masuk')
                ->get()
                ->filter(function (Absence $r) use ($threshold) {
                    $t = $r->schedule_jam_masuk ?? $threshold;
                    return optional($r->jam_masuk)->format('H:i') <= $t;
                })
                ->count();

            // On-time chart: last 7 days
            $onTimeChart = [];
            for ($i = 6; $i >= 0; $i--) {
                $d = now()->subDays($i);
                $dayKey  = $d->toDateString();
                // Use today's threshold as a best-effort approximation for chart history
                $onTimeChart[$d->format('d M')] = Absence::whereDate('tanggal', $dayKey)
                    ->whereNotNull('jam_masuk')
                    ->whereRaw("TIME(jam_masuk) <= ?", [$threshold . ':00'])
                    ->count();
            }

            // chart data: last 7 days present counts
            $chart = [];
            for ($i = 6; $i >= 0; $i--) {
                $d = now()->subDays($i);
                $chart[$d->format('d M')] = Absence::whereDate('tanggal', $d->toDateString())->whereNotNull('jam_masuk')->count();
            }

            $totalMentors   = User::whereHas('jabatan', fn($q) => $q->where('name', 'like', '%mentor%'))->count();
            $totalRoleUsers = User::where('role', 'user')->count();
            $absentRoleUsers = User::where('role', 'user')
                ->whereDoesntHave('absences', fn($q) => $q->whereDate('tanggal', $today)->whereNotNull('jam_masuk'))
                ->count();

            return [
                Stat::make('Total Pengguna', $totalUsers)
                    ->description('Jumlah pengguna terdaftar')
                    ->icon('heroicon-o-users')
                    ->color('primary'),

                Stat::make('Total Mentor', $totalMentors)
                    ->description('Jumlah yang berjabatan mentor')
                    ->icon('heroicon-o-user-group')
                    ->color('secondary'),

                Stat::make('Total Peserta Magang', $totalRoleUsers)
                    ->description('Jumlah peserta Magang')
                    ->icon('heroicon-o-user')
                    ->color('gray'),

                Stat::make('Hadir Hari Ini', $presentToday)
                    ->description($totalUsers ? round(($presentToday / $totalUsers) * 100) . '% hadir' : '0%')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->chart($chart),

                Stat::make('Tidak Hadir (Peserta)', $absentRoleUsers)
                    ->description($totalRoleUsers ? round(($absentRoleUsers / $totalRoleUsers) * 100) . '% tidak hadir' : '0%')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger'),

                Stat::make('Tepat Waktu Hari Ini', $onTimeToday)
                    ->description($presentToday
                        ? round(($onTimeToday / $presentToday) * 100) . '% dari yang hadir'
                        : 'Belum ada kehadiran')
                    ->descriptionIcon($onTimeToday > 0 ? 'heroicon-m-check-badge' : 'heroicon-m-minus-circle')
                    ->icon('heroicon-o-bolt')
                    ->color($presentToday && ($onTimeToday / $presentToday) >= 0.8 ? 'success' : 'warning')
                    ->chart($onTimeChart),
            ];
        });
    }
}
