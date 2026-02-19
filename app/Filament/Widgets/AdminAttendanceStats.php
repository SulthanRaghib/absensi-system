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

    // make it full width (12 cols)
    protected int | string | array $columnSpan = 12;

    protected function getStats(): array
    {
        return Cache::remember('admin_attendance_stats', 60, function () {
            $schedule   = (new AttendanceService)->getTodaySchedule();
            $threshold  = $schedule['jam_masuk']; // 'HH:MM' — Ramadan-aware

            $totalUsers   = User::count();
            $today        = now()->toDateString();
            $presentToday = Absence::whereDate('tanggal', $today)->whereNotNull('jam_masuk')->count();
            $absent       = max(0, $totalUsers - $presentToday);

            // Late: use per-record threshold if available (immune to setting changes)
            $lateRecords = Absence::with('user')
                ->whereDate('tanggal', $today)
                ->whereNotNull('jam_masuk')
                ->get()
                ->filter(function (Absence $r) use ($threshold) {
                    if (! $r->jam_masuk) return false;
                    $recordThreshold = $r->schedule_jam_masuk ?? $threshold;
                    return $r->jam_masuk->format('H:i') > $recordThreshold;
                });

            $lateCount   = $lateRecords->count();
            $lateNames   = $lateRecords->pluck('user.name')->filter()->unique()->values()->all();
            $latePreview = $lateNames
                ? implode(', ', array_slice($lateNames, 0, 3)) . (count($lateNames) > 3 ? '...' : '')
                : '-';

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

                Stat::make('Terlambat Hari Ini', $lateCount)
                    ->description($lateCount > 0 ? $latePreview : 'Semua tepat waktu')
                    ->descriptionIcon($lateCount > 0 ? 'heroicon-m-clock' : 'heroicon-m-check-badge')
                    ->icon('heroicon-o-clock')
                    ->color($lateCount > 0 ? 'warning' : 'success'),
            ];
        });
    }
}
