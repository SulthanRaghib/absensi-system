@php
    $absent = $this->getAbsentRecords();
@endphp

<div class="p-4 bg-white/80 dark:bg-gray-800 rounded-lg shadow-sm">
    <div class="flex items-center justify-between mb-3">
        <div class="flex items-center gap-3">
            <div class="p-2 bg-red-50 dark:bg-red-900/20 rounded-md">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-600" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <div class="text-sm font-medium">Pegawai Belum Absen</div>
                <div class="text-xs text-gray-400">Jumlah: <span class="font-semibold">{{ $absent->count() }}</span>
                </div>
            </div>
        </div>
    </div>

    @if ($absent->isEmpty())
        <div class="text-sm text-gray-500">Semua Pegawai sudah absen hari ini.</div>
    @else
        <div class="max-h-60 overflow-y-auto">
            <ol class="list-decimal pl-6 space-y-2">
                @foreach ($absent as $idx => $r)
                    <li class="flex items-center justify-between p-2 bg-white/60 dark:bg-gray-900/30 rounded">
                        <div class="flex items-center gap-3">
                            <div
                                class="h-8 w-8 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-sm text-gray-700">
                                {{ strtoupper(substr($r->name, 0, 1)) }}</div>
                            <div class="text-sm font-medium">{{ $r->name }}</div>
                        </div>
                    </li>
                @endforeach
            </ol>
        </div>
    @endif
</div>
