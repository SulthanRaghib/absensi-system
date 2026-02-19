<?php

namespace App\Filament\User\Widgets;

use App\Models\Absence;
use App\Models\Setting;
use App\Services\AttendanceService;
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

        // Use AttendanceService for dynamic schedule (Ramadan-aware)
        $attendanceService = new AttendanceService();
        $todaySchedule = $attendanceService->getTodaySchedule();
        $isRamadan = $todaySchedule['is_ramadan'];

        $workStart = Carbon::createFromFormat('H:i', $todaySchedule['jam_masuk']);
        $workEnd   = Carbon::createFromFormat('H:i', $todaySchedule['jam_pulang']);

        // Monthly window
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        // Gather monthly records for user
        $monthlyRecords = Absence::where('user_id', $userId)
            ->whereBetween('tanggal', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->get();

        $monthlyAbsences = $monthlyRecords->whereNotNull('jam_masuk')->count();
        $monthlyComplete = $monthlyRecords->filter(fn($r) => $r->jam_masuk && $r->jam_pulang)->count();

        // Count late arrivals using per-record threshold (Ramadan-safe)
        $graceMinutes = Setting::get('attendance_grace_minutes', 10);
        $lateCount = 0;
        foreach ($monthlyRecords as $rec) {
            if ($rec->jam_masuk) {
                $jm = Carbon::parse($rec->jam_masuk->format('H:i:s'));
                // Prefer the threshold that was active at check-in (immune to future setting changes).
                // Fall back to today's schedule only for legacy records that pre-date this feature.
                $threshold = $rec->schedule_jam_masuk
                    ? Carbon::createFromFormat('H:i', $rec->schedule_jam_masuk)
                    : $workStart;
                if ($jm->gt($threshold->copy()->addMinutes($graceMinutes))) {
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

        // Check for holidays
        $holidayService = new \App\Services\HolidayService();
        $holidays = $holidayService->getHolidays($today->year, $today->month);
        $todayHoliday = $holidays[$today->toDateString()] ?? null;

        if ($todayAbsence) {
            // Priority: Show attendance if checked in
            if ($todayAbsence->jam_pulang) {
                $todayStatus = 'Sudah Lengkap';
                $todayColor = 'success';
                $todayIcon = 'heroicon-m-check-badge';
                $todayDesc = 'Masuk: ' . ($todayAbsence->jam_masuk?->format('H:i') ?? '-') . ' — Pulang: ' . ($todayAbsence->jam_pulang?->format('H:i') ?? '-');
            } elseif ($todayAbsence->jam_masuk) {
                // Check punctuality
                $arrivedAt = Carbon::parse($todayAbsence->jam_masuk->format('H:i:s'));
                $isLate = $arrivedAt->gte($workStart);
                $todayStatus = $isLate ? 'Telat' : 'Sudah Check In';
                $todayColor = $isLate ? 'warning' : 'success';
                $todayIcon = $isLate ? 'heroicon-m-clock' : 'heroicon-m-check-circle';
                $todayDesc = 'Masuk: ' . $arrivedAt->format('H:i') . ($isLate ? ' (Telat)' : '');
            }
        } elseif ($todayHoliday) {
            // Holiday Status (only if no attendance record)
            $todayStatus = 'Libur Nasional';
            $todayColor = 'primary';
            $todayIcon = 'heroicon-m-sparkles'; // Or calendar icon
            $todayDesc = $todayHoliday;
        } else {
            // Not holiday, not absent
            $todayStatus = 'Belum Absen';
            $todayColor = 'danger';
            $todayIcon = 'heroicon-m-x-circle';
            $todayDesc = 'Silakan lakukan absensi masuk';
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
                ->description($isRamadan ? '🌙 Jadwal khusus Ramadan aktif' : 'Jam masuk & pulang untuk hari kerja')
                ->descriptionIcon($isRamadan ? 'heroicon-m-moon' : 'heroicon-m-clock')
                ->color($isRamadan ? 'warning' : 'primary'),

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
