<!-- Face Verification Modal -->
<div x-show="showFaceModal" style="display: none;" class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
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
            <video x-ref="video" autoplay muted playsinline class="w-full h-full object-cover"
                style="transform: scaleX(-1); transform-origin: center;"></video>
            <canvas x-ref="canvas" class="absolute inset-0 w-full h-full pointer-events-none"
                style="transform: scaleX(-1); transform-origin: center;"></canvas>

            <!-- Hold-Still Progress Ring -->
            <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                <svg class="w-[92%] h-[92%]" viewBox="0 0 120 120" aria-hidden="true">
                    <circle cx="60" cy="60" r="52" fill="none" stroke="rgba(255,255,255,0.10)"
                        stroke-width="6" />
                    <circle cx="60" cy="60" r="52" fill="none" stroke-width="6" stroke-linecap="round"
                        :stroke="faceProgressPercent >= 100 ? '#22c55e' : '#3b82f6'"
                        :stroke-dasharray="faceProgressDashArray" :stroke-dashoffset="faceProgressDashOffset"
                        transform="rotate(-90 60 60)"
                        style="transition: stroke-dashoffset 120ms linear, stroke 200ms ease;" />
                </svg>
            </div>

            <!-- Scanning Overlay -->
            <div x-show="isScanning && !isMatched"
                class="absolute inset-0 bg-gradient-to-b from-blue-500/0 via-blue-500/10 to-blue-500/0 animate-scan pointer-events-none">
                <div class="h-[2px] w-full bg-blue-400/50 shadow-[0_0_15px_rgba(96,165,250,0.8)]"></div>
            </div>

            <!-- Success Overlay -->
            <div x-show="isMatched"
                class="absolute inset-0 flex items-center justify-center bg-green-500/20 backdrop-blur-[2px] transition-all duration-500">
                <div class="bg-white rounded-full p-4 shadow-lg animate-bounce">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7">
                        </path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Instructions / Controls -->
        <div class="mt-8 text-center space-y-4 z-20">
            <p class="text-white/60 text-sm max-w-[200px] mx-auto leading-relaxed" x-show="!showRetry && !isMatched">
                Posisikan wajah Anda di dalam lingkaran
            </p>

            <button x-show="showRetry" @click="retryScan()"
                class="px-6 py-2 bg-white text-gray-900 rounded-full font-medium hover:bg-gray-100 transition-colors shadow-lg transform hover:scale-105 active:scale-95">
                Coba Lagi
            </button>

            <button @click="closeFaceModal()" class="text-white/40 text-sm hover:text-white transition-colors mt-4">
                Batalkan
            </button>
        </div>
    </div>
</div>
