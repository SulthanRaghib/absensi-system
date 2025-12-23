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

    protected function getViewData(): array
    {
        $user = Auth::user();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

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
        $cacheKey = "holidays:{$year}:{$month}";

        $holidays = Cache::remember($cacheKey, 60 * 24, function () use ($month, $year) {
            try {
                $url = "https://dayoffapi.vercel.app/api?month={$month}&year={$year}";
                $res = Http::timeout(5)->get($url);
                if ($res->successful()) {
                    $data = $res->json();
                    return is_array($data) ? $data : [];
                }
            } catch (\Throwable $e) {
                // ignore
            }
            return [];
        });

        // normalize holiday dates -> array of Y-m-d => name
        $holidayMap = [];
        foreach ($holidays as $h) {
            // handle if API returns strings or objects with different keys
            if (is_string($h)) {
                try {
                    $d = Carbon::parse($h)->toDateString();
                    $holidayMap[$d] = '';
                } catch (\Throwable $e) {
                }
            } elseif (is_array($h)) {
                // try multiple known keys from different APIs (including the example with 'tanggal')
                $date = $h['tanggal'] ?? $h['date'] ?? ($h['day'] ?? ($h['holidayDate'] ?? null));
                $name = $h['keterangan'] ?? $h['tanggal_display'] ?? $h['localName'] ?? ($h['name'] ?? ($h['description'] ?? ''));
                if ($date) {
                    try {
                        $d = Carbon::parse($date)->toDateString();
                        $holidayMap[$d] = $name ?: '';
                    } catch (\Throwable $e) {
                    }
                }
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
                // permissions
                $permFound = null;
                foreach ($permissions as $perm) {
                    // Carbon::between includes endpoints by default
                    if ($day->between($perm->start_date, $perm->end_date)) {
                        $permFound = $perm;
                        break;
                    }
                }

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
                            $jamMasuk = Carbon::parse($absence->jam_masuk->format('H:i:s'));
                            $threshold = Carbon::createFromTimeString('07:30:59');
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
                            // treat as Alpha only if in the past
                            if ($day->isPast()) {
                                $status = 'alpha';
                                $label = 'Alpha';
                                $color = 'black';
                                $emoji = '⚫';
                            }
                        }
                    } else {
                        // no record and past and not weekend/holiday => Alpha
                        if ($day->isPast()) {
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
