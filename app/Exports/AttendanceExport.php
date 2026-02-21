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

        // Build ramadanDays from the immutable `is_ramadan` flag snapshotted on each
        // attendance record at check-in time. This guarantees that historical Ramadan
        // display (e.g. Feb 2026 exports) will NEVER change when next year's Ramadan
        // dates are different — because we read from the record itself, not from settings.
        $ramadanDays = [];
        $exportYearMonth = $this->startDate->format('Y-m');
        foreach ($this->users as $user) {
            foreach ($user->absences as $absence) {
                if (!empty($absence->is_ramadan) && $absence->tanggal) {
                    if ($absence->tanggal->format('Y-m') === $exportYearMonth) {
                        $day = (int) $absence->tanggal->format('d');
                        $ramadanDays[$day] = true;
                    }
                }
            }
        }
        $ramadanDays = array_keys($ramadanDays);

        return view('exports.attendance', [
            'users'        => $this->users,
            'daysInMonth'  => $this->daysInMonth,
            'monthName'    => $this->monthName,
            'year'         => $this->year,
            'startDate'    => $this->startDate,
            'thresholdMap' => $this->thresholdMap, // [ day => 'HH:MM' ]
            'holidays'     => $holidays,
            'ramadanDays'  => $ramadanDays, // immutable: derived from snapshotted is_ramadan flags
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

                // 5. Column Widths
                $sheet->getColumnDimension('A')->setWidth(5); // No
                $sheet->getColumnDimension('B')->setWidth(35); // Nama

                // Days columns
                for ($day = 1; $day <= $this->daysInMonth; $day++) {
                    $startColIndex = 3 + ($day - 1) * 2;
                    $colString1 = Coordinate::stringFromColumnIndex($startColIndex);
                    $colString2 = Coordinate::stringFromColumnIndex($startColIndex + 1);
                    $sheet->getColumnDimension($colString1)->setWidth(8);
                    $sheet->getColumnDimension($colString2)->setWidth(8);
                }

                // Totals columns
                $totalStartCol = 3 + ($this->daysInMonth * 2);
                for ($i = 0; $i < 5; $i++) {
                    $colString = Coordinate::stringFromColumnIndex($totalStartCol + $i);
                    $sheet->getColumnDimension($colString)->setWidth(10);
                }
            },
        ];
    }
}
