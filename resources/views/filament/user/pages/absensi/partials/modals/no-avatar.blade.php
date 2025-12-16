<!-- No Avatar Modal -->
<div x-show="showNoAvatarModal" style="display: none;" class="fixed inset-0 z-[9999] flex items-center justify-center p-6"
    x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showNoAvatarModal = false"></div>

    <!-- Modal Content -->
    <div class="relative bg-white rounded-3xl p-8 max-w-md w-full shadow-2xl flex flex-col items-center text-center">
        <div class="w-20 h-20 bg-yellow-100 rounded-full flex items-center justify-center mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-yellow-600" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
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
            <a href="{{ route('filament.user.pages.profile') }}"
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
