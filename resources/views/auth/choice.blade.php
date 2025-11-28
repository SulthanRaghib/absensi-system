<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Masuk — Absensi</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="min-h-screen bg-gray-50 flex items-center justify-center" x-data="{
    openDirect: false,
    latitude: null,
    longitude: null,
    locationError: null,
    getLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    this.latitude = position.coords.latitude;
                    this.longitude = position.coords.longitude;
                    this.locationError = null;
                },
                (error) => {
                    switch (error.code) {
                        case error.PERMISSION_DENIED:
                            this.locationError = 'Izin lokasi ditolak. Mohon aktifkan izin lokasi.';
                            break;
                        case error.POSITION_UNAVAILABLE:
                            this.locationError = 'Informasi lokasi tidak tersedia.';
                            break;
                        case error.TIMEOUT:
                            this.locationError = 'Waktu permintaan lokasi habis.';
                            break;
                        default:
                            this.locationError = 'Terjadi kesalahan yang tidak diketahui.';
                    }
                }
            );
        } else {
            this.locationError = 'Geolocation tidak didukung oleh browser ini.';
        }
    }
}" x-init="getLocation()">
    <div class="max-w-xl w-full p-6">
        <div class="bg-white shadow rounded-lg p-6 text-center">
            <h1 class="text-2xl font-semibold mb-2">Selamat datang di Sistem Absensi</h1>
            <p class="text-sm text-gray-600 mb-6">Pilih jenis akun untuk masuk. Untuk karyawan biasa, klik "Masuk
                sebagai User". Untuk administrasi sistem, klik "Masuk sebagai Admin".</p>

            @if (session('success'))
                <div class="mb-4 p-4 bg-green-100 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <a href="{{ route('filament.user.auth.login') }}"
                    class="inline-block bg-amber-500 hover:bg-amber-600 text-white font-semibold py-3 px-4 rounded shadow">Masuk
                    sebagai User</a>

                <a href="{{ route('filament.admin.auth.login') }}"
                    class="inline-block border border-gray-200 hover:bg-gray-50 text-gray-700 font-medium py-3 px-4 rounded">Masuk
                    sebagai Admin</a>
            </div>

            <div class="mt-4">
                <button @click="openDirect=true; getLocation()"
                    class="w-full inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded shadow">
                    Absen Langsung
                </button>
            </div>

            <p class="mt-6 text-xs text-gray-400">Jika Anda sudah masuk, Anda akan dialihkan sesuai peran Anda.</p>
        </div>

        <p class="mt-4 text-center text-xs text-gray-500">Contact: admin@contoh.local</p>
    </div>

    <!-- Modal Absen Langsung -->
    <div x-show="openDirect" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Backdrop -->
            <div x-show="openDirect" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="openDirect=false"
                aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <!-- Modal Panel -->
            <div x-show="openDirect" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">

                <form action="{{ route('attendance.direct') }}" method="POST">
                    @csrf
                    <input type="hidden" name="latitude" x-model="latitude">
                    <input type="hidden" name="longitude" x-model="longitude">

                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div
                                class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    Absen Langsung
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 mb-4">
                                        Masukkan kredensial Anda untuk melakukan absensi (Masuk/Pulang) secara cepat.
                                    </p>

                                    <!-- Location Status -->
                                    <div class="mb-4 text-sm text-left">
                                        <template x-if="latitude && longitude">
                                            <p class="text-green-600 font-medium">✓ Lokasi terdeteksi</p>
                                        </template>
                                        <template x-if="!latitude || !longitude">
                                            <p class="text-amber-600 font-medium">⟳ Mendeteksi lokasi...</p>
                                        </template>
                                        <template x-if="locationError">
                                            <p class="text-red-600 font-medium" x-text="locationError"></p>
                                        </template>
                                    </div>

                                    <div class="mb-4">
                                        <label for="email"
                                            class="block text-sm font-medium text-gray-700">Email</label>
                                        <input type="email" name="email" id="email" required
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    </div>

                                    <div class="mb-4">
                                        <label for="password"
                                            class="block text-sm font-medium text-gray-700">Password</label>
                                        <input type="password" name="password" id="password" required
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" :disabled="!latitude || !longitude"
                            :class="{ 'opacity-50 cursor-not-allowed': !latitude || !longitude }"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Konfirmasi Absen
                        </button>
                        <button type="button" @click="openDirect = false"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>
