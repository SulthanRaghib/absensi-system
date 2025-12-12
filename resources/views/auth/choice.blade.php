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
    </x-head>
</head>

<body x-data="attendance">
    <div class="app-wrapper">
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
            <div style="text-align: center; margin-bottom: 2rem;">
                <h1 style="font-size: 1.875rem; font-weight: 700; color: #1f2937; margin-bottom: 0.5rem;">Sistem Absensi
                </h1>
                <p style="font-size: 0.9375rem; color: #6b7280; font-weight: 400;">Selamat datang! Silakan login untuk
                    melanjutkan</p>
            </div>

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
            <div style="text-align: center; margin-top: 2rem;">
                <p style="font-size: 0.8125rem; color: #9ca3af;">Butuh bantuan? Hubungi admin@contoh.local</p>
            </div>
        </div>
    </div>

    @include('auth.partials.modal')
    @include('auth.partials.scripts')
</body>

</html>
