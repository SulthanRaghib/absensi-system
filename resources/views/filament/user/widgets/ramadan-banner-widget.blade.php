{{-- =========================================================
     RAMADAN BANNER WIDGET
     Single Alpine.js scope — banner + modal in one wrapper
     ========================================================= --}}
<x-filament-widgets::widget>
    <style>
        /* ── Dark-mode refinements for Ramadan banner ── */

        /* Deepen the main card background so it reads as "dark-native" */
        .dark .rbw-card>.rbw-bg {
            background: linear-gradient(135deg, #064e3b 0%, #065f46 50%, #134e4a 100%) !important;
        }

        /* Sharper gold top/bottom accent lines */
        .dark .rbw-card .rbw-accent-top,
        .dark .rbw-card .rbw-accent-btm {
            opacity: 0.9 !important;
        }

        /* Badge: more visible gold ring */
        .dark .rbw-badge {
            background: rgba(234, 179, 8, 0.18) !important;
            border-color: rgba(234, 179, 8, 0.55) !important;
            color: #fde68a !important;
        }

        /* Quote text: slightly brighter */
        .dark .rbw-quote {
            color: rgba(209, 250, 229, 0.9) !important;
        }

        /* Schedule pills: white glass on deep bg */
        .dark .rbw-pill {
            background: rgba(255, 255, 255, 0.08) !important;
            border-color: rgba(255, 255, 255, 0.14) !important;
        }

        /* Countdown panel: deeper tint */
        .dark .rbw-countdown {
            background: rgba(0, 0, 0, 0.25) !important;
            border-color: rgba(255, 255, 255, 0.12) !important;
        }

        .dark .rbw-progress-track {
            background: rgba(255, 255, 255, 0.08) !important;
        }

        /* Modal: richer dark bg */
        .dark .rbw-modal-card {
            background: linear-gradient(135deg, #022c22 0%, #064e3b 60%, #0f3460 100%) !important;
            border-color: rgba(234, 179, 8, 0.45) !important;
        }

        .dark .rbw-modal-footer-btn {
            border-color: rgba(255, 255, 255, 0.15) !important;
            color: rgba(255, 255, 255, 0.55) !important;
        }

        .dark .rbw-modal-footer-btn:hover {
            border-color: rgba(255, 255, 255, 0.35) !important;
            color: #fff !important;
        }

        /* ── Animated hourglass ── */
        @keyframes rbw-hg-flip {
            0% {
                transform: rotate(0deg) scale(1);
            }

            40% {
                transform: rotate(0deg) scale(1);
            }

            50% {
                transform: rotate(180deg) scale(0.80);
            }

            90% {
                transform: rotate(180deg) scale(1);
            }

            100% {
                transform: rotate(360deg) scale(1);
            }
        }

        .rbw-hourglass {
            display: inline-block;
            animation: rbw-hg-flip 3s ease-in-out infinite;
            transform-origin: center;
            filter: drop-shadow(0 0 6px rgba(251, 191, 36, .6));
        }

        /* ── Seconds digit flash ── */
        @keyframes rbw-sec-pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: .45;
            }
        }

        .rbw-sec {
            animation: rbw-sec-pulse 1s ease-in-out infinite;
        }

        /* ── Progress bar shimmer ── */
        @keyframes rbw-shimmer {
            0% {
                background-position: -200% center;
            }

            100% {
                background-position: 200% center;
            }
        }

        .rbw-progress-bar {
            background-size: 200% auto;
            background-image: linear-gradient(90deg, #facc15 0%, #6ee7b7 40%, #facc15 100%);
            animation: rbw-shimmer 2.5s linear infinite;
        }
    </style>

    <div x-data="{
        open: false,
        page: 0,
        jokes: {{ Js::from($selectedJokes) }},

        /* ── Live countdown ── */
        iftarH: {{ (int) explode(':', $iftarTime)[0] }},
        iftarM: {{ (int) explode(':', $iftarTime)[1] }},
        startH: {{ (int) explode(':', $schedule['jam_masuk'])[0] }},
        startM: {{ (int) explode(':', $schedule['jam_masuk'])[1] }},
        endH: {{ (int) explode(':', $schedule['jam_pulang'])[0] }},
        endM: {{ (int) explode(':', $schedule['jam_pulang'])[1] }},
        hoursLeft: 0,
        minsLeft: 0,
        secsLeft: 0,
        pct: 0,
        done: false,

        tick() {
            const now = new Date();
            const iftar = new Date(now.getFullYear(), now.getMonth(), now.getDate(), this.iftarH, this.iftarM, 0);
            const diff = Math.floor((iftar - now) / 1000);
            if (diff <= 0) { this.done = true;
                this.hoursLeft = 0;
                this.minsLeft = 0;
                this.secsLeft = 0;
                this.pct = 100; return; }
            this.done = false;
            this.hoursLeft = Math.floor(diff / 3600);
            this.minsLeft = Math.floor((diff % 3600) / 60);
            this.secsLeft = diff % 60;
            const start = new Date(now.getFullYear(), now.getMonth(), now.getDate(), this.startH, this.startM, 0);
            const end = new Date(now.getFullYear(), now.getMonth(), now.getDate(), this.endH, this.endM, 0);
            const total = Math.max(1, end - start);
            const elapsed = now - start;
            this.pct = Math.min(100, Math.max(0, Math.round((elapsed / total) * 100)));
        },
        init() { this.tick();
            setInterval(() => this.tick(), 1000); },

        next() { this.page = (this.page + 1) % this.jokes.length; },
        prev() { this.page = (this.page - 1 + this.jokes.length) % this.jokes.length; }
    }" x-init="init()">

        {{-- ── BANNER CARD ────────────────────────────────────── --}}
        <div class="rbw-card relative overflow-hidden rounded-2xl shadow-2xl">

            {{-- Backgrounds --}}
            <div
                class="rbw-bg absolute inset-0 bg-gradient-to-br from-emerald-800 via-teal-700 to-emerald-900 pointer-events-none">
            </div>
            <div class="absolute -top-16 -right-16 w-64 h-64 bg-yellow-400/10 rounded-full blur-3xl pointer-events-none">
            </div>
            <div
                class="absolute -bottom-16 -left-16 w-72 h-72 bg-emerald-300/10 rounded-full blur-3xl pointer-events-none">
            </div>
            <div
                class="rbw-accent-top absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-yellow-400/70 to-transparent pointer-events-none">
            </div>
            <div
                class="rbw-accent-btm absolute inset-x-0 bottom-0 h-px bg-gradient-to-r from-transparent via-yellow-400/70 to-transparent pointer-events-none">
            </div>

            {{-- Content --}}
            <div class="relative z-10 p-5 md:p-7">

                {{-- Top badge row --}}
                <div class="flex items-center justify-between mb-5">
                    <span
                        class="rbw-badge inline-flex items-center gap-2 bg-yellow-400/20 border border-yellow-400/40
                             text-yellow-300 text-xs font-bold px-3 py-1 rounded-full uppercase tracking-widest">
                        🌙 Ramadan Mubarak {{ $hijriYear }} H
                    </span>
                    <div class="hidden sm:flex items-center gap-3 text-2xl opacity-50 select-none">
                        <span class="animate-pulse">🪔</span>
                        <span>✨</span>
                        <span class="animate-pulse" style="animation-delay:.4s">🪔</span>
                    </div>
                </div>

                {{-- Main 2-col grid --}}
                <div class="grid md:grid-cols-3 gap-5 items-stretch">

                    {{-- Left: Greeting + schedule + button --}}
                    <div class="md:col-span-2 flex flex-col gap-4">

                        <div>
                            <h2 class="text-2xl md:text-3xl font-extrabold text-white leading-tight">
                                Semangat Puasa, {{ Auth::user()->name }}! ✨
                            </h2>
                            <p
                                class="rbw-quote mt-2 text-emerald-100/80 text-sm md:text-[15px] leading-relaxed max-w-xl">
                                {{ $quoteOfDay }}
                            </p>
                        </div>

                        {{-- Schedule pills --}}
                        <div class="flex flex-wrap gap-2.5">
                            <div
                                class="rbw-pill flex items-center gap-2.5 bg-white/10 border border-white/20 rounded-xl px-4 py-2.5 text-white">
                                <span class="text-lg">🕗</span>
                                <div>
                                    <div class="text-[10px] text-emerald-200/70 uppercase tracking-wider">Jam Masuk
                                    </div>
                                    <div class="text-base font-bold">{{ $schedule['jam_masuk'] }}</div>
                                </div>
                            </div>
                            <div
                                class="rbw-pill flex items-center gap-2.5 bg-white/10 border border-white/20 rounded-xl px-4 py-2.5 text-white">
                                <span class="text-lg">🕔</span>
                                <div>
                                    <div class="text-[10px] text-emerald-200/70 uppercase tracking-wider">Jam Pulang
                                    </div>
                                    <div class="text-base font-bold">{{ $schedule['jam_pulang'] }}</div>
                                </div>
                            </div>
                            <div
                                class="flex items-center gap-2.5 bg-yellow-400/20 border border-yellow-400/30 rounded-xl px-4 py-2.5 text-yellow-200">
                                <span class="text-lg">🌙</span>
                                <div>
                                    <div class="text-[10px] uppercase tracking-wider opacity-70">Jadwal Aktif</div>
                                    <div class="text-base font-bold">Ramadan</div>
                                </div>
                            </div>
                        </div>

                        {{-- CTA Button --}}
                        <div>
                            <button @click="open = true"
                                class="group inline-flex items-center gap-2 bg-yellow-400 hover:bg-yellow-300
                                   text-emerald-900 font-bold text-sm px-5 py-2.5 rounded-xl
                                   shadow-lg shadow-yellow-400/25 hover:shadow-yellow-400/40
                                   transform hover:-translate-y-0.5 transition-all duration-200">
                                <span>🎯</span>
                                Tips &amp; Semangat Hari Ini
                                <svg class="w-4 h-4 transform group-hover:rotate-12 transition-transform duration-200"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </button>
                        </div>

                    </div>

                    {{-- Right: Live Countdown card (Alpine.js, no-refresh) --}}
                    <div
                        class="rbw-countdown flex flex-col items-center justify-center text-center
                            bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl p-5">

                        {{-- ── COUNTING DOWN ── --}}
                        <div x-show="!done">
                            {{-- Animated hourglass --}}
                            <div class="rbw-hourglass text-4xl mb-1 select-none">⏳</div>

                            <p class="text-[10px] text-emerald-200/70 uppercase tracking-widest font-semibold mb-3">
                                Menuju Jam Pulang
                            </p>

                            {{-- HH : MM : SS --}}
                            <div class="flex items-end justify-center gap-1 mb-1">
                                <template x-if="hoursLeft > 0">
                                    <span class="flex items-end gap-1 mr-1">
                                        <span class="text-4xl font-black text-yellow-300 tabular-nums leading-none"
                                            x-text="String(hoursLeft).padStart(2,'0')"></span>
                                        <span class="text-yellow-200/60 text-xs mb-1">jam</span>
                                    </span>
                                </template>
                                <span class="text-4xl font-black text-white tabular-nums leading-none"
                                    x-text="String(minsLeft).padStart(2,'0')"></span>
                                <span class="text-white/60 text-xs mb-1 mr-1">mnt</span>
                                <span class="rbw-sec text-2xl font-bold text-emerald-300 tabular-nums leading-none"
                                    x-text="String(secsLeft).padStart(2,'0')"></span>
                                <span class="text-emerald-400/50 text-xs mb-1">dtk</span>
                            </div>

                            <p class="text-xs text-emerald-300/80 mt-1">
                                Pulang pukul
                                <span class="font-bold text-yellow-300">{{ $iftarTime }}</span>
                            </p>

                            {{-- Animated progress bar --}}
                            <div class="rbw-progress-track w-full mt-4 bg-white/10 rounded-full h-2.5 overflow-hidden">
                                <div class="rbw-progress-bar h-full rounded-full transition-all duration-1000"
                                    :style="'width:' + pct + '%'"></div>
                            </div>
                            <p class="text-[11px] text-emerald-300/60 mt-1.5" x-text="pct + '% hari kerja dilalui'"></p>
                        </div>

                        {{-- ── TIME IS UP ── --}}
                        <div x-show="done">
                            <div class="text-5xl mb-3 animate-bounce select-none">🎉</div>
                            <p class="text-lg font-bold text-yellow-300 mb-1">Alhamdulillah!</p>
                            <p class="text-sm text-white/80">Waktu pulang sudah tiba.</p>
                            <p class="text-xs text-emerald-200 mt-2">Jangan lupa check-out! 😊</p>
                        </div>

                    </div>

                </div>{{-- /grid --}}
            </div>{{-- /content --}}
        </div>{{-- /banner card --}}


        {{-- ── MODAL ──────────────────────────────────────────── --}}
        {{-- Single x-show on the outermost wrapper only — no nested x-show to prevent double-transition flicker --}}
        <div x-show="open" x-transition:enter="transition ease-out duration-250"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95" class="fixed inset-0 z-50 flex items-center justify-center p-4"
            style="display:none;" @keydown.escape.window="open = false">
            <div class="absolute inset-0 bg-black/65 backdrop-blur-sm" @click="open = false"></div>

            <div class="relative z-10 w-full max-w-sm mx-auto">
                <div
                    class="rbw-modal-card relative overflow-hidden rounded-2xl shadow-2xl
                        bg-gradient-to-br from-emerald-800 via-emerald-900 to-teal-900
                        border border-yellow-400/30">

                    <div
                        class="absolute top-0 right-0 w-32 h-32 bg-yellow-400/10 rounded-full
                            -translate-y-1/2 translate-x-1/2 blur-2xl pointer-events-none">
                    </div>

                    {{-- Modal Header --}}
                    <div class="relative flex items-center justify-between p-5 pb-4 border-b border-white/10">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-yellow-400/20 rounded-xl flex items-center justify-center">
                                <span class="text-xl">🎯</span>
                            </div>
                            <div>
                                <h3 class="text-white font-bold text-base leading-none">Tips Ramadan</h3>
                                <p class="text-emerald-300 text-xs mt-0.5">Semangat &amp; jangan mokel ya! 😄</p>
                            </div>
                        </div>
                        <button @click="open = false"
                            class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 flex items-center justify-center
                               text-white/60 hover:text-white transition-colors duration-150">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    {{-- Joke carousel --}}
                    <div class="p-5">
                        <template x-for="(joke, i) in jokes" :key="i">
                            <div x-show="page === i"
                                class="text-center min-h-[140px] flex flex-col items-center justify-center">
                                <div class="text-5xl mb-4" x-text="joke.emoji"></div>
                                <h4 class="text-yellow-300 font-bold text-base mb-2" x-text="joke.title"></h4>
                                <p class="text-white/80 text-sm leading-relaxed" x-text="joke.text"></p>
                            </div>
                        </template>

                        {{-- Dots --}}
                        <div class="flex justify-center gap-2 mt-5">
                            <template x-for="(joke, i) in jokes" :key="i">
                                <button @click="page = i"
                                    :class="page === i ? 'bg-yellow-400 w-6' : 'bg-white/30 w-2.5 hover:bg-white/50'"
                                    class="h-2.5 rounded-full transition-all duration-300"></button>
                            </template>
                        </div>

                        {{-- Prev/Next --}}
                        <div class="flex gap-2.5 mt-4">
                            <button @click="prev()"
                                class="flex-1 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white text-sm
                                   font-medium transition-colors flex items-center justify-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 19l-7-7 7-7" />
                                </svg>
                                Sebelumnya
                            </button>
                            <button @click="next()"
                                class="flex-1 py-2.5 rounded-xl bg-yellow-400 hover:bg-yellow-300 text-emerald-900 text-sm
                                   font-bold transition-colors flex items-center justify-center gap-1">
                                Lanjut
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="px-5 pb-5">
                        <button @click="open = false"
                            class="rbw-modal-footer-btn w-full py-2 rounded-xl border border-white/20 hover:border-white/40
                               text-white/60 hover:text-white text-sm transition-colors duration-150">
                            Tutup — Bismillah, semangat! 💪
                        </button>
                    </div>

                    <div class="h-1 bg-gradient-to-r from-yellow-400 via-emerald-300 to-yellow-400"></div>
                </div>
            </div>
        </div>{{-- /modal --}}

    </div>{{-- /x-data --}}
</x-filament-widgets::widget>
