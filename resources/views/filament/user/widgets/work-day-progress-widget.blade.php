<x-filament-widgets::widget>
    <div x-data="{
        start: new Date('{{ $startIso }}'),
        end: new Date('{{ $endIso }}'),
        clockIn: '{{ $clockInIso }}' ? new Date('{{ $clockInIso }}') : null,
        clockOut: '{{ $clockOutIso }}' ? new Date('{{ $clockOutIso }}') : null,
        isCheckedIn: {{ $isCheckedIn ? 'true' : 'false' }},
        isCheckedOut: {{ $isCheckedOut ? 'true' : 'false' }},
        now: new Date(),
        timeString: '',
        percentage: 0,
        status: 'before',
        timeRemainingText: '',
    
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
            } else if (this.isCheckedIn) {
                this.status = 'in_progress';
                const total = this.end - this.start;
                const elapsed = this.now - this.start;
                this.percentage = Math.min(100, Math.max(0, Math.round((elapsed / total) * 100)));
    
                if (this.now >= this.end) {
                    this.timeRemainingText = 'Waktu kerja telah usai';
                } else {
                    this.timeRemainingText = this.niceDuration(this.end - this.now) + ' lagi';
                }
            } else {
                if (this.now < this.start) {
                    const diff = this.start - this.now;
                    this.timeRemainingText = 'Mulai dalam ' + this.niceDuration(diff);
                    this.status = 'before';
                    this.percentage = 0;
                } else {
                    this.status = 'not_checked_in';
                    this.timeRemainingText = 'Belum Absen Masuk';
                    this.percentage = 0;
                }
            }
        }
    }" x-init="init()"
        class="relative overflow-hidden bg-white rounded-2xl shadow-lg border border-gray-200/60 hover:shadow-xl transition-shadow duration-300">

        <!-- Gradient Accent Border (Top) -->
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r"
            :class="{
                'from-blue-400 via-blue-500 to-cyan-400': status === 'before',
                'from-amber-400 via-orange-500 to-amber-400': status === 'not_checked_in',
                'from-emerald-400 via-green-500 to-teal-400': status === 'in_progress',
                'from-violet-400 via-purple-500 to-fuchsia-400': status === 'finished_work'
            }">
        </div>

        <div class="p-4 sm:p-6 lg:p-8">
            <!-- Header Section -->
            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3 sm:gap-4 mb-4 sm:mb-6">
                <!-- Left: Icon + Status -->
                <div class="flex items-center gap-2 sm:gap-3">
                    <!-- Dynamic Icon -->
                    <div class="flex-shrink-0 w-10 h-10 sm:w-12 sm:h-12 rounded-lg sm:rounded-xl flex items-center justify-center shadow-md transition-all duration-300"
                        :class="{
                            'bg-gradient-to-br from-blue-100 to-cyan-50 text-blue-600': status === 'before',
                            'bg-gradient-to-br from-amber-100 to-orange-50 text-amber-600': status === 'not_checked_in',
                            'bg-gradient-to-br from-emerald-100 to-teal-50 text-emerald-600': status === 'in_progress',
                            'bg-gradient-to-br from-violet-100 to-purple-50 text-violet-600': status === 'finished_work'
                        }">
                        <svg x-show="status === 'before'" class="w-5 h-5 sm:w-6 sm:h-6" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <svg x-show="status === 'not_checked_in'" class="w-5 h-5 sm:w-6 sm:h-6 animate-pulse"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <svg x-show="status === 'in_progress'" class="w-5 h-5 sm:w-6 sm:h-6" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <svg x-show="status === 'finished_work'" class="w-5 h-5 sm:w-6 sm:h-6" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>

                    <!-- Status Text -->
                    <div class="min-w-0 flex-1">
                        <div class="inline-flex items-center gap-1.5 sm:gap-2 px-2 sm:px-3 py-1 sm:py-1.5 rounded-lg text-[10px] sm:text-xs font-semibold tracking-wide transition-all duration-300"
                            :class="{
                                'bg-blue-50 text-blue-700 ring-1 ring-blue-200': status === 'before',
                                'bg-amber-50 text-amber-700 ring-1 ring-amber-200 animate-pulse': status === 'not_checked_in',
                                'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200': status === 'in_progress',
                                'bg-violet-50 text-violet-700 ring-1 ring-violet-200': status === 'finished_work'
                            }">
                            <span x-show="status === 'before'">Belum Dimulai</span>
                            <span x-show="status === 'not_checked_in'"><span class="hidden xs:inline">⚠️ </span>Belum
                                Absen</span>
                            <span x-show="status === 'in_progress'"><span class="hidden xs:inline">● </span>Sedang
                                Bekerja</span>
                            <span x-show="status === 'finished_work'"><span class="hidden xs:inline">✓ </span>Selesai
                                Bekerja</span>
                        </div>
                        <p class="text-[10px] sm:text-xs text-gray-500 mt-1 sm:mt-1.5 font-medium hidden sm:block">
                            Progres Hari Kerja</p>
                    </div>
                </div>

                <!-- Right: Time + Percentage -->
                <div class="text-right flex-shrink-0 sm:ml-auto">
                    <div class="text-2xl sm:text-3xl lg:text-4xl font-bold bg-gradient-to-br from-gray-800 to-gray-600 bg-clip-text text-transparent tabular-nums"
                        x-text="timeString">
                        --:--:--
                    </div>
                    <div class="text-xs sm:text-sm font-semibold text-gray-500 mt-0.5 sm:mt-1"
                        x-text="percentage + '%'">0%</div>
                </div>
            </div>

            <!-- Progress Bar Section -->
            <div class="space-y-2 sm:space-y-3">
                <!-- Progress Container -->
                <div class="relative w-full h-6 sm:h-8 bg-gray-100 rounded-full overflow-hidden shadow-inner">
                    <!-- Progress Fill -->
                    <div class="absolute top-0 left-0 h-full transition-all duration-1000 ease-out rounded-full"
                        :style="`width: ${percentage}%`"
                        :class="{
                            'bg-gradient-to-r from-blue-400 via-blue-500 to-cyan-500': status === 'before',
                            'bg-gradient-to-r from-amber-400 via-orange-500 to-amber-500': status === 'not_checked_in',
                            'bg-gradient-to-r from-emerald-400 via-green-500 to-teal-500': status === 'in_progress',
                            'bg-gradient-to-r from-violet-400 via-purple-500 to-fuchsia-500': status === 'finished_work'
                        }">
                        <!-- Shimmer Effect -->
                        <div
                            class="absolute inset-0 bg-gradient-to-r from-transparent via-white/30 to-transparent animate-shimmer">
                        </div>
                    </div>

                    <!-- Percentage Text Inside Bar (when > 30%) -->
                    <div x-show="percentage > 30" class="absolute inset-0 flex items-center px-2 sm:px-4">
                        <span class="text-[10px] sm:text-xs font-bold text-white drop-shadow-md"
                            x-text="percentage + '%'"></span>
                    </div>
                </div>

                <!-- Time Remaining Info -->
                <div
                    class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-0 text-xs sm:text-sm">
                    <div class="flex items-center gap-1.5 sm:gap-2 text-gray-600">
                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 flex-shrink-0" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="font-medium">{{ $start->format('H:i') }} — {{ $end->format('H:i') }}</span>
                    </div>
                    <div class="font-semibold transition-colors duration-300 pl-5 sm:pl-0"
                        :class="{
                            'text-blue-600': status === 'before',
                            'text-amber-600': status === 'not_checked_in',
                            'text-emerald-600': status === 'in_progress',
                            'text-violet-600': status === 'finished_work'
                        }">
                        <span x-text="timeRemainingText"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Accent (subtle) -->
        <div class="h-0.5 bg-gradient-to-r from-transparent via-gray-200 to-transparent"></div>
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
</x-filament-widgets::widget>
