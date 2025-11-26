@php
    $data = $this->getData();
    $totalUsers = $data['totalUsers'];
    $presentToday = $data['presentToday'];
    $absent = $data['absent'];
    $lateCount = $data['lateCount'];
    $lateNames = $data['lateNames'];
    $last7 = $data['last7'];
@endphp

<section class="space-y-4">
    {{-- Top summary cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-4 bg-white/80 dark:bg-gray-800 rounded-lg shadow-sm flex items-center gap-4">
            <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-full">
                <!-- Users icon -->
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87M16 11a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </div>
            <div class="flex-1">
                <div class="text-xs text-gray-500">Total Pegawai</div>
                <div class="text-2xl font-semibold">{{ number_format($totalUsers) }}</div>
            </div>
        </div>

        <div class="p-4 bg-white/80 dark:bg-gray-800 rounded-lg shadow-sm flex items-center gap-4">
            <div class="p-3 bg-green-50 dark:bg-green-900/20 rounded-full">
                <!-- Present icon -->
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <div class="flex-1">
                <div class="text-xs text-gray-500">Hadir Hari Ini</div>
                <div class="text-2xl font-semibold text-green-600">{{ number_format($presentToday) }}</div>
                <div class="text-xs text-gray-400">{{ $totalUsers ? round(($presentToday / $totalUsers) * 100) : 0 }}%
                    hadir</div>
            </div>
        </div>

        <div class="p-4 bg-white/80 dark:bg-gray-800 rounded-lg shadow-sm flex items-center gap-4">
            <div class="p-3 bg-red-50 dark:bg-red-900/20 rounded-full">
                <!-- Absent icon -->
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </div>
            <div class="flex-1">
                <div class="text-xs text-gray-500">Tidak Hadir</div>
                <div class="text-2xl font-semibold text-red-600">{{ number_format($absent) }}</div>
                <div class="text-xs text-gray-400">{{ $totalUsers ? round(($absent / $totalUsers) * 100) : 0 }}% tidak
                    hadir</div>
            </div>
        </div>
    </div>

    {{-- Middle panels: Late list and 7-day sparkline --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="lg:col-span-2 p-4 bg-white/80 dark:bg-gray-800 rounded-lg shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-yellow-50 dark:bg-yellow-900/20 rounded-md">
                        <!-- Clock / late icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-600" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-sm font-medium">Karyawan Telat</div>
                        <div class="text-xs text-gray-400">Jumlah: <span
                                class="font-semibold">{{ $lateCount }}</span></div>
                    </div>
                </div>
                <div class="text-xs text-gray-400">Terbaru</div>
            </div>

            @if ($lateCount > 0)
                <div class="space-y-2">
                    @foreach ($lateNames as $idx => $name)
                        <div class="flex items-center justify-between p-2 bg-white/60 dark:bg-gray-900/30 rounded">
                            <div class="flex items-center gap-3">
                                <div
                                    class="h-8 w-8 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-sm text-gray-700">
                                    {{ strtoupper(substr($name, 0, 1)) }}</div>
                                <div class="text-sm font-medium">{{ $name }}</div>
                            </div>
                            <div class="text-sm text-gray-400">-</div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-sm text-gray-500">Semua karyawan hadir tepat waktu hari ini.</div>
            @endif
        </div>

        <div class="p-4 bg-white/80 dark:bg-gray-800 rounded-lg shadow-sm">
            <div class="text-sm text-gray-500 mb-2">Hadir 7 Hari Terakhir</div>
            {{-- Simple sparkline bars --}}
            <div class="flex items-end gap-1 h-20">
                @php
                    $values = collect($last7)->pluck('present')->map(fn($v) => (int) $v);
                    $max = $values->max() ?: 1;
                @endphp

                @foreach ($values as $v)
                    @php $h = intval(($v / $max) * 100); @endphp
                    <div class="bg-primary-500/80 dark:bg-primary-400"
                        style="width:10%;height:{{ $h }}%;border-radius:4px"></div>
                @endforeach
            </div>

            <div class="mt-3 text-xs text-gray-500">
                @foreach ($last7 as $point)
                    <div class="flex items-center justify-between">
                        <div>{{ \Carbon\Carbon::parse($point['date'])->format('d M') }}</div>
                        <div>{{ $point['present'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
