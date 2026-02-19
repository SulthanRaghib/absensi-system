<!-- Early Checkout Modal -->
<div x-show="showEarlyCheckoutModal" x-cloak
    class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
    x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xl w-full max-w-md p-6 transform transition-all relative"
        style="max-width: 28rem;" @click.away="showEarlyCheckoutModal = false"
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">

        <div class="text-center">
            <div
                class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 dark:bg-yellow-900/30 mb-4">
                <svg class="h-6 w-6 text-yellow-600 dark:text-yellow-400" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Konfirmasi Pulang Awal</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                Anda melakukan absen pulang sebelum jam kerja berakhir. Apakah Anda yakin ingin melanjutkan?
            </p>

            <div class="flex gap-3 justify-center">
                <button @click="showEarlyCheckoutModal = false" type="button"
                    class="px-4 py-2 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 font-medium transition-colors">
                    Batal
                </button>
                <button @click="confirmEarlyCheckout()" type="button" style="background-color: #ca8a04; color: white;"
                    class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 font-medium transition-colors shadow-lg shadow-yellow-600/20">
                    Ya, Lanjutkan
                </button>
            </div>
        </div>
    </div>
</div>
