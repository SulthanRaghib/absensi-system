<?php

namespace App\Filament\User\Widgets;

use App\Models\Absence;
use App\Models\Setting;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class UserAttendanceInfoWidget extends BaseWidget
{
    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $user = Auth::user();
        $userId = $user->id;

        $today = Carbon::today();
        $todayAbsence = Absence::getTodayAbsence($userId);

        // Define work schedule
        $workStart = Carbon::createFromTimeString('07:30:00');
        $workEnd = $today->isFriday() ? Carbon::createFromTimeString('16:30:00') : Carbon::createFromTimeString('16:00:00');

        // Monthly window
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        // Gather monthly records for user
        $monthlyRecords = Absence::where('user_id', $userId)
            ->whereBetween('tanggal', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->get();

        $monthlyAbsences = $monthlyRecords->whereNotNull('jam_masuk')->count();
        $monthlyComplete = $monthlyRecords->filter(fn($r) => $r->jam_masuk && $r->jam_pulang)->count();

        // Count late arrivals (jam_masuk later than workStart + grace)
        $graceMinutes = Setting::get('attendance_grace_minutes', 10);
        $lateCount = 0;
        foreach ($monthlyRecords as $rec) {
            if ($rec->jam_masuk) {
                // jam_masuk cast may be datetime — normalize to time
                $jm = Carbon::parse($rec->jam_masuk->format('H:i:s'));
                if ($jm->gt($workStart->copy()->addMinutes($graceMinutes))) {
                    $lateCount++;
                }
            }
        }

        // Compute work days in current month (exclude weekends)
        $workDaysCount = 0;
        for ($d = $startOfMonth->copy(); $d->lte($endOfMonth); $d->addDay()) {
            if (!in_array($d->dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY])) {
                $workDaysCount++;
            }
        }

        $percentComplete = $workDaysCount > 0 ? round(($monthlyComplete / $workDaysCount) * 100) : 0;

        // Today's status
        $todayStatus = 'Belum Absen';
        $todayColor = 'danger';
        $todayIcon = 'heroicon-m-x-circle';
        $todayDesc = 'Silakan lakukan absensi masuk';

        if ($todayAbsence) {
            if ($todayAbsence->jam_pulang) {
                $todayStatus = 'Sudah Lengkap';
                $todayColor = 'success';
                $todayIcon = 'heroicon-m-check-badge';
                $todayDesc = 'Masuk: ' . ($todayAbsence->jam_masuk?->format('H:i') ?? '-') . ' — Pulang: ' . ($todayAbsence->jam_pulang?->format('H:i') ?? '-');
            } elseif ($todayAbsence->jam_masuk) {
                // Check punctuality
                $arrivedAt = Carbon::parse($todayAbsence->jam_masuk->format('H:i:s'));
                // For today's status, do NOT apply grace minutes — arriving at 07:30 is considered late
                $isLate = $arrivedAt->gte($workStart);
                $todayStatus = $isLate ? 'Telat' : 'Sudah Check In';
                $todayColor = $isLate ? 'warning' : 'success';
                $todayIcon = $isLate ? 'heroicon-m-clock' : 'heroicon-m-check-circle';
                $todayDesc = 'Masuk: ' . $arrivedAt->format('H:i') . ($isLate ? ' (Telat)' : '');
            }
        }

        // Small sparkline: [present, complete, late]
        $chartData = [$monthlyAbsences, $monthlyComplete, $lateCount];

        return [
            Stat::make('Status Hari Ini', $todayStatus)
                ->description($todayDesc)
                ->descriptionIcon($todayIcon)
                ->color($todayColor)
                ->icon($todayIcon),

            Stat::make('Jadwal Kerja', $workStart->format('H:i') . ' — ' . $workEnd->format('H:i'))
                ->description('Jam masuk & pulang untuk hari kerja')
                ->descriptionIcon('heroicon-m-clock')
                ->color('primary'),

            Stat::make('Kehadiran Bulan Ini', "$monthlyAbsences / $workDaysCount hari")
                ->description("Lengkap: $monthlyComplete — Telat: $lateCount")
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color($percentComplete >= 80 ? 'success' : ($percentComplete >= 50 ? 'warning' : 'danger'))
                ->chart($chartData),

            Stat::make('Kinerja Hadir', $percentComplete . '%')
                ->description('Persentase absen lengkap di bulan ini')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
        ];
    }
}
