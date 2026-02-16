<x-filament-widgets::widget>
    <style>
        [x-cloak] {
            display: none !important
        }

        /* Hide scrollbar for Chrome, Safari and Opera */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        /* Hide scrollbar for IE, Edge and Firefox */
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        /* Calendar tooltip styles removed in favor of inline logic */

        /* Custom animations */
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

        /* Hover scale effect */
        .calendar-cell:hover {
            z-index: 50;
            /* Ensure hovered cell is above others */
        }

        .calendar-cell {
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            /* Create stacking context */
        }

        /* Glow effects for status */
        .glow-green {
            box-shadow: 0 0 25px rgba(34, 197, 94, 0.5), 0 0 50px rgba(34, 197, 94, 0.25);
        }

        .glow-red {
            box-shadow: 0 0 25px rgba(239, 68, 68, 0.5), 0 0 50px rgba(239, 68, 68, 0.25);
        }

        .glow-yellow {
            box-shadow: 0 0 25px rgba(234, 179, 8, 0.5), 0 0 50px rgba(234, 179, 8, 0.25);
        }

        .glow-gray {
            box-shadow: 0 0 25px rgba(107, 114, 128, 0.4), 0 0 50px rgba(107, 114, 128, 0.2);
        }

        .glow-purple {
            box-shadow: 0 0 25px rgba(239, 68, 68, 0.3), 0 0 50px rgba(239, 68, 68, 0.15);
        }

        .glow-blue {
            box-shadow: 0 0 25px rgba(59, 130, 246, 0.5), 0 0 50px rgba(59, 130, 246, 0.25);
        }

        /* Pulse animation for today */
        @keyframes pulse-ring {

            0%,
            100% {
                box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1), 0 0 20px rgba(99, 102, 241, 0.15);
            }

            50% {
                box-shadow: 0 0 0 5px rgba(99, 102, 241, 0.2), 0 0 30px rgba(99, 102, 241, 0.25);
            }
        }

        .ring-today {
            animation: pulse-ring 2s ease-in-out infinite;
        }
    </style>

    <div class="bg-gradient-to-br from-white to-gray-50 rounded-2xl shadow-lg border border-gray-200/50">
        <!-- Header Section -->
        <div class="bg-gradient-to-r from-indigo-600 to-indigo-500 px-6 py-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h3 class="text-xl font-bold text-white mb-1">{{ $monthName }} {{ $year }}</h3>
                    <p class="text-indigo-100 text-sm">Rekap Kehadiran Bulanan</p>
                </div>

                <!-- Quick Stats -->
                <div class="flex gap-2">
                    <div class="bg-white/20 backdrop-blur-sm rounded-lg px-3 py-1.5 text-white text-xs font-medium">
                        <span class="opacity-80">Bulan Ini</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Legend Section -->
        <div class="px-6 py-4 bg-gray-50/50 border-b border-gray-100">
            <div class="flex flex-wrap items-center gap-3 sm:gap-4 justify-center sm:justify-start">
                <div class="flex items-center gap-2 text-sm">
                    <div class="flex items-center justify-center w-7 h-7 rounded-lg bg-green-100">
                        <div class="w-2.5 h-2.5 rounded-full bg-green-500"></div>
                    </div>
                    <span class="text-gray-700 font-medium">Tepat Waktu</span>
                </div>

                <div class="flex items-center gap-2 text-sm">
                    <div class="flex items-center justify-center w-7 h-7 rounded-lg bg-red-100">
                        <div class="w-2.5 h-2.5 rounded-full bg-red-500"></div>
                    </div>
                    <span class="text-gray-700 font-medium">Telat</span>
                </div>

                <div class="flex items-center gap-2 text-sm">
                    <div class="flex items-center justify-center w-7 h-7 rounded-lg bg-yellow-100">
                        <div class="w-2.5 h-2.5 rounded-full bg-yellow-500"></div>
                    </div>
                    <span class="text-gray-700 font-medium">Izin/Sakit</span>
                </div>

                <div class="flex items-center gap-2 text-sm">
                    <div class="flex items-center justify-center w-7 h-7 rounded-lg bg-gray-200">
                        <div class="w-2.5 h-2.5 rounded-full bg-gray-700"></div>
                    </div>
                    <span class="text-gray-700 font-medium">Alpha</span>
                </div>

                <div class="flex items-center gap-2 text-sm">
                    <div class="flex items-center justify-center w-7 h-7 rounded-lg bg-red-100">
                        🎉
                    </div>
                    <span class="text-gray-700 font-medium">Libur Nasional</span>
                </div>
            </div>
        </div>

        <!-- Calendar Section -->
        <div class="p-6">
            <div class="overflow-x-auto xl:overflow-visible no-scrollbar pb-4"
                style="padding-top: 2rem; margin-top: -2rem;">
                <div class="min-w-[640px]">
                    <!-- Day Headers -->
                    <div class="grid grid-cols-7 gap-2 mb-3">
                        <div class="text-center text-xs font-semibold text-gray-500 uppercase tracking-wider py-2">Sen
                        </div>
                        <div class="text-center text-xs font-semibold text-gray-500 uppercase tracking-wider py-2">Sel
                        </div>
                        <div class="text-center text-xs font-semibold text-gray-500 uppercase tracking-wider py-2">Rab
                        </div>
                        <div class="text-center text-xs font-semibold text-gray-500 uppercase tracking-wider py-2">Kam
                        </div>
                        <div class="text-center text-xs font-semibold text-gray-500 uppercase tracking-wider py-2">Jum
                        </div>
                        <div class="text-center text-xs font-semibold text-gray-400 uppercase tracking-wider py-2">Sab
                        </div>
                        <div class="text-center text-xs font-semibold text-gray-400 uppercase tracking-wider py-2">Min
                        </div>
                    </div>

                    <!-- Calendar Grid -->
                    <div class="grid grid-cols-7 gap-2">
                        @php
                            $firstDow = $startOfMonth->dayOfWeekIso - 1;
                            for ($i = 0; $i < $firstDow; $i++) {
                                echo '<div></div>';
                            }
                        @endphp

                        @foreach ($days as $d)
                            @php
                                $colIndex = ($firstDow + $loop->index) % 7;
                                // Tooltip positioning class based on column
                                $tooltipClass = 'left-1/2 -translate-x-1/2'; // Default: center
                                $arrowClass = 'left-1/2 -translate-x-1/2'; // Default arrow: center

                                if ($colIndex === 0) {
                                    // Leftmost column: align left edge
                                    $tooltipClass = 'left-0 translate-x-0';
                                    $arrowClass = 'left-4 translate-x-0'; // Arrow near left
                                } elseif ($colIndex === 6) {
                                    // Rightmost column: align right edge
                                    $tooltipClass = 'right-0 translate-x-0 left-auto';
                                    $arrowClass = 'right-4 translate-x-0 left-auto'; // Arrow near right
                                }
                            @endphp

                            <div x-data="{ show: false }" @mouseenter="show = true" @mouseleave="show = false"
                                class="relative calendar-cell rounded-xl border-2 {{ $d['is_today'] ? 'border-indigo-400 ring-today' : 'border-gray-100' }} hover:border-indigo-200 hover:shadow-md aspect-square flex flex-col items-center justify-center p-2 group cursor-pointer">

                                <!-- Background Circle with Glow -->
                                @if ($d['status'] === 'holiday')
                                    <!-- Holiday: Red background + Emoji behind -->
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <div class="w-16 h-16 rounded-full bg-red-500/15 glow-purple"></div>
                                    </div>
                                    <div class="absolute inset-0 flex items-center justify-center opacity-10 text-6xl">
                                        🎉
                                    </div>
                                @elseif($d['status'] === 'weekend')
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <div class="w-16 h-16 rounded-full bg-gray-300/30"></div>
                                    </div>
                                @elseif($d['status'] === 'permission')
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <div class="w-16 h-16 rounded-full bg-yellow-500/30 glow-yellow"></div>
                                    </div>
                                @elseif($d['status'] === 'late')
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <div class="w-16 h-16 rounded-full bg-red-500/30 glow-red"></div>
                                    </div>
                                @elseif($d['status'] === 'on_time')
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <div class="w-16 h-16 rounded-full bg-green-500/30 glow-green"></div>
                                    </div>
                                @elseif($d['status'] === 'alpha')
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <div class="w-16 h-16 rounded-full bg-gray-700/25 glow-gray"></div>
                                    </div>
                                @else
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <div class="w-16 h-16 rounded-full bg-gray-100"></div>
                                    </div>
                                @endif

                                <!-- Date Number (z-index above circle) -->
                                @if ($d['is_today'])
                                    <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                                        <div class="w-20 h-20 rounded-full bg-indigo-50 glow-blue"></div>
                                    </div>
                                @endif
                                <div class="relative z-10 flex flex-col items-center justify-center flex-1">
                                    <div
                                        class="text-2xl font-bold mb-0.5
                                        {{ $d['status'] === 'holiday' ? 'text-red-600' : 'text-gray-700' }}">
                                        {{ $d['date']->format('j') }}
                                    </div>

                                    <!-- Status Label -->
                                    @if ($d['status'] === 'holiday')
                                        <span class="text-[9px] text-red-700 font-bold uppercase tracking-wide">Libur
                                            Nasional</span>
                                    @elseif($d['status'] === 'weekend')
                                        <span class="text-[9px] text-gray-500 font-medium">Weekend</span>
                                    @elseif($d['status'] === 'permission')
                                        <span
                                            class="text-[9px] text-yellow-700 font-bold uppercase tracking-wide">Izin</span>
                                    @elseif($d['status'] === 'late')
                                        <span
                                            class="text-[9px] text-red-700 font-bold uppercase tracking-wide">Telat</span>
                                    @elseif($d['status'] === 'on_time')
                                        <span
                                            class="text-[9px] text-green-700 font-bold uppercase tracking-wide">Hadir</span>
                                    @elseif($d['status'] === 'alpha')
                                        <span
                                            class="text-[9px] text-gray-700 font-bold uppercase tracking-wide">Alpha</span>
                                    @elseif($d['status'] === 'not_checked_in')
                                        <span class="text-[9px] text-indigo-700 font-bold uppercase tracking-wide">Belum
                                            Absen</span>
                                    @endif
                                </div>

                                <!-- Tooltip -->
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
                                        <!-- Arrow -->
                                        <div
                                            class="absolute top-full border-[6px] border-transparent border-t-gray-900 {{ $arrowClass }}">
                                        </div>
                                    </div>
                                @endif

                                <!-- Today Badge -->
                                @if ($d['is_today'])
                                    <div
                                        class="absolute -top-1 -right-1 w-5 h-5 bg-indigo-500 rounded-full flex items-center justify-center shadow-lg z-10">
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

        <!-- Footer Info -->
        <div class="px-6 py-3 bg-gray-50/50 border-t border-gray-100">
            <p class="text-xs text-gray-500 text-center">
                Arahkan kursor ke tanggal untuk melihat detail kehadiran
            </p>
        </div>
    </div>
</x-filament-widgets::widget>
