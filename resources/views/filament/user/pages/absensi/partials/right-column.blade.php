<!-- Right Column: Map -->
<div class="md:col-span-7 lg:col-span-8 h-full">
    <div
        class="bg-white dark:bg-gray-900 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-800 overflow-hidden h-full flex flex-col">
        <div class="p-5 pb-0 flex justify-between items-center shrink-0">
            <h2 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Lokasi Anda</h2>
            <button @click="manualUpdateLocation()"
                class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 flex items-center space-x-1 bg-blue-50 dark:bg-blue-900/30 px-3 py-1.5 rounded-full transition-colors cursor-pointer active:bg-blue-100 dark:active:bg-blue-900/50">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <span>Update Lokasi</span>
            </button>
        </div>

        <div class="p-5 flex-grow flex flex-col">
            <div id="map"
                class="w-full rounded-2xl map-container bg-gray-100 dark:bg-gray-800 mb-4 z-0 flex-grow min-h-[300px] md:min-h-[400px]">
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-y-3 gap-x-4 text-sm shrink-0">
                <div class="flex flex-col">
                    <span class="text-xs text-gray-400 dark:text-gray-500">Latitude</span>
                    <span x-text="userLocation ? userLocation.lat.toFixed(5) : '-'"
                        class="font-mono font-medium text-gray-700 dark:text-gray-300"></span>
                </div>
                <div class="flex flex-col">
                    <span class="text-xs text-gray-400 dark:text-gray-500">Longitude</span>
                    <span x-text="userLocation ? userLocation.lng.toFixed(5) : '-'"
                        class="font-mono font-medium text-gray-700 dark:text-gray-300"></span>
                </div>
                <div class="flex flex-col">
                    <span class="text-xs text-gray-400 dark:text-gray-500">Akurasi</span>
                    <span x-text="userLocation ? '±' + userLocation.accuracy.toFixed(0) + 'm' : '-'"
                        class="font-mono font-medium text-gray-700 dark:text-gray-300"></span>
                </div>
                <div class="flex flex-col">
                    <span class="text-xs text-gray-400 dark:text-gray-500">Jarak Kantor</span>
                    <span x-text="userLocation ? userLocation.distance.toFixed(0) + 'm' : '-'"
                        class="font-mono font-medium text-gray-700 dark:text-gray-300"></span>
                </div>
            </div>
        </div>
    </div>
</div>
