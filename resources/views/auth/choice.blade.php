<!doctype html>
<html lang="id">

<head>
    <x-head title="Masuk — Absensi">
        <!-- Tailwind (Keep existing) -->
        <script src="https://cdn.tailwindcss.com"></script>

        <!-- Alpine.js (Keep existing) -->
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

        <!-- Leaflet (Keep existing) -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>

        <!-- Face API -->
        <script src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api/dist/face-api.min.js"></script>

        @include('auth.partials.styles')

        @if ($isRamadan)
            <style>
                /* ═══ RAMADAN THEME ═══════════════════════════════════════════ */
                .app-wrapper.ramadan-bg {
                    background: linear-gradient(160deg, #0f2027 0%, #1a3a2a 40%, #0d2b20 70%, #0a1628 100%);
                    position: relative;
                    overflow: hidden;
                }

                #ramadan-canvas {
                    position: fixed;
                    inset: 0;
                    pointer-events: none;
                    z-index: 0;
                }

                .login-card {
                    position: relative;
                    z-index: 1;
                    border-top: 3px solid #d97706;
                }

                .ramadan-banner {
                    text-align: center;
                    margin-bottom: 1.25rem;
                    padding: 0.75rem 1rem;
                    background: linear-gradient(135deg, #fffbeb, #fef3c7);
                    border: 1px solid #fcd34d;
                    border-radius: 14px;
                    position: relative;
                    overflow: hidden;
                }

                .ramadan-banner::before {
                    content: '';
                    position: absolute;
                    inset: 0;
                    background: linear-gradient(90deg, transparent 0%, rgba(252, 211, 77, 0.18) 50%, transparent 100%);
                    animation: rimadan-shimmer 2.5s infinite;
                }

                @keyframes rimadan-shimmer {
                    0% {
                        transform: translateX(-100%);
                    }

                    100% {
                        transform: translateX(100%);
                    }
                }

                .ramadan-banner .rbanner-greeting {
                    font-size: 1rem;
                    font-weight: 700;
                    color: #92400e;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    gap: 0.4rem;
                    margin-bottom: 0.2rem;
                }

                .ramadan-banner .rbanner-sub {
                    font-size: 0.775rem;
                    color: #b45309;
                    font-style: italic;
                    line-height: 1.4;
                }

                .ramadan-bg .form-input:focus {
                    border-color: #d97706;
                    box-shadow: 0 0 0 4px rgba(217, 119, 6, 0.15);
                }

                .ramadan-bg .input-wrapper:focus-within .input-icon {
                    fill: #d97706;
                }

                .ramadan-bg .primary-button {
                    background: linear-gradient(135deg, #d97706 0%, #92400e 100%);
                    box-shadow: 0 4px 14px rgba(217, 119, 6, 0.45);
                }

                .ramadan-bg .primary-button:hover {
                    box-shadow: 0 8px 22px rgba(217, 119, 6, 0.55);
                }

                .ramadan-bg .secondary-button {
                    color: #d97706;
                    border-color: #d97706;
                }

                .ramadan-bg .secondary-button:hover {
                    background: #d97706;
                    color: white;
                    box-shadow: 0 4px 14px rgba(217, 119, 6, 0.35);
                }

                .ramadan-bg .custom-checkbox:checked {
                    background: #d97706;
                    border-color: #d97706;
                }

                .ramadan-deco {
                    position: absolute;
                    pointer-events: none;
                    z-index: 0;
                    opacity: 0.055;
                    font-size: 8rem;
                    top: -1.5rem;
                    right: -0.75rem;
                    transform: rotate(20deg);
                    line-height: 1;
                    user-select: none;
                }

                .ramadan-title {
                    font-size: 1.5rem;
                    font-weight: 800;
                    background: linear-gradient(135deg, #d97706, #92400e);
                    -webkit-background-clip: text;
                    -webkit-text-fill-color: transparent;
                    background-clip: text;
                    margin-bottom: 0.2rem;
                }

                .ramadan-subtitle {
                    font-size: 0.875rem;
                    color: #6b7280;
                    font-weight: 400;
                    margin-bottom: 0.2rem;
                }

                .ramadan-joke {
                    font-size: 0.8rem;
                    color: #d97706;
                    font-weight: 500;
                }

                .ramadan-footer {
                    font-size: 0.8rem;
                    color: #9ca3af;
                    text-align: center;
                }

                .ramadan-footer strong {
                    color: #d97706;
                }
            </style>
        @endif
    </x-head>
</head>

<body x-data="attendance">
    @if ($isRamadan)
        <canvas id="ramadan-canvas"></canvas>
    @endif
    <div class="app-wrapper{{ $isRamadan ? ' ramadan-bg' : '' }}">
        <!-- Cheat Alert Modal -->
        @if (session('fraud_alert'))
            <div x-data="{ show: true }" x-show="show" style="display: none;"
                class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
                x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

                <!-- Backdrop -->
                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="show = false"></div>

                <!-- Modal Content -->
                <div class="relative bg-white/80 backdrop-blur-xl rounded-3xl p-8 max-w-md w-full shadow-2xl border border-white/30 transform transition-all scale-100"
                    style="box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);">

                    <!-- Lock Icon -->
                    <div class="flex justify-center mb-6">
                        <div class="rounded-full p-6 inline-flex"
                            style="background: linear-gradient(135deg, rgba(248, 113, 113, 0.2) 0%, rgba(239, 68, 68, 0.3) 100%); border: 2px solid rgba(248, 113, 113, 0.3);">
                            <svg width="64" height="64" viewBox="0 0 64 64" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M32 8C26.4772 8 22 12.4772 22 18V24H18C15.7909 24 14 25.7909 14 28V50C14 52.2091 15.7909 54 18 54H46C48.2091 54 50 52.2091 50 50V28C50 25.7909 48.2091 24 46 24H42V18C42 12.4772 37.5228 8 32 8Z"
                                    fill="#ef4444" fill-opacity="0.2" stroke="#ef4444" stroke-width="2.5"
                                    stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M26 18C26 14.6863 28.6863 12 32 12C35.3137 12 38 14.6863 38 18V24H26V18Z"
                                    fill="#ef4444" fill-opacity="0.3" stroke="#ef4444" stroke-width="2.5"
                                    stroke-linecap="round" stroke-linejoin="round" />
                                <circle cx="32" cy="38" r="4" fill="#ef4444" />
                                <path d="M32 42V46" stroke="#ef4444" stroke-width="2.5" stroke-linecap="round" />
                            </svg>
                        </div>
                    </div>

                    <!-- Title -->
                    <h1 class="text-center mb-4 text-2xl font-bold text-slate-800"
                        style="font-family: 'Inter', sans-serif;">
                        Mau ngapain Hayoo!!
                    </h1>

                    <!-- Warning Text -->
                    <p class="text-center mb-6 text-slate-600 leading-relaxed"
                        style="font-family: 'Inter', sans-serif;">
                        Sistem mendeteksi adanya aktivitas yang tidak sesuai. Mohon melakukan absensi dengan jujur dan
                        sesuai prosedur.
                    </p>

                    <!-- Footer Text -->
                    <p class="text-center mb-6 text-sm text-slate-400" style="font-family: 'Inter', sans-serif;">
                        Ganti HP? Hubungi Admin untuk reset agar bisa absen kembali.
                    </p>

                    <!-- Button -->
                    <button @click="show = false"
                        class="w-full rounded-2xl py-4 text-white font-semibold shadow-lg transform transition-all duration-200 hover:-translate-y-0.5 hover:shadow-xl active:translate-y-0"
                        style="background: linear-gradient(135deg, #f87171 0%, #ef4444 100%); font-family: 'Inter', sans-serif;">
                        Saya Mengerti
                    </button>
                </div>
            </div>
        @endif

        <div class="login-card">
            <!-- Profile Avatar -->
            <div class="profile-avatar">
                <img src="{{ asset('images/Logo_bapeten.png') }}" alt="Logo" class="profile-icon">
            </div>

            <!-- Title Section -->
            @if ($isRamadan)
                <div class="ramadan-deco" aria-hidden="true">🌙</div>
                <div style="text-align:center;margin-bottom:1rem;">
                    <h1 class="ramadan-title">🌙 Ramadan Kareem</h1>
                    <p class="ramadan-subtitle">Sistem Absensi — Selamat menunaikan ibadah puasa!</p>
                    <p class="ramadan-joke">☕ Puasa boleh, absen jangan sampai ikut puasa juga ya!</p>
                </div>
                @php
                    $quotes = [
                        [
                            'text' => 'Semangat kerja di bulan Ramadan = pahala double! 💪',
                            'sub' => 'Sahur tadi semoga cukup energi ya...',
                        ],
                        [
                            'text' => 'Perut kosong, tapi produktivitas penuh! 🚀',
                            'sub' => 'Bismillah, kita pasti bisa!',
                        ],
                        [
                            'text' => 'Ramadan ngajarin sabar — termasuk sabar nunggu buka di kantor 😄',
                            'sub' => 'Keep up the great work!',
                        ],
                        [
                            'text' => 'Ikhlas puasa, ikhlas kerja, insyaAllah berkah! ✨',
                            'sub' => 'Ayo semangat hari ini!',
                        ],
                        [
                            'text' => 'Ngantuk? Itulah ujian sejati pegawai Ramadan 😴',
                            'sub' => 'Tapi absen dulu baru merem!',
                        ],
                        [
                            'text' => 'Lapar itu sunnah, telat absen itu dosa kantor 😂',
                            'sub' => 'Masuk tepat waktu ya!',
                        ],
                        [
                            'text' => 'Puasa bikin fokus naik, gosip di kantor turun 🤫',
                            'sub' => 'Alhamdulillah, Ramadan menjaga lisan kita!',
                        ],
                        [
                            'text' => 'Sahur tadi? Semoga sumbernya kuat sampai sore 🍚',
                            'sub' => 'Nasi + doa = bahan bakar terbaik!',
                        ],
                        [
                            'text' => 'Rapat sambil puasa itu ujian kesabaran level dewa 🙏',
                            'sub' => 'Tapi pasti bisa, bismillah!',
                        ],
                        [
                            'text' => 'Kopi pagi? Hari ini diganti dengan niat yang kuat ☕➡️💡',
                            'sub' => 'Semangat kerja tanpa kafein!',
                        ],
                        [
                            'text' => 'Absen tepat waktu = investasi pahala gratis 📋',
                            'sub' => 'Jangan sampai telat, ya!',
                        ],
                        [
                            'text' => 'Ramadan: bulan di mana meeting pagi terasa lebih panjang 🕐',
                            'sub' => 'Sabar, buka tinggal beberapa jam lagi!',
                        ],
                        [
                            'text' => 'Puasa itu gratis, telat masuk kena teguran — pilih yang mana? 😏',
                            'sub' => 'Datang tepat waktu yuk!',
                        ],
                        [
                            'text' => 'Mood naik turun saat Ramadan itu wajar, asal absennya stabil 📈',
                            'sub' => 'Tetap profesional, tetap semangat!',
                        ],
                        [
                            'text' => 'Jam 11 siang adalah saat paling syarat kesabaran pegawai 😅',
                            'sub' => 'Bentar lagi Dzuhur, kuat ya!',
                        ],
                        [
                            'text' => 'Produktif di Ramadan = sedekah waktu untuk negara 🇮🇩',
                            'sub' => 'Ayo kerja keras hari ini!',
                        ],
                        [
                            'text' => 'Buka puasa bareng rekan kerja itu bonusnya Ramadan 🍜',
                            'sub' => 'Tapi kerjain dulu tugasnya!',
                        ],
                        [
                            'text' => 'Perut keroncongan tapi laporan harus selesai duluan 📄',
                            'sub' => 'Deadline tidak kenal puasa!',
                        ],
                        [
                            'text' => 'Godaan terberat Ramadan di kantor: makan siang rekan sebelah 🙈',
                            'sub' => 'Tutup mata, fokus kerja!',
                        ],
                        [
                            'text' => 'Tarawih semalam bikin ngantuk, tapi niat ke kantor bikin semangat! 🌙',
                            'sub' => 'MasyaAllah, hebat sekali kamu!',
                        ],
                        [
                            'text' => 'Ramadan mengajarkan: prioritas itu penting, bukan sekadar sibuk 🎯',
                            'sub' => 'Kerja smart, bukan cuma kerja keras!',
                        ],
                        [
                            'text' => 'Laper boleh, tapi senyumnya jangan sampai ikut puasa 😊',
                            'sub' => 'Tetap ramah dan baik hati ya!',
                        ],
                        [
                            'text' => 'Setiap klik dan ketikan hari ini bisa jadi ladang pahala lho 💻',
                            'sub' => 'Niatkan kerja sebagai ibadah!',
                        ],
                        [
                            'text' => 'Ngantuk setelah sahur itu manusiawi, absen ontime itu mulia 🏅',
                            'sub' => 'Kamu sudah memilih yang benar!',
                        ],
                        [
                            'text' => 'Ramadan: waktu terbaik untuk reset kebiasaan kerja yang kurang baik 🔄',
                            'sub' => 'Jadikan bulan ini titik balik!',
                        ],
                        [
                            'text' => 'Puasa bukan alasan malas, justru bukti kita bisa lebih disiplin! 🔥',
                            'sub' => 'Tunjukkan terbaik kamu hari ini!',
                        ],
                        [
                            'text' => 'Cek email sambil puasa terasa lebih khusyuk entah kenapa 📧',
                            'sub' => 'Alhamdulillah, tetap produktif!',
                        ],
                        [
                            'text' => 'Setiap tetes keringat kerja di Ramadan = pahala yang mengalir 💦',
                            'sub' => 'Kerja keras hari ini, berkah forever!',
                        ],
                        [
                            'text' => 'Meja kerja bersih = pikiran jernih = puasa berkualitas ✨',
                            'sub' => 'Rapikan meja sebelum mulai kerja!',
                        ],
                        [
                            'text' => 'Ramadan bukan bulan slow down, tapi bulan level up! ⬆️',
                            'sub' => 'Ayo buktikan produktivitasmu!',
                        ],
                        [
                            'text' => 'Buka nanti sama teman kantor itu salah satu nikmat Ramadan 🌅',
                            'sub' => 'Sebentar lagi, tetap semangat!',
                        ],
                    ];
                    // Random per hari: konsisten dalam satu hari, berbeda tiap tanggal
                    $seed = abs(crc32(date('Y-m-d')));
                    $q = $quotes[$seed % count($quotes)];
                @endphp
                <div class="ramadan-banner">
                    <div class="rbanner-greeting">{{ $q['text'] }}</div>
                    <div class="rbanner-sub">{{ $q['sub'] }}</div>
                </div>
            @else
                <div style="text-align: center; margin-bottom: 2rem;">
                    <h1 style="font-size: 1.875rem; font-weight: 700; color: #1f2937; margin-bottom: 0.5rem;">Sistem
                        Absensi</h1>
                    <p style="font-size: 0.9375rem; color: #6b7280; font-weight: 400;">Selamat datang! Silakan login
                        untuk melanjutkan</p>
                </div>
            @endif

            <!-- Session Messages -->
            @if (session('success'))
                <div class="message-area success" style="display: block;">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="message-area error" style="display: block;">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Login Form -->
            <div x-data="loginForm()">
                <!-- Error Message -->
                <div x-show="errorMessage" x-transition class="message-area error" style="display: none;">
                    <p x-text="errorMessage"></p>
                </div>
                <form @submit.prevent="submitLogin">
                    <!-- Email Input -->
                    <div style="margin-bottom: 0.5rem;">
                        <label for="email"
                            style="display: block; font-size: 0.875rem; font-weight: 500; color: #374151; margin-bottom: 0.5rem;">Email</label>
                        <div class="input-wrapper">
                            <svg class="input-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z" />
                            </svg>
                            <input type="email" id="email" x-model="email" class="form-input"
                                placeholder="nama@gmail.com" autofocus :class="{ 'border-red-500': errorMessage }">
                        </div>
                    </div>

                    <!-- Password Input -->
                    <div style="margin-bottom: 0.5rem;">
                        <label for="password"
                            style="display: block; font-size: 0.875rem; font-weight: 500; color: #374151; margin-bottom: 0.5rem;">Password</label>
                        <div class="input-wrapper">
                            <svg class="input-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z" />
                            </svg>
                            <input :type="showPassword ? 'text' : 'password'" id="password" x-model="password"
                                class="form-input" style="padding-right: 3rem;" placeholder="••••••••"
                                :class="{ 'border-red-500': errorMessage }">
                            <button type="button" class="password-toggle" @click="showPassword = !showPassword"
                                aria-label="Toggle password visibility">
                                <svg x-show="!showPassword" class="eye-icon" viewBox="0 0 24 24"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z" />
                                </svg>
                                <svg x-show="showPassword" class="eye-icon" viewBox="0 0 24 24"
                                    xmlns="http://www.w3.org/2000/svg" style="display: none;">
                                    <path
                                        d="M12 7c2.76 0 5 2.24 5 5 0 .65-.13 1.26-.36 1.83l2.92 2.92c1.51-1.26 2.7-2.89 3.43-4.75-1.73-4.39-6-7.5-11-7.5-1.4 0-2.74.25-3.98.7l2.16 2.16C10.74 7.13 11.35 7 12 7zM2 4.27l2.28 2.28.46.46C3.08 8.3 1.78 10.02 1 12c1.73 4.39 6 7.5 11 7.5 1.55 0 3.03-.3 4.38-.84l.42.42L19.73 22 21 20.73 3.27 3 2 4.27zM7.53 9.8l1.55 1.55c-.05.21-.08.43-.08.65 0 1.66 1.34 3 3 3 .22 0 .44-.03.65-.08l1.55 1.55c-.67.33-1.41.53-2.2.53-2.76 0-5-2.24-5-5 0-.79.2-1.53.53-2.2zm4.31-.78l3.15 3.15.02-.16c0-1.66-1.34-3-3-3l-.17.01z" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Remember Me Checkbox -->
                    <div class="checkbox-wrapper">
                        <input type="checkbox" id="remember" x-model="remember" class="custom-checkbox">
                        <label for="remember"
                            style="margin-left: 0.625rem; font-size: 0.875rem; color: #4b5563; cursor: pointer;">Ingat
                            saya</label>
                    </div>

                    <!-- Login Button -->
                    <button type="submit" class="primary-button" :disabled="isLoading">
                        <span x-show="!isLoading">Masuk</span>
                        <span x-show="isLoading" class="flex items-center" style="display: none;">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            Memproses...
                        </span>
                    </button>
                </form>
            </div>

            <!-- Direct Attendance Button -->
            <button @click="openDirect=true; getLocation()" class="secondary-button">Absen Langsung</button>

            <!-- Footer -->
            @if ($isRamadan)
                <div style="text-align:center;margin-top:1.5rem;">
                    <p class="ramadan-footer">🌙 <strong>Ramadan {{ date('Y') }}</strong> — Semoga amal ibadah kita
                        diterima</p>
                    <p style="font-size:0.75rem;color:#d1d5db;margin-top:0.2rem;">Butuh bantuan? Hubungi admin</p>
                </div>
            @else
                <div style="text-align: center; margin-top: 2rem;">
                    <p style="font-size: 0.8125rem; color: #9ca3af;">Butuh bantuan? Hubungi admin@contoh.local</p>
                </div>
            @endif
        </div>
    </div>

    @include('auth.partials.modal')
    @if ($isRamadan)
        <script>
            (function() {
                const canvas = document.getElementById('ramadan-canvas');
                if (!canvas) return;
                const ctx = canvas.getContext('2d');

                const STARS = [];
                const COUNT = 120;

                function resize() {
                    canvas.width = window.innerWidth;
                    canvas.height = window.innerHeight;
                }
                resize();
                window.addEventListener('resize', resize);

                // Seed stars
                for (let i = 0; i < COUNT; i++) {
                    STARS.push({
                        x: Math.random(),
                        y: Math.random(),
                        r: Math.random() * 1.4 + 0.4,
                        a: Math.random(), // current alpha
                        da: (Math.random() * 0.006 + 0.002) * (Math.random() < 0.5 ? 1 : -1),
                        speed: Math.random() * 0.00008 + 0.00002,
                    });
                }

                // A few floating crescents
                const MOONS = Array.from({
                    length: 5
                }, () => ({
                    x: Math.random(),
                    y: Math.random(),
                    size: Math.random() * 18 + 12,
                    alpha: Math.random() * 0.12 + 0.04,
                    speed: (Math.random() * 0.00006 + 0.00002) * (Math.random() < 0.5 ? 1 : -1),
                }));

                function drawCrescent(cx, cy, size, alpha) {
                    ctx.save();
                    ctx.globalAlpha = alpha;
                    ctx.fillStyle = '#fcd34d';
                    ctx.beginPath();
                    ctx.arc(cx, cy, size, 0, Math.PI * 2);
                    ctx.fill();
                    ctx.fillStyle = '#1a3a2a'; // match bg
                    ctx.beginPath();
                    ctx.arc(cx + size * 0.35, cy - size * 0.1, size * 0.82, 0, Math.PI * 2);
                    ctx.fill();
                    ctx.restore();
                }

                let last = 0;

                function draw(ts) {
                    const dt = Math.min(ts - last, 50);
                    last = ts;
                    ctx.clearRect(0, 0, canvas.width, canvas.height);

                    // Stars
                    STARS.forEach(s => {
                        s.y -= s.speed * dt;
                        if (s.y < 0) {
                            s.y = 1;
                            s.x = Math.random();
                        }
                        s.a += s.da;
                        if (s.a <= 0 || s.a >= 1) s.da *= -1;
                        ctx.globalAlpha = Math.abs(s.a) * 0.8;
                        ctx.fillStyle = '#fff';
                        ctx.beginPath();
                        ctx.arc(s.x * canvas.width, s.y * canvas.height, s.r, 0, Math.PI * 2);
                        ctx.fill();
                    });

                    // Crescents
                    MOONS.forEach(m => {
                        m.y -= m.speed * dt;
                        if (m.y < -0.05) {
                            m.y = 1.05;
                            m.x = Math.random();
                        }
                        drawCrescent(m.x * canvas.width, m.y * canvas.height, m.size, m.alpha);
                    });

                    requestAnimationFrame(draw);
                }
                requestAnimationFrame(t => {
                    last = t;
                    draw(t);
                });
            })();
        </script>
    @endif

    @include('auth.partials.scripts')
</body>

</html>
