@php
    $data = $this->getData();
    $totalUsers = $data['totalUsers'];
    $presentToday = $data['presentToday'];
    $absent = $data['absent'];
    $lateCount = $data['lateCount'];
    $lateNames = $data['lateNames'];
    $last7 = $data['last7'];
@endphp

<div class="filament-widget">
    <div class="grid grid-cols-3 gap-4 mb-4">
        <div class="p-4 bg-white rounded-lg shadow-sm">
            <div class="text-sm text-gray-500">Total Pegawai</div>
            <div class="text-2xl font-bold">{{ number_format($totalUsers) }}</div>
        </div>

        <div class="p-4 bg-white rounded-lg shadow-sm">
            <div class="text-sm text-gray-500">Hadir Hari Ini</div>
            <div class="text-2xl font-bold text-green-600">{{ number_format($presentToday) }}</div>
        </div>

        <div class="p-4 bg-white rounded-lg shadow-sm">
            <div class="text-sm text-gray-500">Tidak Hadir</div>
            <div class="text-2xl font-bold text-red-600">{{ number_format($absent) }}</div>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div class="p-4 bg-white rounded-lg shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <div class="text-sm text-gray-500">Karyawan Telat ({{ $lateCount }})</div>
            </div>

            @if ($lateCount > 0)
                <ul class="list-disc list-inside text-sm">
                    @foreach ($lateNames as $name)
                        <li>{{ $name }}</li>
                    @endforeach
                </ul>
            @else
                <div class="text-sm text-gray-500">Tidak ada karyawan telat hari ini.</div>
            @endif
        </div>

        <div class="p-4 bg-white rounded-lg shadow-sm">
            <div class="text-sm text-gray-500 mb-2">Hadir 7 Hari Terakhir</div>
            <div class="text-sm">
                @foreach ($last7 as $point)
                    <div class="flex justify-between">
                        <div>{{ \Carbon\Carbon::parse($point['date'])->format('d M') }}</div>
                        <div>{{ $point['present'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
