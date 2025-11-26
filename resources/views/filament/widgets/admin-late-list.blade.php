@php
    $late = $this->getLateRecords();
@endphp

<div class="p-4 bg-white/80 dark:bg-gray-800 rounded-lg shadow-sm">
    <div class="flex items-center justify-between mb-3">
        <div class="flex items-center gap-3">
            <div class="p-2 bg-yellow-50 dark:bg-yellow-900/20 rounded-md">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-600" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <div class="text-sm font-medium">Karyawan Telat</div>
                <div class="text-xs text-gray-400">Jumlah: <span class="font-semibold">{{ $late->count() }}</span>
                </div>
            </div>
        </div>
        <div class="text-xs text-gray-400">Terbaru</div>
    </div>

    @if ($late->isEmpty())
        <div class="text-sm text-gray-500">Semua karyawan hadir tepat waktu hari ini.</div>
    @else
        <ol class="list-decimal pl-6 space-y-2">
            @foreach ($late as $idx => $r)
                <li class="flex items-center justify-between p-2 bg-white/60 dark:bg-gray-900/30 rounded">
                    <div class="flex items-center gap-3">
                        <div
                            class="h-8 w-8 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-sm text-gray-700">
                            {{ strtoupper(substr($r->name, 0, 1)) }}</div>
                        <div class="text-sm font-medium">{{ $r->name }}</div>
                    </div>
                    <div class="text-sm text-gray-500">{{ $r->time }}</div>
                </li>
            @endforeach
        </ol>
    @endif
</div>
