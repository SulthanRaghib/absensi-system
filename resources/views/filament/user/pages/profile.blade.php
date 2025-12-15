<x-filament-panels::page>
    <style>
        /* Keep the cropper viewport usable on tall images / small screens */
        .avatar-cropper-stage {
            height: min(60vh, 560px);
        }

        .avatar-cropper-stage .cropper-container,
        .avatar-cropper-stage .cropper-wrap-box,
        .avatar-cropper-stage .cropper-canvas {
            width: 100% !important;
            height: 100% !important;
        }

        .avatar-cropper-stage img {
            max-width: 100%;
            display: block;
        }
    </style>

    <div x-data="smartProfile({ userAvatar: '{{ Auth::user()->avatar_url }}', userName: '{{ Auth::user()->name }}', storageUrl: '{{ asset('storage') }}' })" class="space-y-6">
        <!-- Avatar Section -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 dark:bg-gray-900 dark:border-gray-800">
            <h2 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Foto Profil</h2>

            <div class="flex flex-col md:flex-row gap-6 items-start">
                <!-- Preview Area -->
                <div class="relative group shrink-0">
                    <div
                        class="w-40 h-40 rounded-full overflow-hidden bg-gray-100 border-4 border-white shadow-lg relative dark:bg-gray-800 dark:border-gray-700">
                        <!-- Current Avatar or Preview -->
                        <template x-if="previewUrl">
                            <img :src="previewUrl" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!previewUrl">
                            <img :src="currentAvatar" class="w-full h-full object-cover">
                        </template>

                        <!-- Loading Overlay -->
                        <div x-show="isAnalyzing"
                            class="absolute inset-0 bg-black/50 flex items-center justify-center z-10">
                            <svg class="animate-spin h-8 w-8 text-white" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                        </div>
                    </div>

                    <!-- Delete Current Avatar Button (X) -->
                    <button x-show="!previewUrl && userAvatar" @click="openDeleteAvatarModal" type="button"
                        class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1.5 shadow-md hover:bg-red-600 transition-colors z-30"
                        title="Hapus Foto Profil">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                clip-rule="evenodd" />
                        </svg>
                    </button>

                    <!-- Upload Button -->
                    <label
                        class="absolute bottom-2 right-2 bg-blue-600 text-white p-2.5 rounded-full cursor-pointer hover:bg-blue-700 transition-colors shadow-md z-20">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <input type="file" class="hidden" accept="image/*" @change="handleFileSelect"
                            x-ref="fileInput">
                    </label>

                    <!-- Cancel Button -->
                    <button x-show="previewUrl" @click="resetUpload" type="button"
                        class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1.5 shadow-md hover:bg-red-600 transition-colors z-30"
                        title="Batalkan Upload">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>

                <!-- Delete Avatar Confirmation Modal -->
                <div x-show="showDeleteAvatarModal" x-cloak style="display: none;"
                    class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
                    x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

                    <div class="absolute inset-0" @click="closeDeleteAvatarModal"></div>

                    <div class="relative w-full max-w-sm bg-white rounded-2xl shadow-2xl p-6">
                        <h3 class="text-lg font-semibold text-gray-900">Hapus Foto Profil?</h3>
                        <p class="text-sm text-gray-600 mt-2">Foto profil akan dihapus dari sistem.</p>

                        <div class="mt-6 flex gap-3 justify-end">
                            <button type="button" @click="closeDeleteAvatarModal"
                                class="px-4 py-2 rounded-lg bg-gray-100 text-gray-700 font-medium hover:bg-gray-200">
                                Tidak
                            </button>
                            <button type="button" @click="confirmDeleteAvatar"
                                class="px-4 py-2 rounded-lg bg-red-600 text-white font-semibold hover:bg-red-700">
                                Ya, Hapus
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Validation Status -->
                <div class="flex-1 space-y-3 w-full">
                    <div x-show="validationStatus === 'idle'" class="text-gray-500 text-sm dark:text-gray-400">
                        <p class="font-medium mb-1">Panduan Foto Profil:</p>
                        <ul class="list-disc list-inside space-y-1 ml-1">
                            <li>Wajah harus terlihat jelas dan fokus.</li>
                            <li>Hanya satu wajah dalam foto.</li>
                            <li>Wajah harus memenuhi minimal 40% area foto.</li>
                            <li>Posisi wajah harus di tengah.</li>
                        </ul>
                    </div>

                    <div x-show="validationStatus === 'analyzing'"
                        class="text-blue-600 font-medium flex items-center gap-2 p-3 bg-blue-50 rounded-lg border border-blue-100 dark:bg-blue-900/20 dark:border-blue-800 dark:text-blue-400">
                        <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        <span>Sedang menganalisis kualitas foto...</span>
                    </div>

                    <div x-show="validationStatus === 'valid'"
                        class="p-4 bg-green-50 text-green-700 rounded-lg border border-green-200 text-sm flex items-start gap-3 dark:bg-green-900/20 dark:border-green-800 dark:text-green-400">
                        <div class="bg-green-100 rounded-full p-1 dark:bg-green-800">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div>
                            <p class="font-bold text-base">Foto Valid!</p>
                            <p class="mt-1">Kualitas foto sangat baik. Anda dapat menyimpan perubahan sekarang.</p>
                        </div>
                    </div>

                    <div x-show="validationStatus === 'invalid'"
                        class="p-4 bg-red-50 text-red-700 rounded-lg border border-red-200 text-sm flex items-start gap-3 dark:bg-red-900/20 dark:border-red-800 dark:text-red-400">
                        <div class="bg-red-100 rounded-full p-1 dark:bg-red-800">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div>
                            <p class="font-bold text-base">Foto Tidak Valid</p>
                            <p class="mt-1" x-text="errorMessage"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form -->
        {{ $this->form }}

        <!-- Actions -->
        <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
            <x-filament::button wire:click="save" class="w-full md:w-auto"
                x-bind:disabled="isAnalyzing || validationStatus === 'invalid'">
                Simpan Perubahan
            </x-filament::button>
        </div>

        <!-- Cropper Modal -->
        <div x-show="showCropperModal" style="display: none;"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/75 backdrop-blur-sm"
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

            <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl overflow-hidden flex flex-col max-h-[90vh]">
                <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900">Sesuaikan Foto</h3>
                    <button @click="cancelCrop" class="text-gray-500 hover:text-gray-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="flex-1 p-4 bg-gray-900">
                    <div class="mx-auto w-full max-w-xl">
                        <div class="avatar-cropper-stage w-full overflow-hidden rounded-lg bg-gray-900">
                            <img id="cropper-image" :src="cropperImage" alt="Cropper" />
                        </div>
                    </div>
                </div>

                <div class="p-4 border-t border-gray-200 flex justify-end gap-3 bg-gray-50">
                    <button @click="cancelCrop"
                        class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 font-medium">
                        Batal
                    </button>
                    <button @click="applyCrop"
                        class="px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700 font-medium shadow-sm">
                        Terapkan & Validasi
                    </button>
                </div>
            </div>
        </div>
    </div>

</x-filament-panels::page>
