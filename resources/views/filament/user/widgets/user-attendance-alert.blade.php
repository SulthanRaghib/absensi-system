<x-filament-widgets::widget>
    @if (!($isHoliday ?? false))

        @if ($isRamadan ?? false)
            {{-- ── RAMADAN-THEMED ALERT ─────────────────────────── --}}
            <div
                class="uaa-ramadan relative overflow-hidden bg-gradient-to-br from-amber-50 via-yellow-50 to-orange-50
                        border border-amber-300 rounded-2xl shadow-lg p-6
                        flex flex-col md:flex-row items-center justify-between gap-6">

                <div
                    class="absolute top-0 right-0 w-28 h-28 bg-amber-200/30 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2 pointer-events-none">
                </div>
                <div
                    class="absolute bottom-0 left-0 w-24 h-24 bg-yellow-200/30 rounded-full blur-2xl translate-y-1/2 -translate-x-1/2 pointer-events-none">
                </div>

                {{-- Left --}}
                <div class="relative flex items-start gap-4 w-full md:w-auto">
                    <div
                        class="w-12 h-12 bg-gradient-to-br from-amber-500 to-yellow-500
                                rounded-xl flex items-center justify-center shadow-lg text-2xl
                                transform hover:scale-110 transition-transform duration-300">
                        🌙
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-amber-800 mb-0.5">
                            Belum Check-In — Bulan Ramadan nih!
                        </h3>
                        <div class="h-1 w-16 bg-gradient-to-r from-amber-500 to-yellow-400 rounded-full mb-2"></div>
                        <p class="text-amber-700 text-sm leading-relaxed">
                            Puasa boleh, <strong>tapi absen jangan sampai bolong!</strong>
                            Jam masuk Ramadan: <span class="font-bold text-amber-900">{{ $jamMasuk }}</span>. Yuk
                            check-in sekarang 💪
                        </p>
                    </div>
                </div>

                {{-- Button --}}
                <div class="relative w-full md:w-auto flex justify-end">
                    <x-filament::button tag="a" href="{{ route('filament.user.pages.absensi') }}" color="warning"
                        class="group relative px-6 py-3 font-semibold rounded-xl shadow-lg
                               hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-300
                               w-full md:w-auto">
                        <span class="flex items-center gap-2">
                            🕗 Absen Sekarang
                            <svg class="w-5 h-5 transform group-hover:translate-x-1 transition-transform duration-300"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </span>
                    </x-filament::button>
                </div>

                <div
                    class="absolute bottom-0 inset-x-0 h-1.5 bg-gradient-to-r from-amber-400 via-yellow-400 to-amber-400">
                </div>
            </div>
        @else
            {{-- ── NORMAL ALERT ──────────────────────────────────── --}}
            <div
                class="uaa-normal relative overflow-hidden bg-gradient-to-br from-red-50 via-orange-50 to-amber-50
                   border border-red-200 rounded-2xl shadow-xl p-6 backdrop-blur-sm
                   flex flex-col md:flex-row items-center justify-between gap-6">

                <div
                    class="absolute top-0 right-0 w-32 h-32 bg-red-200/30 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2 pointer-events-none">
                </div>
                <div
                    class="absolute bottom-0 left-0 w-24 h-24 bg-orange-200/30 rounded-full blur-2xl translate-y-1/2 -translate-x-1/2 pointer-events-none">
                </div>

                <div class="relative flex items-start gap-4 w-full md:w-auto">
                    <div
                        class="w-12 h-12 bg-gradient-to-br from-red-500 to-orange-500
                                rounded-xl flex items-center justify-center shadow-lg
                                transform hover:scale-110 transition-transform duration-300">
                        @svg('heroicon-o-exclamation-triangle', 'w-7 h-7 text-white')
                    </div>
                    <div>
                        <h3
                            class="uaa-title text-xl font-bold bg-gradient-to-r from-red-600 to-orange-600 bg-clip-text text-transparent mb-1">
                            Peringatan Absensi
                        </h3>
                        <div class="h-1 w-16 bg-gradient-to-r from-red-500 to-orange-500 rounded-full mb-2"></div>
                        <p class="text-gray-700 leading-relaxed text-sm">
                            Anda belum melakukan absensi hari ini. Harap segera Check-In!
                        </p>
                    </div>
                </div>

                <div class="relative w-full md:w-auto flex justify-end">
                    <x-filament::button tag="a" href="{{ route('filament.user.pages.absensi') }}" color="warning"
                        class="group relative px-6 py-3 bg-gradient-to-r from-red-500 via-orange-500 to-red-500
                           bg-size-200 bg-pos-0 hover:bg-pos-100 text-white font-semibold rounded-xl
                           shadow-lg hover:shadow-xl transform hover:-translate-y-0.5
                           transition-all duration-300 overflow-hidden w-full md:w-auto">
                        <span class="relative z-10 flex items-center gap-2">
                            Absen Sekarang
                            <svg class="w-5 h-5 transform group-hover:translate-x-1 transition-transform duration-300"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </span>
                        <div
                            class="absolute inset-0 bg-white/20 transform scale-x-0 group-hover:scale-x-100
                                transition-transform duration-300 origin-left">
                        </div>
                    </x-filament::button>
                </div>

                <div class="absolute bottom-0 inset-x-0 h-1.5 bg-gradient-to-r from-red-500 via-orange-500 to-red-500">
                </div>
            </div>
        @endif
    @else
        {{-- ── HOLIDAY MESSAGE ──────────────────────────────── --}}
        <div
            class="uaa-holiday p-6 bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200 rounded-2xl shadow-sm text-center">
            <div class="mb-3 flex justify-center">
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                    <span class="text-2xl">🎉</span>
                </div>
            </div>
            <h3 class="text-lg font-semibold text-blue-900 mb-1">Selamat Libur!</h3>
            <p class="text-blue-700 text-sm">Hari ini adalah hari libur. Nikmati waktu istirahat Anda.</p>
        </div>
    @endif

    <style>
        .bg-size-200 {
            background-size: 200% 100%;
        }

        .bg-pos-0 {
            background-position: 0% 0%;
        }

        .bg-pos-100 {
            background-position: 100% 0%;
        }

        /* ── Dark-mode overrides ── */
        .dark .uaa-ramadan {
            background: #111827 !important;
            background-image: none !important;
            border-color: rgba(251, 191, 36, 0.3) !important;
        }

        .dark .uaa-ramadan .text-amber-800 {
            color: #fbbf24 !important;
        }

        .dark .uaa-ramadan .text-amber-700 {
            color: #fcd34d !important;
        }

        .dark .uaa-ramadan .text-amber-900 {
            color: #fde68a !important;
        }

        .dark .uaa-normal {
            background: #111827 !important;
            background-image: none !important;
            border-color: rgba(239, 68, 68, 0.3) !important;
        }

        .dark .uaa-normal .uaa-title {
            -webkit-text-fill-color: unset !important;
            background: none !important;
            background-clip: unset !important;
            -webkit-background-clip: unset !important;
            color: #f87171 !important;
        }

        .dark .uaa-normal .text-gray-700 {
            color: #d1d5db !important;
        }

        .dark .uaa-holiday {
            background: #111827 !important;
            background-image: none !important;
            border-color: rgba(59, 130, 246, 0.3) !important;
        }

        .dark .uaa-holiday .text-blue-900 {
            color: #93c5fd !important;
        }

        .dark .uaa-holiday .text-blue-700 {
            color: #93c5fd !important;
        }

        .dark .uaa-holiday [class*="bg-blue-100"] {
            background-color: rgba(59, 130, 246, 0.15) !important;
        }
    </style>
</x-filament-widgets::widget>
