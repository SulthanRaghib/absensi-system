@php
    $late = $this->getLateRecords();
    $schedule = $this->getScheduleInfo();
@endphp

<x-filament-widgets::widget>
    <div class="p-4 bg-white/80 dark:bg-gray-800 rounded-lg shadow-sm">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-yellow-50 dark:bg-yellow-900/20 rounded-md">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-600" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium">Pegawai Telat</span>
                        @if ($schedule['is_ramadan'])
                            <span
                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300 border border-amber-200 dark:border-amber-700">
                                🌙 Ramadan
                            </span>
                        @endif
                    </div>
                    <div class="text-xs text-gray-400">
                        Batas: <span class="font-semibold">{{ $schedule['jam_masuk'] }}</span>
                        &nbsp;·&nbsp;
                        Jumlah: <span class="font-semibold">{{ $late->count() }}</span>
                    </div>
                </div>
            </div>
            <div class="text-xs text-gray-400">Hari ini</div>
        </div>

        @if ($late->isEmpty())
            <div class="flex items-center gap-2 text-sm text-emerald-600 dark:text-emerald-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd" />
                </svg>
                Semua pegawai hadir tepat waktu hari ini.
            </div>
        @else
            <ol class="list-decimal pl-6 space-y-2">
                @foreach ($late as $r)
                    <li class="flex items-center justify-between p-2 bg-white/60 dark:bg-gray-900/30 rounded">
                        <div class="flex items-center gap-3">
                            <div
                                class="h-8 w-8 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-sm font-semibold text-gray-700 dark:text-gray-300">
                                {{ strtoupper(substr($r->name, 0, 1)) }}
                            </div>
                            <div>
                                <div class="text-sm font-medium">{{ $r->name }}</div>
                                @if ($r->is_ramadan)
                                    <span class="text-xs text-amber-600 dark:text-amber-400">🌙 Ramadan · batas
                                        {{ $r->threshold }}</span>
                                @else
                                    <span class="text-xs text-gray-400">Batas {{ $r->threshold }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-semibold text-red-600 dark:text-red-400">{{ $r->time }}</div>
                            @php
                                [$th_h, $th_m] = explode(':', $r->threshold);
                                [$jm_h, $jm_m] = explode(':', $r->time);
                                $diffMin = $jm_h * 60 + $jm_m - ($th_h * 60 + $th_m);
                            @endphp
                            @if ($diffMin > 0)
                                <div class="text-xs text-gray-400">+{{ $diffMin }} mnt</div>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ol>
        @endif
    </div>
</x-filament-widgets::widget>
