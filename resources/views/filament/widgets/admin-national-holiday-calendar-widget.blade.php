<x-filament-widgets::widget>
    <style>
        [x-cloak] {
            display: none !important;
        }

        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        @keyframes fade-in-up {
            from {
                opacity: 0;
                transform: translateY(4px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in-up {
            animation: fade-in-up 0.3s ease-out;
        }

        .calendar-cell:hover {
            z-index: 50;
        }

        .calendar-cell {
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }

        .glow-amber {
            box-shadow: 0 0 25px rgba(245, 158, 11, 0.35), 0 0 50px rgba(245, 158, 11, 0.15);
        }

        .glow-gray {
            box-shadow: 0 0 25px rgba(107, 114, 128, 0.35), 0 0 50px rgba(107, 114, 128, 0.15);
        }

        @keyframes pulse-ring {

            0%,
            100% {
                box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.12), 0 0 20px rgba(245, 158, 11, 0.16);
            }

            50% {
                box-shadow: 0 0 0 5px rgba(245, 158, 11, 0.22), 0 0 30px rgba(245, 158, 11, 0.25);
            }
        }

        .ring-today {
            animation: pulse-ring 2s ease-in-out infinite;
        }

        .dark .acw-card {
            background: #111827 !important;
            background-image: none !important;
            border-color: #374151 !important;
        }

        .dark .acw-legend {
            background: rgba(31, 41, 55, 0.5) !important;
            border-color: #374151 !important;
        }

        .dark .acw-legend .text-gray-700 {
            color: #d1d5db !important;
        }

        .dark .acw-legend [class*="bg-amber-100"] {
            background-color: rgba(245, 158, 11, 0.16) !important;
        }

        .dark .acw-legend [class*="bg-slate-200"] {
            background-color: rgba(107, 114, 128, 0.18) !important;
        }

        .dark .acw-card .acw-day {
            color: #9ca3af !important;
        }

        .dark .calendar-cell {
            border-color: #374151 !important;
        }

        .dark .calendar-cell:hover {
            border-color: rgba(245, 158, 11, 0.5) !important;
        }

        .dark .calendar-cell .text-gray-700 {
            color: #e5e7eb !important;
        }

        .dark .calendar-cell .text-amber-700 {
            color: #fbbf24 !important;
        }

        .dark .calendar-cell .text-slate-700 {
            color: #d1d5db !important;
        }

        .dark .calendar-cell .text-slate-500 {
            color: #94a3b8 !important;
        }

        .dark .calendar-cell.border-amber-200 {
            border-color: rgba(245, 158, 11, 0.35) !important;
        }

        .dark .calendar-cell.border-slate-200 {
            border-color: rgba(71, 85, 105, 0.8) !important;
        }
    </style>

    <div
        class="acw-card bg-gradient-to-br from-white to-gray-50 dark:from-gray-900 dark:to-gray-800 rounded-2xl shadow-lg border border-gray-200/50 dark:border-gray-700/80 overflow-hidden">
        <div class="bg-gradient-to-r from-slate-950 to-slate-800 px-6 py-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h3 class="text-xl font-bold text-white mb-1">Kalender Nasional {{ $monthName }}
                        {{ $year }}</h3>
                    <p class="text-slate-300 text-sm">Fokus pada libur nasional dan akhir pekan untuk kebutuhan
                        operasional admin.</p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <select wire:model.live="selectedMonth"
                        class="bg-white/10 backdrop-blur-sm text-white border-transparent rounded-lg text-sm font-medium focus:border-amber-300 focus:ring-0 cursor-pointer hover:bg-white/15 transition-colors [&>option]:text-slate-900 py-1.5 pl-3 pr-8">
                        @foreach (range(1, 12) as $m)
                            <option value="{{ $m }}">
                                {{ Carbon\Carbon::create()->month($m)->locale('id')->isoFormat('MMMM') }}</option>
                        @endforeach
                    </select>

                    <select wire:model.live="selectedYear"
                        class="bg-white/10 backdrop-blur-sm text-white border-transparent rounded-lg text-sm font-medium focus:border-amber-300 focus:ring-0 cursor-pointer hover:bg-white/15 transition-colors [&>option]:text-slate-900 py-1.5 pl-3 pr-8">
                        @foreach (range(now()->year - 2, now()->year + 2) as $y)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="p-6">
            <div class="grid gap-4 sm:grid-cols-3 mb-6">
                <div
                    class="rounded-2xl bg-white dark:bg-gray-800 p-4 border border-gray-200 dark:border-gray-700 shadow-sm">
                    <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Libur
                        Nasional</div>
                    <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $holidayCount }}</div>
                    <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">Hari libur resmi bulan ini</div>
                </div>

                <div
                    class="rounded-2xl bg-white dark:bg-gray-800 p-4 border border-gray-200 dark:border-gray-700 shadow-sm">
                    <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Akhir
                        Pekan</div>
                    <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $weekendCount }}</div>
                    <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">Sabtu dan Minggu dalam bulan ini</div>
                </div>

                <div
                    class="rounded-2xl bg-white dark:bg-gray-800 p-4 border border-gray-200 dark:border-gray-700 shadow-sm">
                    <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Libur
                        Terdekat</div>
                    @if ($upcomingHoliday)
                        <div class="mt-2 text-base font-bold text-gray-900 dark:text-white">
                            {{ $upcomingHoliday['name'] }}</div>
                        <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            {{ $upcomingHoliday['date']->translatedFormat('d F Y') }}</div>
                    @else
                        <div class="mt-2 text-base font-bold text-gray-900 dark:text-white">Tidak ada libur terjadwal
                        </div>
                        <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">Bulan ini belum ada agenda libur
                            nasional</div>
                    @endif
                </div>
            </div>

            <div
                class="acw-legend px-6 py-4 bg-gray-50/50 dark:bg-gray-800/80 border border-gray-100 dark:border-gray-700/50 rounded-2xl mb-6">
                <div class="flex flex-wrap items-center gap-3 sm:gap-4 justify-center sm:justify-start">
                    <div class="flex items-center gap-2 text-sm">
                        <div
                            class="flex items-center justify-center w-7 h-7 rounded-lg bg-amber-100 dark:bg-amber-500/20">
                            <div class="w-2.5 h-2.5 rounded-full bg-amber-500"></div>
                        </div>
                        <span class="text-gray-700 dark:text-gray-300 font-medium">Libur Nasional</span>
                    </div>

                    <div class="flex items-center gap-2 text-sm">
                        <div class="flex items-center justify-center w-7 h-7 rounded-lg bg-slate-200 dark:bg-slate-700">
                            <div class="w-2.5 h-2.5 rounded-full bg-slate-600 dark:bg-slate-400"></div>
                        </div>
                        <span class="text-gray-700 dark:text-gray-300 font-medium">Akhir Pekan</span>
                    </div>

                    <div class="flex items-center gap-2 text-sm">
                        <div class="flex items-center justify-center w-7 h-7 rounded-lg bg-gray-200 dark:bg-gray-700">
                            <div class="w-2.5 h-2.5 rounded-full bg-gray-700 dark:bg-gray-400"></div>
                        </div>
                        <span class="text-gray-700 dark:text-gray-300 font-medium">Hari Kerja</span>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto xl:overflow-visible no-scrollbar pb-4"
                style="padding-top: 2rem; margin-top: -2rem;">
                <div class="min-w-[640px]">
                    <div
                        class="grid grid-cols-7 gap-2 mb-3 text-center text-[11px] font-bold uppercase tracking-[0.2em] text-slate-500">
                        @foreach ($weekdayLabels as $weekday)
                            <div class="acw-day py-1">{{ $weekday }}</div>
                        @endforeach
                    </div>

                    <div class="grid grid-cols-7 gap-2">
                        @for ($i = 0; $i < $firstDow; $i++)
                            <div></div>
                        @endfor

                        @foreach ($days as $d)
                            @php
                                $tooltipClass = 'left-1/2 -translate-x-1/2';
                                $arrowClass = 'left-1/2 -translate-x-1/2';
                                $colIndex = ($firstDow + $loop->index) % 7;

                                if ($colIndex === 0) {
                                    $tooltipClass = 'left-0 translate-x-0';
                                    $arrowClass = 'left-4 translate-x-0';
                                } elseif ($colIndex === 6) {
                                    $tooltipClass = 'right-0 translate-x-0 left-auto';
                                    $arrowClass = 'right-4 translate-x-0 left-auto';
                                }
                            @endphp

                            <div x-data="{ show: false }" @mouseenter="show = true" @mouseleave="show = false"
                                class="relative calendar-cell rounded-xl border-2 {{ $d['is_today'] ? 'border-amber-400 ring-today' : 'border-gray-100 dark:border-gray-800' }} hover:border-amber-200 dark:hover:border-amber-500/50 hover:shadow-md aspect-square flex flex-col items-center justify-center p-2 group cursor-pointer {{ $d['is_holiday'] ? 'bg-amber-50 dark:bg-amber-500/10' : ($d['is_weekend'] ? 'bg-slate-50 dark:bg-gray-800/80' : 'bg-white dark:bg-gray-800/40') }}">

                                @if ($d['is_holiday'])
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <div class="w-16 h-16 rounded-full bg-amber-500/15 glow-amber"></div>
                                    </div>
                                    <div
                                        class="absolute inset-0 flex items-center justify-center opacity-10 dark:opacity-[0.08] text-6xl pointer-events-none">
                                        🎉
                                    </div>
                                @elseif($d['is_weekend'])
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <div
                                            class="w-16 h-16 rounded-full bg-gray-300/30 dark:bg-gray-600/30 glow-gray">
                                        </div>
                                    </div>
                                @else
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <div class="w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-700/50"></div>
                                    </div>
                                @endif

                                @if ($d['is_today'])
                                    <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                                        <div class="w-20 h-20 rounded-full bg-amber-50 dark:bg-amber-500/20 glow-amber">
                                        </div>
                                    </div>
                                @endif

                                <div class="relative z-10 flex flex-col items-center justify-center flex-1">
                                    <div
                                        class="text-2xl font-bold mb-0.5 {{ $d['is_holiday'] ? 'text-amber-700' : 'text-gray-700 dark:text-gray-200' }}">
                                        {{ $d['day'] }}
                                    </div>

                                    @if ($d['is_holiday'])
                                        <span class="text-[9px] text-amber-700 font-bold uppercase tracking-wide">Libur
                                            Nasional</span>
                                    @elseif($d['is_weekend'])
                                        <span class="text-[9px] text-gray-500 dark:text-gray-400 font-medium">Akhir
                                            Pekan</span>
                                    @else
                                        <span
                                            class="text-[9px] text-gray-700 dark:text-gray-400 font-bold uppercase tracking-wide">Hari
                                            Kerja</span>
                                    @endif
                                </div>

                                @if ($d['label'])
                                    <div x-show="show" x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 scale-95"
                                        x-transition:enter-end="opacity-100 scale-100"
                                        x-transition:leave="transition ease-in duration-100"
                                        x-transition:leave-start="opacity-100 scale-100"
                                        x-transition:leave-end="opacity-0 scale-95"
                                        class="absolute z-[100] bottom-[calc(100%+8px)] w-max max-w-[150px] sm:max-w-[200px] bg-gray-900 text-white text-[10px] sm:text-xs py-2 px-3 rounded-lg shadow-xl backdrop-blur-sm whitespace-normal text-center break-words leading-tight {{ $tooltipClass }}"
                                        style="display: none;">
                                        <div class="font-medium">{{ $d['label'] }}</div>
                                        <div
                                            class="absolute top-full border-[6px] border-transparent border-t-gray-900 {{ $arrowClass }}">
                                        </div>
                                    </div>
                                @endif

                                @if ($d['is_today'])
                                    <div
                                        class="absolute -top-1 -right-1 w-5 h-5 bg-amber-500 rounded-full flex items-center justify-center shadow-lg z-10">
                                        <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
