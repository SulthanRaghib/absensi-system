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
    </style>
</head>

<body>
    <table>
        <!-- Header Rows -->
        <tr>
            <td colspan="{{ 2 + $daysInMonth * 2 + 3 }}" class="header-title" style="border: none;">Rekapitulasi Kehadiran
                Peserta Magang</td>
        </tr>
        {{-- <tr>
            <td colspan="{{ 2 + $daysInMonth * 2 + 3 }}" class="header-subtitle" style="border: none;">Laporan Absensi
                Pegawai</td>
        </tr> --}}
        <tr>
            <td colspan="{{ 2 + $daysInMonth * 2 + 3 }}" class="header-subtitle" style="border: none;">Periode:
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
                <th colspan="3" rowspan="2" class="bg-gray">Total</th>
            </tr>
            <tr>
                @for ($day = 1; $day <= $daysInMonth; $day++)
                    @php
                        $date = $startDate->copy()->day($day);
                        $isWeekend = $date->isWeekend();
                        $bgClass = $isWeekend ? 'bg-red' : 'bg-gray';
                    @endphp
                    <th colspan="2" class="{{ $bgClass }}">{{ $day }}</th>
                @endfor
            </tr>
            <tr>
                @for ($day = 1; $day <= $daysInMonth; $day++)
                    @php
                        $date = $startDate->copy()->day($day);
                        $isWeekend = $date->isWeekend();
                        $bgClass = $isWeekend ? 'bg-red' : 'bg-gray';
                    @endphp
                    <th class="{{ $bgClass }}" style="width: 60px;">In</th>
                    <th class="{{ $bgClass }}" style="width: 60px;">Out</th>
                @endfor
                <th class="bg-gray" style="width: 80px;">Hadir</th>
                <th class="bg-gray" style="width: 80px;">Terlambat</th>
                <th class="bg-gray" style="width: 80px;">Alpa</th>
            </tr>
        </thead>

        <!-- Table Body -->
        <tbody>
            @foreach ($users as $index => $user)
                @php
                    $totalHadir = 0;
                    $totalTerlambat = 0;
                    $totalAlpa = 0;
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

                            $absence = $user->absences->first(function ($a) use ($dateStr) {
                                return $a->tanggal->format('Y-m-d') === $dateStr;
                            });

                            $jamMasuk = $absence && $absence->jam_masuk ? $absence->jam_masuk->format('H:i') : '';
                            $jamPulang = $absence && $absence->jam_pulang ? $absence->jam_pulang->format('H:i') : '';

                            $isLate = false;

                            // Hitung Total
                            if ($jamMasuk) {
                                $totalHadir++;
                                // Cek Terlambat (Asumsi jam masuk kantor 07:30)
                                if ($jamMasuk > ($jamMasukKantor ?? '07:30')) {
                                    $totalTerlambat++;
                                    $isLate = true;
                                }
                            } else {
                                // Hitung Alpa jika hari kerja dan belum ada absen (dan tanggal sudah lewat/hari ini)
                                if (!$isWeekend && $dateObj->lte(now())) {
                                    $totalAlpa++;
                                }
                            }
                        @endphp

                        <td class="{{ $bgClass }}" style="{{ $isLate ? 'color: red; font-weight: bold;' : '' }}">
                            {{ $jamMasuk }}</td>
                        <td class="{{ $bgClass }}">{{ $jamPulang }}</td>
                    @endfor

                    <td>{{ $totalHadir }}</td>
                    <td>{{ $totalTerlambat }}</td>
                    <td>{{ $totalAlpa }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
