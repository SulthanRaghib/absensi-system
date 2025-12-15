<div>
    <x-filament-panels::page>
        <!-- Leaflet CSS & JS -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>

        <!-- Face API -->
        <script src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api/dist/face-api.min.js"></script>

        <style>
            .glass-card {
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255, 255, 255, 0.2);
            }

            .btn-action {
                transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            }

            .btn-action:active {
                transform: scale(0.98);
            }

            .map-container {
                box-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.06);
            }

            video {
                transform: scaleX(-1);
                /* Mirror the video */
            }
        </style>

        <div x-data="absensiMapData({
            officeLat: {{ $officeLocation['latitude'] }},
            officeLng: {{ $officeLocation['longitude'] }},
            officeRadius: {{ $officeLocation['radius'] }},
            checkInRoute: '{{ route('absensi.check-in') }}',
            checkOutRoute: '{{ route('absensi.check-out') }}',
            csrfToken: '{{ csrf_token() }}',
            faceRecognitionEnabled: {{ $faceRecognitionEnabled ? 'true' : 'false' }},
            userAvatar: '{{ Auth::user()->avatar_url ? asset('storage/' . Auth::user()->avatar_url) : null }}'
        })" class="grid grid-cols-1 md:grid-cols-12 gap-6">

            <!-- Face Verification Modal -->
            <div x-show="showFaceModal" style="display: none;"
                class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
                x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

                <!-- Backdrop -->
                <div class="absolute inset-0 bg-black/75 backdrop-blur-sm" @click="closeFaceModal()"></div>

                <!-- Modal Content -->
                <div class="relative bg-white rounded-3xl p-6 max-w-lg w-full shadow-2xl flex flex-col items-center">
                    <h2 class="text-xl font-bold mb-4">Verifikasi Wajah</h2>

                    <div class="relative w-full aspect-[4/3] bg-black rounded-xl overflow-hidden mb-4">
                        <video x-ref="video" autoplay muted playsinline class="w-full h-full object-cover"></video>
                        <canvas x-ref="canvas" class="absolute inset-0 w-full h-full pointer-events-none"></canvas>

                        <!-- Loading Overlay -->
                        <div x-show="isFaceLoading"
                            class="absolute inset-0 flex items-center justify-center bg-black/50 text-white">
                            <div class="flex flex-col items-center">
                                <svg class="animate-spin h-8 w-8 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                <span x-text="faceStatus">Memuat Model...</span>
                            </div>
                        </div>
                    </div>

                    <p class="text-sm text-gray-500 mb-4 text-center" x-text="faceMessage"></p>

                    <div class="flex gap-3 w-full">
                        <button @click="closeFaceModal()"
                            class="flex-1 py-3 bg-gray-100 text-gray-700 rounded-xl font-semibold hover:bg-gray-200">
                            Batal
                        </button>
                        <button @click="captureAndVerify()" :disabled="isFaceLoading || !isModelLoaded"
                            class="flex-1 py-3 bg-blue-600 text-white rounded-xl font-semibold hover:bg-blue-700 disabled:opacity-50">
                            Verifikasi
                        </button>
                    </div>
                </div>
            </div>

            <!-- Cheat Alert Modal -->
            <div x-show="showCheatModal" style="display: none;"
                class="fixed inset-0 z-[9999] flex items-center justify-center p-6"
                x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

                <!-- Backdrop -->
                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showCheatModal = false"></div>

                <!-- Modal Content -->
                <div class="relative bg-white/90 backdrop-blur-xl rounded-3xl p-10 max-w-md w-full shadow-2xl border border-white/40 transform transition-all scale-100"
                    style="box-shadow: 0 10px 40px rgba(31, 38, 135, 0.18);">

                    <!-- Lock Icon -->
                    <div class="flex justify-center mb-8">
                        <div class="rounded-full p-7 inline-flex"
                            style="background: linear-gradient(135deg, rgba(248, 113, 113, 0.2) 0%, rgba(239, 68, 68, 0.35) 100%); border: 2px solid rgba(248, 113, 113, 0.35);">
                            <svg width="70" height="70" viewBox="0 0 64 64" fill="none"
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
                    <h1 class="text-center mb-6 text-3xl font-bold text-slate-800"
                        style="font-family: 'Inter', sans-serif;">
                        Mau ngapain Hayoo!!
                    </h1>

                    <!-- Warning Text -->
                    <p class="text-center mb-8 text-slate-600 leading-relaxed text-lg"
                        style="font-family: 'Inter', sans-serif;">
                        Sistem mendeteksi adanya aktivitas yang tidak sesuai. Mohon melakukan absensi dengan jujur dan
                        sesuai prosedur.
                    </p>

                    <!-- Footer Text -->
                    <p class="text-center mb-8 text-sm text-slate-500" style="font-family: 'Inter', sans-serif;">
                        Ganti HP? Hubungi Admin untuk reset agar bisa absen kembali.
                    </p>

                    <!-- Button -->
                    <button @click="showCheatModal = false"
                        class="w-full rounded-2xl py-4 text-lg text-white font-semibold shadow-lg transition-all duration-200 hover:-translate-y-0.5 hover:shadow-xl active:translate-y-0"
                        style="background: linear-gradient(135deg, #f87171 0%, #ef4444 100%); font-family: 'Inter', sans-serif;">
                        Saya Mengerti
                    </button>
                </div>
            </div>

            <!-- No Avatar Modal -->
            <div x-show="showNoAvatarModal" style="display: none;"
                class="fixed inset-0 z-[9999] flex items-center justify-center p-6"
                x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

                <!-- Backdrop -->
                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showNoAvatarModal = false"></div>

                <!-- Modal Content -->
                <div
                    class="relative bg-white rounded-3xl p-8 max-w-md w-full shadow-2xl flex flex-col items-center text-center">
                    <div class="w-20 h-20 bg-yellow-100 rounded-full flex items-center justify-center mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-yellow-600" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>

                    <h2 class="text-2xl font-bold text-gray-900 mb-3">Foto Profil Belum Diatur</h2>

                    <p class="text-gray-600 mb-8">
                        Anda belum mengatur foto profil! Harap upload foto wajah yang jelas di menu Profil sebelum
                        melakukan absen.
                    </p>

                    <div class="flex flex-col gap-3 w-full">
                        <a href="{{ filament()->getProfileUrl() }}"
                            class="w-full py-3.5 bg-blue-600 text-white rounded-xl font-semibold hover:bg-blue-700 transition-colors shadow-lg shadow-blue-600/20">
                            Atur Foto Profil
                        </a>
                        <button @click="showNoAvatarModal = false"
                            class="w-full py-3.5 bg-gray-100 text-gray-700 rounded-xl font-semibold hover:bg-gray-200 transition-colors">
                            Nanti Saja
                        </button>
                    </div>
                </div>
            </div>

            <!-- Left Column: Profile, Status, Actions -->
            <div class="md:col-span-5 lg:col-span-4 space-y-6">

                <!-- Profile Section -->
                <div
                    class="flex items-center space-x-4 bg-white p-4 rounded-3xl shadow-sm border border-gray-100 md:bg-transparent md:shadow-none md:border-0 md:p-0">
                    <div class="relative">
                        @if ($user->avatar_url)
                            <img src="{{ asset('storage/' . $user->avatar_url) }}" alt="{{ $user->name }}"
                                class="w-16 h-16 rounded-2xl object-cover shadow-lg shadow-blue-500/30">
                        @else
                            <div
                                class="w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white text-2xl font-bold shadow-lg shadow-blue-500/30">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        @endif
                        <div
                            class="absolute -bottom-1 -right-1 w-5 h-5 bg-green-500 border-2 border-white rounded-full">
                        </div>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900 leading-tight">{{ $user->name }}</h1>
                        <p class="text-sm text-gray-500 font-medium">{{ $user->email }}</p>
                        <p
                            class="text-xs text-blue-600 font-semibold mt-1 bg-blue-50 inline-block px-2 py-0.5 rounded-md">
                            {{ now()->isoFormat('dddd, D MMMM Y') }}
                        </p>
                    </div>
                </div>

                <!-- Attendance Status Card -->
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Status Hari Ini</h2>
                        @if ($todayAbsence?->jam_masuk && $todayAbsence?->jam_pulang)
                            <span
                                class="px-2 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full">Selesai</span>
                        @elseif($todayAbsence?->jam_masuk)
                            <span
                                class="px-2 py-1 bg-blue-100 text-blue-700 text-xs font-bold rounded-full">Bekerja</span>
                        @else
                            <span class="px-2 py-1 bg-gray-100 text-gray-600 text-xs font-bold rounded-full">Belum
                                Absen</span>
                        @endif
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <!-- Check In Time -->
                        <div
                            class="bg-green-50/50 rounded-2xl p-4 border border-green-100 flex flex-col items-center justify-center text-center">
                            <div
                                class="w-8 h-8 rounded-full bg-green-100 text-green-600 flex items-center justify-center mb-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                </svg>
                            </div>
                            <span class="text-xs text-gray-500 font-medium mb-0.5">Jam Masuk</span>
                            <span x-ref="jamMasukDisplay" class="text-xl font-bold text-gray-900 tracking-tight">
                                {{ $todayAbsence?->jam_masuk ? $todayAbsence->jam_masuk->format('H:i') : '--:--' }}
                            </span>
                        </div>

                        <!-- Check Out Time -->
                        <div
                            class="bg-orange-50/50 rounded-2xl p-4 border border-orange-100 flex flex-col items-center justify-center text-center">
                            <div
                                class="w-8 h-8 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center mb-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                            </div>
                            <span class="text-xs text-gray-500 font-medium mb-0.5">Jam Pulang</span>
                            <span x-ref="jamPulangDisplay" class="text-xl font-bold text-gray-900 tracking-tight">
                                {{ $todayAbsence?->jam_pulang ? $todayAbsence->jam_pulang->format('H:i') : '--:--' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="grid grid-cols-2 gap-4">
                    <button @click="initiateCheckIn()"
                        class="btn-action group relative w-full flex flex-col items-center justify-center p-4 rounded-2xl bg-gray-900 text-white shadow-lg shadow-gray-900/20 disabled:opacity-50 disabled:cursor-not-allowed disabled:shadow-none overflow-hidden"
                        :disabled="!userLocation || isLoading || {{ $todayAbsence?->jam_masuk ? 'true' : 'false' }}">
                        <div class="absolute inset-0 bg-white/10 opacity-0 group-hover:opacity-100 transition-opacity">
                        </div>
                        <div
                            class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center mb-2 group-disabled:bg-white/10">
                            <template x-if="isLoading && actionType === 'in'">
                                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                            </template>
                            <template x-if="!isLoading || actionType !== 'in'">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                </svg>
                            </template>
                        </div>
                        <span class="font-semibold">Absen Masuk</span>
                    </button>

                    <button @click="performAttendance('out')"
                        class="btn-action group relative w-full flex flex-col items-center justify-center p-4 rounded-2xl bg-white text-gray-900 border border-gray-200 shadow-sm hover:border-gray-300 disabled:opacity-50 disabled:cursor-not-allowed disabled:bg-gray-50"
                        :disabled="!userLocation || isLoading ||
                            {{ !$todayAbsence?->jam_masuk || $todayAbsence?->jam_pulang ? 'true' : 'false' }}">
                        <div
                            class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center mb-2 group-disabled:bg-gray-200">
                            <template x-if="isLoading && actionType === 'out'">
                                <svg class="animate-spin h-5 w-5 text-gray-600" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                            </template>
                            <template x-if="!isLoading || actionType !== 'out'">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                            </template>
                        </div>
                        <span class="font-semibold">Absen Pulang</span>
                    </button>
                </div>
            </div>

            <!-- Right Column: Map -->
            <div class="md:col-span-7 lg:col-span-8 h-full">
                <div
                    class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden h-full flex flex-col">
                    <div class="p-5 pb-0 flex justify-between items-center shrink-0">
                        <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Lokasi Anda</h2>
                        <button @click="manualUpdateLocation()"
                            class="text-xs font-semibold text-blue-600 hover:text-blue-700 flex items-center space-x-1 bg-blue-50 px-3 py-1.5 rounded-full transition-colors cursor-pointer active:bg-blue-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            <span>Update Lokasi</span>
                        </button>
                    </div>

                    <div class="p-5 flex-grow flex flex-col">
                        <div id="map"
                            class="w-full rounded-2xl map-container bg-gray-100 mb-4 z-0 flex-grow min-h-[300px] md:min-h-[400px]">
                        </div>

                        <div class="grid grid-cols-2 md:grid-cols-4 gap-y-3 gap-x-4 text-sm shrink-0">
                            <div class="flex flex-col">
                                <span class="text-xs text-gray-400">Latitude</span>
                                <span x-text="userLocation ? userLocation.lat.toFixed(5) : '-'"
                                    class="font-mono font-medium text-gray-700"></span>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-xs text-gray-400">Longitude</span>
                                <span x-text="userLocation ? userLocation.lng.toFixed(5) : '-'"
                                    class="font-mono font-medium text-gray-700"></span>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-xs text-gray-400">Akurasi</span>
                                <span x-text="userLocation ? '±' + userLocation.accuracy.toFixed(0) + 'm' : '-'"
                                    class="font-mono font-medium text-gray-700"></span>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-xs text-gray-400">Jarak Kantor</span>
                                <span x-text="userLocation ? userLocation.distance.toFixed(0) + 'm' : '-'"
                                    class="font-mono font-medium text-gray-700"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Early Checkout Modal -->
            <div x-show="showEarlyCheckoutModal" x-cloak
                class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
                x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

                <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 transform transition-all relative"
                    style="max-width: 28rem;" @click.away="showEarlyCheckoutModal = false"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">

                    <div class="text-center">
                        <div
                            class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 mb-4">
                            <svg class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Konfirmasi Pulang Awal</h3>
                        <p class="text-sm text-gray-500 mb-6">
                            Anda melakukan absen pulang sebelum jam kerja berakhir. Apakah Anda yakin ingin melanjutkan?
                        </p>

                        <div class="flex gap-3 justify-center">
                            <button @click="showEarlyCheckoutModal = false" type="button"
                                class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-medium transition-colors">
                                Batal
                            </button>
                            <button @click="confirmEarlyCheckout()" type="button"
                                style="background-color: #ca8a04; color: white;"
                                class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 font-medium transition-colors shadow-lg shadow-yellow-600/20">
                                Ya, Lanjutkan
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alert Container -->
            <div id="alert-container"
                class="fixed bottom-6 left-4 right-4 z-50 flex flex-col gap-2 pointer-events-none">
            </div>
        </div>

        <script>
            function absensiMapData(config) {
                return {
                    map: null,
                    userMarker: null,
                    officeCircle: null,
                    userLocation: null,
                    watchId: null,
                    isLoading: false,
                    actionType: null,
                    showEarlyCheckoutModal: false,
                    showCheatModal: false,
                    deviceToken: null,

                    // Face Recognition State
                    showFaceModal: false,
                    showNoAvatarModal: false,
                    isFaceLoading: false,
                    isModelLoaded: false,
                    faceStatus: 'Memuat Model...',
                    faceMessage: 'Posisikan wajah Anda di tengah kamera',
                    capturedImage: null,
                    videoStream: null,

                    init() {
                        // Device Binding Logic
                        let token = localStorage.getItem('device_token');
                        if (!token) {
                            token = crypto.randomUUID();
                            localStorage.setItem('device_token', token);
                        }
                        this.deviceToken = token;

                        this.$nextTick(() => {
                            this.initMap();
                            this.startTracking(true); // Center map on init

                            if (config.faceRecognitionEnabled) {
                                this.loadFaceModels();
                            }
                        });
                    },

                    async loadFaceModels() {
                        try {
                            this.faceStatus = 'Memuat Model Wajah...';
                            // Load models from CDN or local
                            const MODEL_URL = 'https://justadudewhohacks.github.io/face-api.js/models';

                            await Promise.all([
                                faceapi.nets.ssdMobilenetv1.loadFromUri(MODEL_URL),
                                faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
                                faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL)
                            ]);

                            this.isModelLoaded = true;
                            console.log('Face API Models Loaded');
                        } catch (error) {
                            console.error('Error loading face models:', error);
                            this.showAlert('Gagal memuat sistem pengenalan wajah. Cek koneksi internet.', 'error');
                        }
                    },

                    initiateCheckIn() {
                        if (config.faceRecognitionEnabled) {
                            if (!config.userAvatar) {
                                this.showNoAvatarModal = true;
                                return;
                            }
                            this.openFaceModal();
                        } else {
                            this.performAttendance('in');
                        }
                    },

                    async openFaceModal() {
                        this.showFaceModal = true;
                        this.isFaceLoading = true;
                        this.faceStatus = 'Menyiapkan Kamera...';
                        this.faceMessage = 'Posisikan wajah Anda di tengah kamera';

                        try {
                            const stream = await navigator.mediaDevices.getUserMedia({
                                video: {}
                            });
                            this.videoStream = stream;
                            this.$refs.video.srcObject = stream;
                            this.isFaceLoading = false;
                        } catch (err) {
                            console.error(err);
                            this.showAlert('Gagal mengakses kamera. Pastikan izin kamera diberikan.', 'error');
                            this.closeFaceModal();
                        }
                    },

                    closeFaceModal() {
                        this.showFaceModal = false;
                        if (this.videoStream) {
                            this.videoStream.getTracks().forEach(track => track.stop());
                            this.videoStream = null;
                        }
                    },

                    async captureAndVerify() {
                        if (!this.isModelLoaded) return;

                        this.isFaceLoading = true;
                        this.faceStatus = 'Memverifikasi Wajah...';

                        const video = this.$refs.video;

                        // 1. Detect Face from Camera
                        const detection = await faceapi.detectSingleFace(video).withFaceLandmarks().withFaceDescriptor();

                        if (!detection) {
                            this.isFaceLoading = false;
                            this.faceMessage = 'Wajah tidak terdeteksi! Pastikan pencahayaan cukup.';
                            return;
                        }

                        // 2. Load User Avatar and Compute Descriptor
                        try {
                            // We need to load the avatar image as an HTMLImageElement
                            const img = await faceapi.fetchImage(config.userAvatar);
                            const avatarDetection = await faceapi.detectSingleFace(img).withFaceLandmarks()
                                .withFaceDescriptor();

                            if (!avatarDetection) {
                                this.isFaceLoading = false;
                                this.showAlert(
                                    'Foto profil Anda tidak valid (wajah tidak terdeteksi). Harap ganti foto profil.',
                                    'error');
                                this.closeFaceModal();
                                return;
                            }

                            // 3. Compare Descriptors
                            const distance = faceapi.euclideanDistance(detection.descriptor, avatarDetection.descriptor);
                            const threshold = 0.6;

                            console.log('Face Distance:', distance);

                            if (distance < threshold) {
                                // Match!
                                // Capture Image
                                const canvas = this.$refs.canvas;
                                canvas.width = video.videoWidth;
                                canvas.height = video.videoHeight;
                                const ctx = canvas.getContext('2d');
                                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                                this.capturedImage = canvas.toDataURL('image/png');

                                this.closeFaceModal();
                                this.performAttendance('in', false, this.capturedImage);
                            } else {
                                this.isFaceLoading = false;
                                this.faceMessage = 'Wajah tidak cocok dengan foto profil!';
                            }

                        } catch (error) {
                            console.error(error);
                            this.isFaceLoading = false;
                            this.showAlert('Terjadi kesalahan saat verifikasi wajah.', 'error');
                        }
                    },

                    initMap() {
                        if (this.map) return;

                        this.map = L.map('map', {
                            zoomControl: false,
                            attributionControl: false
                        }).setView([config.officeLat, config.officeLng], 15);

                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(this.map);

                        // Office marker
                        const officeIcon = L.divIcon({
                            className: 'custom-div-icon',
                            html: `<div style="background-color: #EF4444; width: 12px; height: 12px; border-radius: 50%; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3);"></div>`,
                            iconSize: [12, 12],
                            iconAnchor: [6, 6]
                        });

                        L.marker([config.officeLat, config.officeLng], {
                            icon: officeIcon
                        }).addTo(this.map);

                        // Office radius circle
                        this.officeCircle = L.circle([config.officeLat, config.officeLng], {
                            color: '#3B82F6',
                            fillColor: '#3B82F6',
                            fillOpacity: 0.1,
                            weight: 1,
                            radius: config.officeRadius
                        }).addTo(this.map);
                    },

                    manualUpdateLocation() {
                        this.showAlert('Mencari lokasi terkini...', 'info');
                        this.startTracking(true);
                    },

                    startTracking(centerOnUpdate = false) {
                        if (!navigator.geolocation) {
                            this.showAlert('Browser tidak mendukung GPS!', 'error');
                            return;
                        }

                        const updateFromPosition = (position) => {
                            const lat = position.coords.latitude;
                            const lng = position.coords.longitude;
                            const accuracy = position.coords.accuracy;
                            const distance = this.calculateDistance(lat, lng, config.officeLat, config.officeLng);

                            this.userLocation = {
                                lat,
                                lng,
                                accuracy,
                                distance
                            };
                            this.updateMapMarker(lat, lng);

                            if (centerOnUpdate) {
                                this.map.setView([lat, lng], 18);
                                centerOnUpdate = false;
                            }
                        };

                        if (this.watchId) {
                            navigator.geolocation.clearWatch(this.watchId);
                        }

                        // Fast path: cached / low-power fix first to avoid initial TIMEOUT on some devices
                        navigator.geolocation.getCurrentPosition(
                            (position) => updateFromPosition(position),
                            () => {}, {
                                enableHighAccuracy: false,
                                timeout: 8000,
                                maximumAge: 60000
                            }
                        );

                        this.watchId = navigator.geolocation.watchPosition(
                            (position) => {
                                updateFromPosition(position);
                            },
                            (error) => {
                                console.error('Geolocation error:', error);
                                let message = 'Gagal mengambil lokasi.';
                                if (error.code === 1) message = 'Izin lokasi ditolak.';
                                if (error.code === 2) message = 'Sinyal GPS tidak ditemukan.';
                                if (error.code === 3) message = 'Waktu permintaan habis.';
                                this.showAlert(message, 'error');
                            }, {
                                enableHighAccuracy: true,
                                timeout: 60000,
                                maximumAge: 10000
                            }
                        );
                    },

                    updateMapMarker(lat, lng) {
                        if (this.userMarker) {
                            this.userMarker.setLatLng([lat, lng]);
                        } else {
                            const userIcon = L.divIcon({
                                className: 'custom-div-icon',
                                html: `<div class="relative">
                                        <div class="w-4 h-4 bg-blue-500 rounded-full border-2 border-white shadow-md"></div>
                                        <div class="absolute -inset-2 bg-blue-500/20 rounded-full animate-ping"></div>
                                       </div>`,
                                iconSize: [16, 16],
                                iconAnchor: [8, 8]
                            });

                            this.userMarker = L.marker([lat, lng], {
                                icon: userIcon
                            }).addTo(this.map);
                        }

                        // Only fit bounds if it's the first update or user is far away
                        // this.map.fitBounds(L.latLngBounds([[config.officeLat, config.officeLng], [lat, lng]]), { padding: [50, 50] });
                    },

                    calculateDistance(lat1, lon1, lat2, lon2) {
                        const R = 6371000;
                        const φ1 = lat1 * Math.PI / 180;
                        const φ2 = lat2 * Math.PI / 180;
                        const Δφ = (lat2 - lat1) * Math.PI / 180;
                        const Δλ = (lon2 - lon1) * Math.PI / 180;

                        const a = Math.sin(Δφ / 2) * Math.sin(Δφ / 2) +
                            Math.cos(φ1) * Math.cos(φ2) *
                            Math.sin(Δλ / 2) * Math.sin(Δλ / 2);
                        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

                        return R * c;
                    },

                    async performAttendance(action, force = false, image = null) {
                        if (!this.userLocation) {
                            this.showAlert('Lokasi belum ditemukan!', 'error');
                            return;
                        }

                        // Check Avatar for Check Out
                        if (action === 'out') {
                            if (!config.userAvatar) {
                                this.showNoAvatarModal = true;
                                return;
                            }
                        }

                        // Early Checkout Validation
                        if (action === 'out' && !force) {
                            const now = new Date();
                            const day = now.getDay();
                            const hour = now.getHours();
                            const minute = now.getMinutes();

                            let isEarly = false;

                            console.log('Debug Time:', {
                                day,
                                hour,
                                minute
                            });

                            // Friday (5): 16:30
                            if (day === 5) {
                                if (hour < 16 || (hour === 16 && minute < 30)) isEarly = true;
                            }
                            // Other days (Mon-Thu, Sat, Sun): 16:00
                            else {
                                if (hour < 16) isEarly = true;
                            }

                            if (isEarly) {
                                console.log('Showing early checkout modal');
                                this.showEarlyCheckoutModal = true;
                                return;
                            }
                        }
                        this.isLoading = true;
                        this.actionType = action;
                        const url = action === 'in' ? config.checkInRoute : config.checkOutRoute;

                        try {
                            const response = await fetch(url, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': config.csrfToken
                                },
                                body: JSON.stringify({
                                    latitude: this.userLocation.lat,
                                    longitude: this.userLocation.lng,
                                    accuracy: this.userLocation.accuracy,
                                    device_token: this.deviceToken,
                                    image: image // Send captured image if available
                                })
                            });

                            const data = await response.json();

                            // Debugging response
                            console.log('Attendance Response:', {
                                status: response.status,
                                data
                            });

                            if (data.cheat_alert) {
                                this.showCheatModal = true;
                                return;
                            }

                            if (data.success) {
                                // Update Device Token if rotated
                                if (data.data.new_device_id) {
                                    console.log('Rotating Device ID:', data.data.new_device_id);
                                    localStorage.setItem('device_token', data.data.new_device_id);
                                    this.deviceToken = data.data.new_device_id;
                                }

                                this.showAlert(action === 'in' ? 'Berhasil Check-in!' : 'Berhasil Check-out!', 'success');
                                if (action === 'in') {
                                    this.$refs.jamMasukDisplay.textContent = data.data.jam_masuk;
                                } else {
                                    this.$refs.jamPulangDisplay.textContent = data.data.jam_pulang;
                                }
                                setTimeout(() => location.reload(), 1500);
                            } else {
                                this.showAlert(data.message, 'error');
                            }
                        } catch (error) {
                            this.showAlert('Terjadi kesalahan koneksi.', 'error');
                        } finally {
                            this.isLoading = false;
                            this.actionType = null;
                        }
                    },

                    confirmEarlyCheckout() {
                        this.showEarlyCheckoutModal = false;
                        this.performAttendance('out', true);
                    },

                    showAlert(message, type = 'info') {
                        const colors = {
                            success: 'bg-green-500 text-white',
                            error: 'bg-red-500 text-white',
                            warning: 'bg-yellow-500 text-white',
                            info: 'bg-gray-800 text-white'
                        };

                        const alert = document.createElement('div');
                        alert.className =
                            `${colors[type]} px-4 py-3 rounded-xl shadow-lg text-sm font-medium transform transition-all duration-300 translate-y-10 opacity-0 pointer-events-auto flex items-center justify-between`;
                        alert.innerHTML = `<span>${message}</span>`;

                        const container = document.getElementById('alert-container');
                        container.appendChild(alert);

                        requestAnimationFrame(() => {
                            alert.classList.remove('translate-y-10', 'opacity-0');
                        });

                        setTimeout(() => {
                            alert.classList.add('translate-y-10', 'opacity-0');
                            setTimeout(() => alert.remove(), 300);
                        }, 4000);
                    }
                }
            }
        </script>
    </x-filament-panels::page>
</div>
