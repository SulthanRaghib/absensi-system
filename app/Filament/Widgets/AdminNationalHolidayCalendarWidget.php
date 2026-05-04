<?php

namespace App\Filament\Widgets;

use App\Services\HolidayService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Filament\Widgets\Widget;

class AdminNationalHolidayCalendarWidget extends Widget
{
    protected static ?string $heading = 'Kalender Nasional';

    protected string $view = 'filament.widgets.admin-national-holiday-calendar-widget';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 5;

    public int $selectedMonth;

    public int $selectedYear;

    public function mount(): void
    {
        $this->selectedMonth = now()->month;
        $this->selectedYear = now()->year;
    }

    public function updatedSelectedMonth(): void
    {
        $this->dispatch('national-calendar-updated');
    }

    public function updatedSelectedYear(): void
    {
        $this->dispatch('national-calendar-updated');
    }

    protected function getViewData(): array
    {
        $selectedDate = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1);
        $startOfMonth = $selectedDate->copy()->startOfMonth();
        $endOfMonth = $selectedDate->copy()->endOfMonth();

        $holidayMap = app(HolidayService::class)->getHolidays($startOfMonth->year, $startOfMonth->month);

        $holidays = collect($holidayMap)
            ->map(function (string $name, string $date) {
                return [
                    'date' => Carbon::parse($date),
                    'name' => $name,
                ];
            })
            ->sortBy('date')
            ->values();

        $days = [];
        $weekendCount = 0;
        $firstDow = $startOfMonth->dayOfWeekIso - 1;

        foreach (CarbonPeriod::create($startOfMonth, $endOfMonth) as $day) {
            $key = $day->toDateString();
            $isHoliday = isset($holidayMap[$key]);
            $isWeekend = $day->isWeekend();

            if ($isWeekend) {
                $weekendCount++;
            }

            $days[] = [
                'date' => $day->copy(),
                'key' => $key,
                'day' => (int) $day->day,
                'weekday' => $day->locale('id')->translatedFormat('D'),
                'is_today' => $day->isToday(),
                'is_holiday' => $isHoliday,
                'is_weekend' => $isWeekend,
                'label' => $isHoliday ? $holidayMap[$key] : ($isWeekend ? 'Akhir Pekan' : null),
            ];
        }

        $today = now();
        $upcomingHoliday = $holidays->first(function (array $holiday) use ($today) {
            return $holiday['date']->greaterThanOrEqualTo($today->copy()->startOfDay());
        });

        return [
            'monthName' => $startOfMonth->locale('id')->isoFormat('MMMM'),
            'year' => $startOfMonth->year,
            'days' => $days,
            'holidays' => $holidays,
            'holidayCount' => $holidays->count(),
            'weekendCount' => $weekendCount,
            'upcomingHoliday' => $upcomingHoliday,
            'monthLabel' => $startOfMonth->locale('id')->isoFormat('MMMM YYYY'),
            'weekdayLabels' => collect(['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min']),
            'firstDow' => $firstDow,
        ];
    }
}
