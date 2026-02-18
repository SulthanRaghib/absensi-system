<?php

namespace App\Filament\User\Widgets;

use App\Models\Absence;
use App\Models\Permission;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class AttendanceCalendarWidget extends Widget
{
    protected static ?string $heading = 'Kalender Kehadiran';

    protected string $view = 'filament.user.widgets.attendance-calendar-widget';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 10;

    public $selectedMonth;
    public $selectedYear;

    public function mount()
    {
        $this->selectedMonth = now()->month;
        $this->selectedYear = now()->year;
    }

    public function updatedSelectedMonth()
    {
        $this->dispatch('calendar-updated');
    }

    public function updatedSelectedYear()
    {
        $this->dispatch('calendar-updated');
    }

    protected function getViewData(): array
    {
        $user = Auth::user();

        $selectedDate = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1);
        $startOfMonth = $selectedDate->copy()->startOfMonth();
        $endOfMonth = $selectedDate->copy()->endOfMonth();

        // Fetch Absences for the month
        $absences = Absence::where('user_id', $user->id)
            ->whereBetween('tanggal', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->get()
            ->keyBy(fn($a) => $a->tanggal->toDateString());

        // Fetch approved permissions overlapping the month
        $permissions = Permission::where('user_id', $user->id)
            ->where('status', 'approved')
            ->where(function ($q) use ($startOfMonth, $endOfMonth) {
                $q->whereBetween('start_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
                    ->orWhereBetween('end_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
                    ->orWhere(function ($qq) use ($startOfMonth, $endOfMonth) {
                        $qq->where('start_date', '<=', $startOfMonth->toDateString())
                            ->where('end_date', '>=', $endOfMonth->toDateString());
                    });
            })
            ->get();

        // Holidays from external API (cached 24 hours)
        $month = $startOfMonth->month;
        $year = $startOfMonth->year;

        // Fetch holiday data from centralized service
        // Returns associative array ['YYYY-MM-DD' => 'Holiday Name']
        $holidayMap = (new \App\Services\HolidayService)->getHolidays($year, $month);

        // Pre-calculate Permissions Map (Optimized O(1) Lookup)
        $permissionDays = [];
        foreach ($permissions as $perm) {
            $pStart = Carbon::parse($perm->start_date);
            $pEnd = Carbon::parse($perm->end_date);

            // Generate all dates in the range
            $period = CarbonPeriod::create($pStart, $pEnd);
            foreach ($period as $date) {
                // Key by Y-m-d for instant lookup
                $permissionDays[$date->toDateString()] = $perm;
            }
        }

        // Build day map for current month
        $period = CarbonPeriod::create($startOfMonth, $endOfMonth);
        $days = [];

        foreach ($period as $day) {
            $key = $day->toDateString();
            $isHoliday = array_key_exists($key, $holidayMap);
            $isWeekend = $day->isWeekend();

            $status = 'none';
            $label = '';
            $color = 'gray-200';
            $emoji = '';

            if ($isHoliday) {
                $status = 'holiday';
                $label = $holidayMap[$key] ?: 'Libur Nasional';
                $color = 'red-100';
                $emoji = '🎉';
            } elseif ($isWeekend) {
                $status = 'weekend';
                $label = 'Akhir Pekan';
                $color = 'gray-100';
                $emoji = '⛱️';
            } else {
                // Check Permission via Lookup (O(1))
                $permFound = $permissionDays[$key] ?? null;

                if ($permFound) {
                    $status = 'permission';
                    $type = strtolower($permFound->type ?? 'izin');
                    $label = ucfirst($type);
                    $color = 'yellow-100';
                    $emoji = '🟡';
                } else {
                    $absence = $absences[$key] ?? null;
                    if ($absence) {
                        if ($absence->jam_masuk) {
                            // Already cast to Carbon via Model $casts
                            $jamMasuk = $absence->jam_masuk;
                            // 07:30:59 Threshold
                            $threshold = $jamMasuk->copy()->setTime(7, 30, 59);

                            if ($jamMasuk->gt($threshold)) {
                                $status = 'late';
                                $label = 'Telat ' . $jamMasuk->format('H:i');
                                $color = 'red-200';
                                $emoji = '🔴';
                            } else {
                                $status = 'on_time';
                                $label = 'Hadir ' . $jamMasuk->format('H:i');
                                $color = 'green-200';
                                $emoji = '🟢';
                            }
                        } else {
                            // if there is an absence record but no jam_masuk
                            // for today show 'Belum Absen', otherwise mark as Alpha if in the past
                            if ($day->isToday()) {
                                $status = 'not_checked_in';
                                $label = 'Belum Absen';
                                $color = 'indigo-100';
                                $emoji = '⏳';
                            } elseif ($day->isPast()) {
                                $status = 'alpha';
                                $label = 'Alpha';
                                $color = 'black';
                                $emoji = '⚫';
                            }
                        }
                    } else {
                        // no record: if today -> not_checked_in; if past -> alpha
                        if ($day->isToday()) {
                            $status = 'not_checked_in';
                            $label = 'Belum Absen';
                            $color = 'indigo-100';
                            $emoji = '⏳';
                        } elseif ($day->isPast()) {
                            $status = 'alpha';
                            $label = 'Alpha';
                            $color = 'black';
                            $emoji = '⚫';
                        }
                    }
                }
            }

            $days[] = [
                'date' => $day->copy(),
                'key' => $key,
                'status' => $status,
                'label' => $label,
                'color' => $color,
                'emoji' => $emoji,
                'is_today' => $day->isToday(),
                'is_holiday' => $isHoliday,
            ];
        }

        return [
            'monthName' => $startOfMonth->locale('id')->isoFormat('MMMM'),
            'year' => $startOfMonth->year,
            'days' => $days,
            'startOfMonth' => $startOfMonth,
        ];
    }
}
