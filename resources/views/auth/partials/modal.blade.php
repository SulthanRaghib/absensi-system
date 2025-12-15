<!-- Modal Absen Langsung -->
<div x-show="openDirect" style="display: none;"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 backdrop-blur-sm bg-black/40"
    aria-labelledby="modal-title" role="dialog" aria-modal="true">

    <!-- Backdrop -->
    <div x-show="openDirect" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="absolute inset-0"
        @click="openDirect=false">
    </div>

    <!-- Modal Container -->
    <div x-show="openDirect" x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100" x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 scale-95"
        class="relative bg-white/90 backdrop-blur-md border border-white/20 shadow-2xl
                rounded-2xl w-full max-w-lg overflow-hidden">

        <!-- ===================================================
             MAIN FORM VIEW
        =================================================== -->
        <div x-show="!showConfirm">

            <form action="{{ route('attendance.direct') }}" method="POST" x-ref="attendanceForm"
                @submit.prevent="checkAttendance">
                @csrf

                <!-- Hidden Fields -->
                <input type="hidden" name="latitude" x-model="latitude">
                <input type="hidden" name="longitude" x-model="longitude">
                <input type="hidden" name="accuracy" x-model="accuracy">
                <input type="hidden" name="device_token" x-model="deviceToken">
                <input type="hidden" name="email" x-model="email">
                <input type="hidden" name="password" x-model="password">
                <input type="hidden" name="image" x-model="capturedImage">

                <!-- Header -->
                <div class="p-6 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="h-12 w-12 flex items-center justify-center rounded-xl bg-blue-100">
                            <svg class="h-7 w-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Absen Langsung</h3>
                            <p class="text-sm text-gray-500">Masukkan kredensial untuk melakukan absensi cepat.</p>
                        </div>
                    </div>
                </div>

                <!-- Body -->
                <div class="p-6 space-y-6">

                    <!-- Map -->
                    <div>
                        <div id="map" class="w-full h-52 rounded-xl bg-gray-100 shadow-inner"></div>

                        <!-- Status -->
                        <div class="mt-3 grid grid-cols-2 text-sm text-gray-600 gap-4">
                            <div class="flex justify-between">
                                <span>Akurasi:</span>
                                <span id="accuracy-display" class="font-mono font-medium">-</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Jarak:</span>
                                <span id="distance-display" class="font-mono font-medium">-</span>
                            </div>
                        </div>

                        <div class="mt-3 text-sm">
                            <template x-if="latitude && longitude">
                                <p class="text-green-600 font-medium flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Lokasi terdeteksi
                                </p>
                            </template>

                            <template x-if="!latitude || !longitude">
                                <p class="text-amber-600 font-medium flex items-center gap-2">
                                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.37 0 0 5.37 0 12h4z"></path>
                                    </svg>
                                    Mendeteksi lokasi...
                                </p>
                            </template>

                            <template x-if="locationError">
                                <p class="text-red-600 font-medium" x-text="locationError"></p>
                            </template>
                        </div>
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="text-sm font-medium text-gray-700">Email</label>
                        <input type="email" x-model="email"
                            class="mt-1 w-full px-4 py-2 rounded-xl border border-gray-300
                                      focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                    </div>

                    <!-- Password -->
                    <div x-data="{ show: false }">
                        <label class="text-sm font-medium text-gray-700">Password</label>

                        <div class="relative mt-1">
                            <input :type="show ? 'text' : 'password'" x-model="password"
                                class="w-full px-4 py-2 rounded-xl border border-gray-300
                   focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm pr-10">

                            <!-- Eye Icon -->
                            <button type="button" @click="show = !show"
                                class="absolute inset-y-0 right-3 flex items-center text-gray-500 hover:text-gray-700">
                                <!-- Eye Open -->
                                <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0
                         8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477
                         0-8.268-2.943-9.542-7z" />
                                </svg>

                                <!-- Eye Closed -->
                                <svg x-show="show" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7
                         A10.058 10.058 0 014.2 7.42m3.62-2.24A9.956 9.956
                         0 0112 5c4.478 0 8.268 2.943 9.542 7a9.98 9.98
                         0 01-1.563 3.029M15 12a3 3 0 00-3-3m0 0a3 3
                         0 013 3m-3-3L3 3m9 9l9 9" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="p-6 bg-gray-50 flex flex-col sm:flex-row-reverse gap-2">
                    <button type="submit" :disabled="!latitude || !longitude || isLoading"
                        :class="{ 'opacity-50 cursor-not-allowed': !latitude || !longitude || isLoading }"
                        class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-blue-600 text-white font-semibold
                                   hover:bg-blue-700 shadow-md transition">
                        <span x-show="!isLoading">Konfirmasi Absen</span>
                        <span x-show="isLoading">Memproses...</span>
                    </button>

                    <button type="button" @click="openDirect = false"
                        class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-white border border-gray-300
                                   text-gray-700 font-medium hover:bg-gray-100 shadow-sm transition">
                        Batal
                    </button>
                </div>
            </form>
        </div>

        <!-- ===================================================
             CONFIRMATION VIEW
        =================================================== -->
        <div x-show="showConfirm" style="display: none;">

            <div class="p-6 border-b border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="h-12 w-12 flex items-center justify-center rounded-xl bg-yellow-100">
                        <svg class="h-7 w-7 text-yellow-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667
                                     1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77
                                     1.333.192 3 1.732 3z" />
                        </svg>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Konfirmasi Absen Pulang</h3>
                        <p class="text-sm text-gray-500" x-text="confirmMessage"></p>
                    </div>
                </div>
            </div>

            <div class="p-6 bg-gray-50 flex flex-col sm:flex-row-reverse gap-2">
                <button type="button" @click="submitForm()"
                    class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-blue-600 text-white font-semibold
                               hover:bg-blue-700 shadow transition">
                    Ya, Absen Pulang
                </button>

                <button type="button" @click="showConfirm = false"
                    class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-white border border-gray-300
                               text-gray-700 font-medium hover:bg-gray-100 shadow-sm transition">
                    Kembali
                </button>
            </div>
        </div>

    </div>

    <!-- Face Verification Modal -->
    <div x-show="showFaceModal" style="display: none;"
        class="fixed inset-0 z-[60] flex items-center justify-center p-4"
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/90 backdrop-blur-md" @click="closeFaceModal()"></div>

        <!-- Modal Content -->
        <div class="relative w-full max-w-md mx-auto flex flex-col items-center justify-center min-h-[500px]">

            <!-- Status Badge -->
            <div class="absolute top-0 z-20 mt-8">
                <div class="px-4 py-2 rounded-full backdrop-blur-md border border-white/10 shadow-xl transition-all duration-300"
                    :class="{
                        'bg-white/10 text-white': faceStatus === 'scanning',
                        'bg-blue-500/20 text-blue-200 border-blue-500/50': faceStatus === 'detecting',
                        'bg-green-500/20 text-green-200 border-green-500/50': faceStatus === 'success',
                        'bg-red-500/20 text-red-200 border-red-500/50': faceStatus === 'error'
                    }">
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 rounded-full animate-pulse"
                            :class="{
                                'bg-white': faceStatus === 'scanning',
                                'bg-blue-400': faceStatus === 'detecting',
                                'bg-green-400': faceStatus === 'success',
                                'bg-red-400': faceStatus === 'error'
                            }">
                        </div>
                        <span class="text-sm font-medium tracking-wide" x-text="faceMessage"></span>
                    </div>
                </div>
            </div>

            <!-- Camera Container -->
            <div
                class="relative w-72 h-72 sm:w-80 sm:h-80 rounded-full overflow-hidden border-4 border-white/10 shadow-2xl bg-black">
                <video x-ref="video" autoplay muted playsinline
                    class="w-full h-full object-cover transform scale-x-[-1]"></video>
                <canvas x-ref="canvas"
                    class="absolute inset-0 w-full h-full pointer-events-none transform scale-x-[-1]"></canvas>

                <!-- Scanning Overlay -->
                <div x-show="isScanning && !isMatched"
                    class="absolute inset-0 bg-gradient-to-b from-blue-500/0 via-blue-500/10 to-blue-500/0 animate-scan pointer-events-none">
                    <div class="h-[2px] w-full bg-blue-400/50 shadow-[0_0_15px_rgba(96,165,250,0.8)]"></div>
                </div>

                <!-- Success Overlay -->
                <div x-show="isMatched"
                    class="absolute inset-0 flex items-center justify-center bg-green-500/20 backdrop-blur-[2px] transition-all duration-500">
                    <div class="bg-white rounded-full p-4 shadow-lg animate-bounce">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Instructions / Controls -->
            <div class="mt-8 text-center space-y-4 z-20">
                <p class="text-white/60 text-sm max-w-[200px] mx-auto leading-relaxed"
                    x-show="!showRetry && !isMatched">
                    Posisikan wajah Anda di dalam lingkaran
                </p>

                <button x-show="showRetry" @click="retryScan()"
                    class="px-6 py-2 bg-white text-gray-900 rounded-full font-medium hover:bg-gray-100 transition-colors shadow-lg transform hover:scale-105 active:scale-95">
                    Coba Lagi
                </button>

                <button @click="closeFaceModal()"
                    class="text-white/40 text-sm hover:text-white transition-colors mt-4">
                    Batalkan
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes scan {
        0% {
            transform: translateY(-100%);
        }

        100% {
            transform: translateY(100%);
        }
    }

    .animate-scan {
        animation: scan 2s linear infinite;
    }
</style>
