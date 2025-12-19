<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="google-site-verification" content="D7lwUHT9cSFPvvz6Ad11J0QBbCgBTe7hi_0Lc7OfY3E" />
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            font-family: Arial, sans-serif;
        }

        th,
        td {
            border: 1px solid #000000;
            padding: 5px;
            text-align: center;
            vertical-align: middle;
            font-size: 16px;
            white-space: nowrap;
        }

        .header-title {
            font-size: 26px;
            font-weight: bold;
            text-align: center;
            border: none;
        }

        .header-subtitle {
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            border: none;
        }

        .bg-gray {
            background-color: #f0f0f0;
        }

        .bg-red {
            background-color: #ffcccc;
        }

        /* Totals header colors */
        .bg-hadir {
            background-color: #d1e7dd;
            /* light green */
        }

        .bg-izin {
            background-color: #fff3cd;
            /* light yellow */
        }

        .bg-sakit {
            background-color: #cfe2ff;
            /* light blue */
        }

        .bg-terlambat {
            background-color: #ffd6d6;
            /* light red */
        }

        .bg-alpa {
            background-color: #f8d7da;
            /* pinkish */
        }
    </style>
</head>

<body>
    @php
        $officeEntryTime = $jamMasukKantor ? \Carbon\Carbon::parse($jamMasukKantor)->format('H:i') : '07:30';
    @endphp
    <table>
        <!-- Header Rows -->
        <tr>
            <td colspan="{{ 2 + $daysInMonth * 2 + 5 }}" class="header-title" style="border: none;">Rekapitulasi Kehadiran
                Peserta Magang</td>
        </tr>
        {{-- <tr>
            <td colspan="{{ 2 + $daysInMonth * 2 + 3 }}" class="header-subtitle" style="border: none;">Laporan Absensi
                Pegawai</td>
        </tr> --}}
        <tr>
            <td colspan="{{ 2 + $daysInMonth * 2 + 5 }}" class="header-subtitle" style="border: none;">Periode:
                {{ $monthName }} {{ $year }}</td>
        </tr>
        {{-- <tr>
            <td colspan="{{ 2 + $daysInMonth * 2 + 2 }}" style="border: none; height: 10px;"></td>
        </tr> --}}

        <!-- Table Header -->
        <thead>
            <tr>
                <th rowspan="3" class="bg-gray" style="width: 40px;">No</th>
                <th rowspan="3" class="bg-gray" style="width: 250px;">Nama</th>
                <th colspan="{{ $daysInMonth * 2 }}" class="bg-gray">Tanggal</th>
                <th colspan="5" rowspan="2" class="bg-gray">Total</th>
            </tr>
            <tr>
                @for ($day = 1; $day <= $daysInMonth; $day++)
                    @php
                        $date = $startDate->copy()->day($day);
                        $bgClass = 'bg-gray';
                    @endphp
                    <th colspan="2" class="{{ $bgClass }}">{{ $day }}</th>
                @endfor
            </tr>
            <tr>
                @for ($day = 1; $day <= $daysInMonth; $day++)
                    @php
                        $date = $startDate->copy()->day($day);
                        $isWeekend = $date->isWeekend();
                        $bgClass = 'bg-gray';
                    @endphp
                    <th class="{{ $bgClass }}" style="width: 60px;">In</th>
                    <th class="{{ $bgClass }}" style="width: 60px;">Out</th>
                @endfor
                <th style="width: 80px; background-color: #d1e7dd;">Hadir</th>
                <th style="width: 80px; background-color: #fff3cd;">Izin</th>
                <th style="width: 80px; background-color: #cfe2ff;">Sakit</th>
                <th style="width: 80px; background-color: #ffd6d6;">Terlambat</th>
                <th style="width: 80px; background-color: #f8d7da;">Alpa</th>
            </tr>
        </thead>

        <!-- Table Body -->
        <tbody>
            @foreach ($users as $index => $user)
                @php
                    $totalHadir = 0;
                    $totalTerlambat = 0;
                    $totalAlpa = 0;
                    $totalIzin = 0;
                    $totalSakit = 0;
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td style="text-align: left;">{{ $user->name }}</td>

                    @for ($day = 1; $day <= $daysInMonth; $day++)
                        @php
                            $dateObj = $startDate->copy()->day($day);
                            $dateStr = $dateObj->format('Y-m-d');
                            $isWeekend = $dateObj->isWeekend();
                            $bgClass = $isWeekend ? 'bg-red' : '';

                            $attendance = $user->absences->first(function ($a) use ($dateStr) {
                                return $a->tanggal->format('Y-m-d') === $dateStr;
                            });

                            $permission = null;

                            if (!$attendance || !$attendance->jam_masuk) {
                                $permission = $user->permissions->first(function ($permission) use ($dateObj) {
                                    return $permission->start_date->lte($dateObj) &&
                                        $permission->end_date->gte($dateObj);
                                });
                            }

                            $inDisplay = '';
                            $outDisplay = '';
                            $inStyles = [];
                            $mergeCell = false;

                            if ($attendance && $attendance->jam_masuk) {
                                $inDisplay = $attendance->jam_masuk->format('H:i');
                                $outDisplay = $attendance->jam_pulang ? $attendance->jam_pulang->format('H:i') : '';
                                $totalHadir++;

                                if ($inDisplay > $officeEntryTime) {
                                    $totalTerlambat++;
                                    $inStyles[] = 'color: red';
                                    $inStyles[] = 'font-weight: bold';
                                }
                            } elseif ($permission) {
                                switch ($permission->type) {
                                    case 'sakit':
                                        $inDisplay = 'S';
                                        $inStyles[] = 'background-color: #cfe2ff';
                                        $inStyles[] = 'color: #084298';
                                        $inStyles[] = 'font-weight: bold';
                                        $totalSakit++;
                                        $mergeCell = true;
                                        break;
                                    case 'izin':
                                        $inDisplay = 'I';
                                        $inStyles[] = 'background-color: #fff3cd';
                                        $inStyles[] = 'color: #664d03';
                                        $inStyles[] = 'font-weight: bold';
                                        $totalIzin++;
                                        $mergeCell = true;
                                        break;
                                    case 'dinas_luar':
                                        $inDisplay = 'DL';
                                        $inStyles[] = 'background-color: #d1e7dd';
                                        $inStyles[] = 'color: #0f5132';
                                        $inStyles[] = 'font-weight: bold';
                                        $totalHadir++;
                                        break;
                                    default:
                                        $inDisplay = '-';
                                        break;
                                }
                            } else {
                                if (!$isWeekend && $dateObj->lte(now())) {
                                    $inDisplay = 'A';
                                    $totalAlpa++;
                                    $inStyles[] = 'color: #d32f2f';
                                    $inStyles[] = 'font-weight: bold';
                                    $mergeCell = true;
                                } else {
                                    $inDisplay = '-';
                                }
                            }

                            $inStyle = $inStyles ? implode('; ', $inStyles) : '';
                        @endphp

                        @if ($mergeCell)
                            <td class="{{ $bgClass }}" colspan="2" style="{{ $inStyle }}">
                                {{ $inDisplay }}</td>
                        @else
                            <td class="{{ $bgClass }}" style="{{ $inStyle }}">{{ $inDisplay }}</td>
                            <td class="{{ $bgClass }}">{{ $outDisplay }}</td>
                        @endif
                    @endfor

                    <td>{{ $totalHadir }}</td>
                    <td>{{ $totalIzin }}</td>
                    <td>{{ $totalSakit }}</td>
                    <td>{{ $totalTerlambat }}</td>
                    <td>{{ $totalAlpa }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
