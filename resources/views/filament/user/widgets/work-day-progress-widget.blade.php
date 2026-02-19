<x-filament-widgets::widget>
    {{-- Dark-mode overrides for work-day-progress --}}
    <style>
        .dark .wdp-card {
            background: #111827 !important;
            background-image: none !important;
            border-color: #374151 !important;
        }

        .dark .wdp-holiday-card {
            background: #111827 !important;
            border-color: #374151 !important;
        }

        .dark .wdp-holiday-title {
            color: #fca5a5 !important;
        }

        .dark .wdp-holiday-text {
            color: #d1d5db !important;
        }

        .dark .wdp-holiday-h2 {
            color: #f3f4f6 !important;
        }

        .dark .wdp-holiday-illus {
            background: rgba(255, 255, 255, 0.05) !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
        }

        .dark .wdp-subtitle {
            color: #9ca3af !important;
        }

        .dark .wdp-ramadan-hint {
            background: rgba(251, 191, 36, 0.1) !important;
            border-color: rgba(251, 191, 36, 0.2) !important;
            color: #fcd34d !important;
        }

        .dark .wdp-track {
            background: #1f2937 !important;
        }

        .dark .wdp-schedule-text {
            color: #9ca3af !important;
        }

        .dark .wdp-watermark {
            opacity: 0.04 !important;
        }

        /* Status badge dark overrides */
        .dark .wdp-status-badge[class*="bg-blue-50"],
        .dark .wdp-status-badge[class*="bg-amber-50"],
        .dark .wdp-status-badge[class*="bg-emerald-50"],
        .dark .wdp-status-badge[class*="bg-violet-50"],
        .dark .wdp-status-badge[class*="bg-teal-50"],
        .dark .wdp-status-badge[class*="bg-orange-50"] {
            background: rgba(255, 255, 255, 0.08) !important;
        }

        .dark .wdp-status-badge {
            --tw-ring-color: rgba(255, 255, 255, 0.15) !important;
        }

        /* Ramadan badge dark */
        .dark .wdp-ramadan-badge {
            background: rgba(251, 191, 36, 0.12) !important;
            color: #fcd34d !important;
            --tw-ring-color: rgba(251, 191, 36, 0.25) !important;
        }

        /* Icon box dark */
        .dark .wdp-icon-box[class*="from-blue-100"],
        .dark .wdp-icon-box[class*="from-amber-100"],
        .dark .wdp-icon-box[class*="from-emerald-100"],
        .dark .wdp-icon-box[class*="from-violet-100"],
        .dark .wdp-icon-box[class*="from-teal-100"],
        .dark .wdp-icon-box[class*="from-orange-100"] {
            background: rgba(255, 255, 255, 0.08) !important;
            background-image: none !important;
        }

        /* Time display dark */
        .dark .wdp-time {
            -webkit-text-fill-color: unset !important;
            background: none !important;
            -webkit-background-clip: unset !important;
            background-clip: unset !important;
            color: #f3f4f6 !important;
        }

        .dark .wdp-percentage {
            color: #9ca3af !important;
        }
    </style>

    @if (!empty($holiday))
        <!-- Holiday Card -->
        <div
            class="wdp-holiday-card relative overflow-hidden bg-white rounded-2xl shadow-lg border border-red-100 hover:shadow-xl transition-shadow duration-300 group">
            <!-- Background Decoration -->
            <div
                class="absolute inset-0 opacity-10 bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI4MCIgaGVpZ2h0PSI4MCIgdmlld0JveD0iMCAwIDgwIDgwIj48ZyBmaWxsPSIjZWY0NDQ0IiBmaWxsLW9wYWNpdHk9IjAuNCI+PHBhdGggZD0iTTAgMGg4MHY4MEgwVjB6bTQwIDQwaDQwdjQwSDQwVjQwem0wLTQwaDQwdjQwSDQwVjB6bjAtNDBoNDB2NDBINDBWMHoiLz48L2c+PC9zdmc+')]">
            </div>

            <!-- Confetti Gradient -->
            <div class="absolute inset-0 bg-gradient-to-br from-red-50 to-orange-50 opacity-90 dark:opacity-20"></div>

            <div class="relative p-6 sm:p-8 flex items-center justify-between gap-6">
                <!-- Left Content -->
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-2">
                        <span
                            class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-red-100 text-red-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </span>
                        <h3 class="wdp-holiday-title text-sm font-bold tracking-wider text-red-600 uppercase">Libur
                            Nasional</h3>
                    </div>

                    <h2 class="wdp-holiday-h2 text-2xl sm:text-3xl font-bold text-gray-800 mb-2 leading-tight">
                        {{ $holiday }}
                    </h2>

                    <p class="wdp-holiday-text text-gray-600 font-medium">Selamat beristirahat dan menikmati waktu luang
                        Anda! 🎉</p>
                </div>

                <!-- Right lustration/Icon -->
                <div
                    class="wdp-holiday-illus hidden sm:flex flex-shrink-0 items-center justify-center w-24 h-24 bg-white/50 backdrop-blur-sm rounded-2xl shadow-sm border border-white/50">
                    <span class="text-5xl transform group-hover:scale-110 transition-transform duration-300">🍵</span>
                </div>
            </div>

            <!-- Bottom Border -->
            <div
                class="absolute bottom-0 left-0 right-0 h-1.5 bg-gradient-to-r from-red-400 via-orange-400 to-yellow-400">
            </div>
        </div>
    @else
        <!-- Work Day Progress -->
        <div x-data="{
            start: new Date('{{ $startIso }}'),
            end: new Date('{{ $endIso }}'),
            clockIn: '{{ $clockInIso }}' ? new Date('{{ $clockInIso }}') : null,
            clockOut: '{{ $clockOutIso }}' ? new Date('{{ $clockOutIso }}') : null,
            isCheckedIn: {{ $isCheckedIn ? 'true' : 'false' }},
            isCheckedOut: {{ $isCheckedOut ? 'true' : 'false' }},
            isRamadan: {{ $isRamadan ? 'true' : 'false' }},
            now: new Date(),
            timeString: '',
            percentage: 0,
            status: 'before',
            timeRemainingText: '',
            ramadanHint: '',

            init() {
                this.update();
                setInterval(() => this.update(), 1000);
            },

            niceDuration(diffMs) {
                const totalSeconds = Math.max(0, Math.floor(diffMs / 1000));
                const hours = Math.floor(totalSeconds / 3600);
                const minutes = Math.floor((totalSeconds % 3600) / 60);
                const seconds = totalSeconds % 60;

                if (hours > 0) return `${hours} Jam ${minutes} Menit`;
                if (minutes > 0) return `${minutes} Menit ${seconds} Detik`;
                return `${seconds} Detik`;
            },

            update() {
                this.now = new Date();
                this.timeString = this.now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });

                if (this.isCheckedOut) {
                    this.status = 'finished_work';
                    this.percentage = 100;
                    const diff = this.clockOut - this.clockIn;
                    this.timeRemainingText = 'Total jam kerja: ' + this.niceDuration(diff);
                    this.ramadanHint = 'Alhamdulillah, waktunya berbuka! 🌙';
                } else if (this.isCheckedIn) {
                    this.status = 'in_progress';
                    const total = this.end - this.start;
                    const elapsed = this.now - this.start;
                    this.percentage = Math.min(100, Math.max(0, Math.round((elapsed / total) * 100)));

                    if (this.now >= this.end) {
                        this.timeRemainingText = 'Waktu kerja telah usai';
                        this.ramadanHint = 'Waktunya buka puasa! 🥤';
                    } else {
                        this.timeRemainingText = this.niceDuration(this.end - this.now) + ' lagi';
                        this.ramadanHint = 'Tahan dulu, buka puasa sebentar lagi 💪';
                    }
                } else {
                    if (this.now < this.start) {
                        const diff = this.start - this.now;
                        this.timeRemainingText = 'Mulai dalam ' + this.niceDuration(diff);
                        this.status = 'before';
                        this.percentage = 0;
                        this.ramadanHint = 'Ngantuk? Itu efek sahur, bukan malas! 😴';
                    } else {
                        this.status = 'not_checked_in';
                        this.timeRemainingText = 'Belum Absen Masuk';
                        this.percentage = 0;
                        this.ramadanHint = 'Lapar bukan alasan skip absen ya! 😅';
                    }
                }
            }
        }" x-init="init()"
            class="wdp-card relative overflow-hidden rounded-2xl shadow-lg border transition-shadow duration-300 hover:shadow-xl"
            :class="isRamadan
                ?
                'bg-gradient-to-br from-amber-50 via-white to-teal-50 border-amber-200/60' :
                'bg-white border-gray-200/60'">

            <!-- Gradient Accent Border (Top) -->
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r transition-all duration-700"
                :class="isRamadan
                    ?
                    'from-amber-300 via-yellow-400 to-amber-300' : {
                        'from-blue-400 via-blue-500 to-cyan-400': status === 'before',
                        'from-amber-400 via-orange-500 to-amber-400': status === 'not_checked_in',
                        'from-emerald-400 via-green-500 to-teal-400': status === 'in_progress',
                        'from-violet-400 via-purple-500 to-fuchsia-400': status === 'finished_work'
                    }">
            </div>

            <!-- Ramadan: subtle star/crescent SVG watermark (decorative, far right) -->
            @if ($isRamadan)
                <div
                    class="wdp-watermark absolute top-3 right-3 sm:top-4 sm:right-5 opacity-[0.07] pointer-events-none select-none">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64"
                        class="w-20 h-20 sm:w-28 sm:h-28 text-amber-600 fill-current">
                        <path
                            d="M32 4C16.536 4 4 16.536 4 32s12.536 28 28 28 28-12.536 28-28S47.464 4 32 4zm8 38c-7.732 0-14-6.268-14-14 0-4.418 2.05-8.358 5.264-11A16 16 0 1040 42z" />
                    </svg>
                </div>
            @endif

            <div class="p-4 sm:p-6 lg:p-8">
                <!-- Header Section -->
                <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3 sm:gap-4 mb-4 sm:mb-6">
                    <!-- Left: Icon + Status -->
                    <div class="flex items-center gap-2 sm:gap-3">
                        <!-- Dynamic Icon -->
                        <div class="wdp-icon-box flex-shrink-0 w-10 h-10 sm:w-12 sm:h-12 rounded-lg sm:rounded-xl flex items-center justify-center shadow-md transition-all duration-300"
                            :class="isRamadan
                                ?
                                {
                                    'bg-gradient-to-br from-amber-100 to-yellow-50 text-amber-600': status === 'before',
                                    'bg-gradient-to-br from-orange-100 to-amber-50 text-orange-600': status === 'not_checked_in',
                                    'bg-gradient-to-br from-teal-100 to-cyan-50 text-teal-600': status === 'in_progress',
                                    'bg-gradient-to-br from-emerald-100 to-green-50 text-emerald-600': status === 'finished_work'
                                } : {
                                    'bg-gradient-to-br from-blue-100 to-cyan-50 text-blue-600': status === 'before',
                                    'bg-gradient-to-br from-amber-100 to-orange-50 text-amber-600': status === 'not_checked_in',
                                    'bg-gradient-to-br from-emerald-100 to-teal-50 text-emerald-600': status === 'in_progress',
                                    'bg-gradient-to-br from-violet-100 to-purple-50 text-violet-600': status === 'finished_work'
                                }">
                            <!-- Ramadan: crescent moon icon for 'before' and 'in_progress' -->
                            <template x-if="isRamadan && (status === 'before' || status === 'in_progress')">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M21.752 15.002A9.72 9.72 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                                </svg>
                            </template>
                            <!-- Normal: clock icon for 'before' -->
                            <svg x-show="!isRamadan && status === 'before'" class="w-5 h-5 sm:w-6 sm:h-6" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <!-- Warning icon for 'not_checked_in' -->
                            <svg x-show="status === 'not_checked_in'" class="w-5 h-5 sm:w-6 sm:h-6 animate-pulse"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <!-- Play icon for 'in_progress' (normal) -->
                            <svg x-show="!isRamadan && status === 'in_progress'" class="w-5 h-5 sm:w-6 sm:h-6"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <!-- Check icon for 'finished_work' -->
                            <svg x-show="status === 'finished_work'" class="w-5 h-5 sm:w-6 sm:h-6" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>

                        <!-- Status Text -->
                        <div class="min-w-0 flex-1">
                            <div class="wdp-status-badge inline-flex items-center gap-1.5 sm:gap-2 px-2 sm:px-3 py-1 sm:py-1.5 rounded-lg text-[10px] sm:text-xs font-semibold tracking-wide transition-all duration-300"
                                :class="isRamadan
                                    ?
                                    {
                                        'bg-amber-50 text-amber-700 ring-1 ring-amber-300': status === 'before',
                                        'bg-orange-50 text-orange-700 ring-1 ring-orange-300 animate-pulse': status === 'not_checked_in',
                                        'bg-teal-50 text-teal-700 ring-1 ring-teal-300': status === 'in_progress',
                                        'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-300': status === 'finished_work'
                                    } : {
                                        'bg-blue-50 text-blue-700 ring-1 ring-blue-200': status === 'before',
                                        'bg-amber-50 text-amber-700 ring-1 ring-amber-200 animate-pulse': status === 'not_checked_in',
                                        'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200': status === 'in_progress',
                                        'bg-violet-50 text-violet-700 ring-1 ring-violet-200': status === 'finished_work'
                                    }">
                                <span x-show="status === 'before'">
                                    <span x-show="isRamadan">🌙 </span>Belum Dimulai
                                </span>
                                <span x-show="status === 'not_checked_in'">
                                    <span x-show="isRamadan">🕌 </span><span x-show="!isRamadan">⚠️ </span>Belum Absen
                                </span>
                                <span x-show="status === 'in_progress'">
                                    <span x-show="isRamadan">🌙 </span><span x-show="!isRamadan">● </span>Sedang
                                    Bekerja
                                </span>
                                <span x-show="status === 'finished_work'">
                                    <span x-show="isRamadan">🌙 </span><span x-show="!isRamadan">✓ </span>Selesai
                                    Bekerja
                                </span>
                            </div>

                            <!-- Subtitle: Progres label + Ramadan badge -->
                            <div class="flex items-center gap-2 mt-1 sm:mt-1.5">
                                <p class="wdp-subtitle text-[10px] sm:text-xs font-medium hidden sm:block"
                                    :class="isRamadan ? 'text-amber-600' : 'text-gray-500'">
                                    <span x-show="!isRamadan">Progres Hari Kerja</span>
                                    <span x-show="isRamadan">Progres Hari Kerja</span>
                                </p>
                                @if ($isRamadan)
                                    <span
                                        class="wdp-ramadan-badge hidden sm:inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md text-[9px] font-bold tracking-wider bg-amber-100 text-amber-700 ring-1 ring-amber-300 uppercase">
                                        🌙 Ramadan
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Right: Time + Percentage -->
                    <div class="text-right flex-shrink-0 sm:ml-auto">
                        <div class="wdp-time text-2xl sm:text-3xl lg:text-4xl font-bold tabular-nums bg-clip-text text-transparent"
                            :class="isRamadan
                                ?
                                'bg-gradient-to-br from-amber-700 via-orange-600 to-amber-500' :
                                'bg-gradient-to-br from-gray-800 to-gray-600'"
                            x-text="timeString">
                            --:--:--
                        </div>
                        <div class="wdp-percentage text-xs sm:text-sm font-semibold mt-0.5 sm:mt-1 transition-colors duration-300"
                            :class="isRamadan ? 'text-amber-600' : 'text-gray-500'" x-text="percentage + '%'">0%</div>
                    </div>
                </div>

                <!-- Progress Bar Section -->
                <div class="space-y-2 sm:space-y-3">
                    <!-- Progress Track -->
                    <div class="wdp-track relative w-full h-6 sm:h-8 rounded-full overflow-hidden shadow-inner transition-colors duration-500"
                        :class="isRamadan ? 'bg-amber-100/70' : 'bg-gray-100'">
                        <!-- Progress Fill -->
                        <div class="absolute top-0 left-0 h-full transition-all duration-1000 ease-out rounded-full"
                            :style="`width: ${percentage}%`"
                            :class="isRamadan
                                ?
                                {
                                    'bg-gradient-to-r from-amber-300 via-yellow-400 to-amber-400': status === 'before',
                                    'bg-gradient-to-r from-orange-400 via-red-400 to-orange-500': status === 'not_checked_in',
                                    'bg-gradient-to-r from-teal-400 via-teal-500 to-cyan-400': status === 'in_progress',
                                    'bg-gradient-to-r from-emerald-400 via-green-500 to-teal-400': status === 'finished_work'
                                } : {
                                    'bg-gradient-to-r from-blue-400 via-blue-500 to-cyan-500': status === 'before',
                                    'bg-gradient-to-r from-amber-400 via-orange-500 to-amber-500': status === 'not_checked_in',
                                    'bg-gradient-to-r from-emerald-400 via-green-500 to-teal-500': status === 'in_progress',
                                    'bg-gradient-to-r from-violet-400 via-purple-500 to-fuchsia-500': status === 'finished_work'
                                }">
                            <!-- Shimmer -->
                            <div
                                class="absolute inset-0 bg-gradient-to-r from-transparent via-white/30 to-transparent animate-shimmer">
                            </div>
                        </div>

                        <!-- Percentage Label Inside Bar -->
                        <div x-show="percentage > 30" class="absolute inset-0 flex items-center px-2 sm:px-4">
                            <span class="text-[10px] sm:text-xs font-bold text-white drop-shadow-md"
                                x-text="percentage + '%'"></span>
                        </div>
                    </div>

                    <!-- Time Range + Remaining Info -->
                    <div
                        class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-0 text-xs sm:text-sm">
                        <div class="wdp-schedule-text flex items-center gap-1.5 sm:gap-2"
                            :class="isRamadan ? 'text-amber-700' : 'text-gray-600'">
                            @if ($isRamadan)
                                <span class="text-base leading-none">🌙</span>
                                <span class="font-semibold">{{ $start->format('H:i') }} —
                                    {{ $end->format('H:i') }}</span>
                                <span class="text-[10px] font-medium opacity-70 italic">Jadwal Ramadan</span>
                            @else
                                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 flex-shrink-0" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="font-medium">{{ $start->format('H:i') }} —
                                    {{ $end->format('H:i') }}</span>
                            @endif
                        </div>

                        <!-- Right: time remaining -->
                        <div class="font-semibold transition-colors duration-300 pl-5 sm:pl-0"
                            :class="isRamadan
                                ?
                                {
                                    'text-amber-600': status === 'before',
                                    'text-orange-600': status === 'not_checked_in',
                                    'text-teal-600': status === 'in_progress',
                                    'text-emerald-600': status === 'finished_work'
                                } : {
                                    'text-blue-600': status === 'before',
                                    'text-amber-600': status === 'not_checked_in',
                                    'text-emerald-600': status === 'in_progress',
                                    'text-violet-600': status === 'finished_work'
                                }">
                            <span x-text="timeRemainingText"></span>
                        </div>
                    </div>

                    <!-- Ramadan hint row (humor/motivation) -->
                    @if ($isRamadan)
                        <div x-show="ramadanHint"
                            class="wdp-ramadan-hint mt-1 flex items-center gap-2 px-3 py-1.5 rounded-lg bg-amber-50 border border-amber-200/70 text-[10px] sm:text-xs text-amber-700 italic">
                            <span x-text="ramadanHint"></span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Bottom Accent -->
            <div class="h-0.5 bg-gradient-to-r transition-colors duration-500"
                :class="isRamadan
                    ?
                    'from-transparent via-amber-300 to-transparent' :
                    'from-transparent via-gray-200 to-transparent'">
            </div>
        </div>

        <style>
            @keyframes shimmer {
                0% {
                    transform: translateX(-100%);
                }

                100% {
                    transform: translateX(100%);
                }
            }

            .animate-shimmer {
                animation: shimmer 3s infinite;
            }
        </style>
    @endif
</x-filament-widgets::widget>
