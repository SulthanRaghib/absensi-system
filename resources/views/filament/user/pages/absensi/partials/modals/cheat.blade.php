<!-- Cheat Alert Modal -->
<div x-show="showCheatModal" style="display: none;" class="fixed inset-0 z-[9999] flex items-center justify-center p-6"
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
                        fill="#ef4444" fill-opacity="0.2" stroke="#ef4444" stroke-width="2.5" stroke-linecap="round"
                        stroke-linejoin="round" />
                    <path d="M26 18C26 14.6863 28.6863 12 32 12C35.3137 12 38 14.6863 38 18V24H26V18Z" fill="#ef4444"
                        fill-opacity="0.3" stroke="#ef4444" stroke-width="2.5" stroke-linecap="round"
                        stroke-linejoin="round" />
                    <circle cx="32" cy="38" r="4" fill="#ef4444" />
                    <path d="M32 42V46" stroke="#ef4444" stroke-width="2.5" stroke-linecap="round" />
                </svg>
            </div>
        </div>

        <!-- Title -->
        <h1 class="text-center mb-6 text-3xl font-bold text-slate-800" style="font-family: 'Inter', sans-serif;">
            Mau ngapain Hayoo!!
        </h1>

        <!-- Warning Text -->
        <p class="text-center mb-8 text-slate-600 leading-relaxed text-lg" style="font-family: 'Inter', sans-serif;">
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
