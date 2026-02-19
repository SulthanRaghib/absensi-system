<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class AttendanceExport implements FromView, WithEvents
{
    protected $users;
    protected $daysInMonth;
    protected $monthName;
    protected $year;
    protected $startDate;
    /**
     * Per-day threshold map: [ dayNumber => 'HH:MM' ]
     * Replaces the single $jamMasukKantor so Ramadan dates use the correct threshold.
     * Also accepts a plain string for backward compatibility (converted to a flat map).
     */
    protected $thresholdMap;

    public function __construct($users, $daysInMonth, $monthName, $year, $startDate, $thresholdMapOrLegacyString)
    {
        $this->users       = $users;
        $this->daysInMonth = $daysInMonth;
        $this->monthName   = $monthName;
        $this->year        = $year;
        $this->startDate   = $startDate;

        // Backward compat: if old code passes a plain string, wrap it.
        if (is_array($thresholdMapOrLegacyString)) {
            $this->thresholdMap = $thresholdMapOrLegacyString;
        } else {
            $time = $thresholdMapOrLegacyString ? substr((string) $thresholdMapOrLegacyString, 0, 5) : '07:30';
            $this->thresholdMap = array_fill(1, $daysInMonth, $time);
        }
    }

    public function view(): View
    {
        $start = $this->startDate->copy();
        $end = $this->startDate->copy()->addDays($this->daysInMonth - 1);

        $this->users->loadMissing([
            'absences',
            'permissions' => function ($query) use ($start, $end) {
                $query->where('status', 'approved')
                    ->where(function ($permissionQuery) use ($start, $end) {
                        $permissionQuery
                            ->whereBetween('start_date', [$start, $end])
                            ->orWhereBetween('end_date', [$start, $end])
                            ->orWhere(function ($query) use ($start, $end) {
                                $query->where('start_date', '<=', $start)
                                    ->where('end_date', '>=', $end);
                            });
                    });
            },
        ]);

        $holidayMap = (new \App\Services\HolidayService)->getHolidays($this->startDate->year, $this->startDate->month);
        // The view expects standard array of date strings
        $holidays = array_keys($holidayMap);

        return view('exports.attendance', [
            'users'        => $this->users,
            'daysInMonth'  => $this->daysInMonth,
            'monthName'    => $this->monthName,
            'year'         => $this->year,
            'startDate'    => $this->startDate,
            'thresholdMap' => $this->thresholdMap, // [ day => 'HH:MM' ]
            'holidays'     => $holidays,
        ]);
    }


    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();

                // 1. Freeze Panes
                // Freezing C6 to keep Rows 1-5 (Title + 3 Header Rows) and Cols A-B visible.
                // Data starts at Row 6.
                $sheet->freezePane('C6');

                // 2. Title & Header Formatting
                // Row 1: Title
                $sheet->mergeCells('A1:' . $highestColumn . '1');
                $sheet->getStyle('A1')->getFont()->setSize(16)->setBold(true);

                // Row 2: Period
                $sheet->mergeCells('A2:' . $highestColumn . '2');
                $sheet->getStyle('A2')->getFont()->setSize(14)->setBold(true);

                // Row 3: Main Header Background
                $sheet->getStyle('A3:' . $highestColumn . '3')->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFE0E0E0');
                $sheet->getStyle('A3:' . $highestColumn . '3')->getFont()->setBold(true);

                // 3. Alignment Rules
                // Global: Middle & Center
                $sheet->getStyle('A1:' . $highestColumn . $highestRow)->getAlignment()
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Exception: Column B (Nama) - Left aligned for data rows
                $sheet->getStyle('B6:B' . $highestRow)->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT);

                // 4. Borders
                $sheet->getStyle('A1:' . $highestColumn . $highestRow)->getBorders()
                    ->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                // 5. Weekend Coloring
                // Iterate through days to find weekends and color the data columns
                if ($highestRow >= 6) {
                    for ($day = 1; $day <= $this->daysInMonth; $day++) {
                        $date = $this->startDate->copy()->day($day);

                        if ($date->isWeekend()) {
                            // Calculate columns for this day
                            // Day 1 starts at Column C (Index 3)
                            // Each day takes 2 columns (In, Out)
                            $startColIndex = 3 + ($day - 1) * 2;

                            // Apply to both columns of the day
                            for ($i = 0; $i < 2; $i++) {
                                $colString = Coordinate::stringFromColumnIndex($startColIndex + $i);
                                $range = $colString . '6:' . $colString . $highestRow;

                                $sheet->getStyle($range)->getFill()
                                    ->setFillType(Fill::FILL_SOLID)
                                    ->getStartColor()->setARGB('FFFFCCCC'); // Light Red
                            }
                        }
                    }
                }
            },
        ];
    }
}
