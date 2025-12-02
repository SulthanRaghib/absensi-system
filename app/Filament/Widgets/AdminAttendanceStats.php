<?php

namespace App\Filament\Widgets;

use App\Models\Absence;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Setting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class AdminAttendanceStats extends BaseWidget
{
    protected static ?int $sort = -1;

    protected ?string $pollingInterval = null;

    // make it full width (12 cols)
    protected int | string | array $columnSpan = 12;

    protected function getStats(): array
    {
        return Cache::remember('admin_attendance_stats', 60, function () {
            $totalUsers = User::count();
            $today = now()->toDateString();
            $presentToday = Absence::whereDate('tanggal', $today)->whereNotNull('jam_masuk')->count();
            $absent = max(0, $totalUsers - $presentToday);

            $workStart = Carbon::createFromTimeString('07:30:00');
            $graceMinutes = Setting::get('attendance_grace_minutes', 10);

            $lateRecords = Absence::with('user')
                ->whereDate('tanggal', $today)
                ->whereNotNull('jam_masuk')
                ->get()
                ->filter(function ($r) use ($workStart, $graceMinutes) {
                    if (! $r->jam_masuk) return false;
                    $jm = Carbon::parse($r->jam_masuk->format('H:i:s'));
                    return $jm->gt($workStart->copy()->addMinutes($graceMinutes));
                });

            $lateCount = $lateRecords->count();
            $lateNames = $lateRecords->pluck('user.name')->filter()->unique()->values()->all();
            $latePreview = $lateNames ? implode(', ', array_slice($lateNames, 0, 5)) . (count($lateNames) > 5 ? '...' : '') : '-';

            // chart data: last 7 days present counts (associative so labels are preserved)
            $start = now()->subDays(6)->startOfDay();
            $chart = [];
            for ($i = 0; $i < 7; $i++) {
                $d = $start->copy()->addDays($i);
                $label = $d->format('d M'); // e.g. "20 Nov"
                $chart[$label] = Absence::whereDate('tanggal', $d->toDateString())->whereNotNull('jam_masuk')->count();
            }

            // additional counts: mentors (jabatan) and users by role
            $totalMentors = User::whereHas('jabatan', function ($query) {
                $query->where('name', 'like', '%mentor%');
            })->count();
            $totalRoleUsers = User::where('role', 'user')->count();

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

                Stat::make('Tidak Hadir', $absent)
                    ->description($totalUsers ? round(($absent / $totalUsers) * 100) . '% tidak hadir' : '0%')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger'),

                // Stat::make('Karyawan Telat', $lateCount)
                //     ->description($latePreview)
                //     ->icon('heroicon-o-clock')
                //     ->color($lateCount > 0 ? 'warning' : 'success'),
            ];
        });
    }
}
