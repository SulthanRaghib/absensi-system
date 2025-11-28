<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Masuk — Absensi</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Leaflet CSS & JS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
    <style>
        .map-container {
            box-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.06);
        }
    </style>
</head>

<body class="min-h-screen bg-gray-50 flex items-center justify-center" x-data="{
    openDirect: false,
    latitude: null,
    longitude: null,
    locationError: null,
    map: null,
    userMarker: null,

    // Form & State
    email: '',
    password: '',
    isLoading: false,
    showConfirm: false,
    confirmMessage: '',
    status: '',

    init() {
        this.$watch('openDirect', value => {
            if (value) {
                this.$nextTick(() => {
                    this.initMap();
                    this.getLocation();
                });
            } else {
                // Reset state when closed
                this.showConfirm = false;
                this.email = '';
                this.password = '';
            }
        });
    },

    async checkAttendance() {
        this.isLoading = true;
        try {
            const response = await fetch('{{ route('attendance.check-status') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    email: this.email,
                    password: this.password
                })
            });

            const data = await response.json();

            if (!response.ok) {
                alert(data.message || 'Terjadi kesalahan');
                this.isLoading = false;
                return;
            }

            if (data.status === 'check-out') {
                this.showConfirm = true;
                this.confirmMessage = data.message;
                this.status = 'check-out';
            } else if (data.status === 'completed') {
                alert(data.message);
            } else {
                // check-in or others, submit immediately
                this.$refs.attendanceForm.submit();
            }
        } catch (error) {
            console.error(error);
            alert('Terjadi kesalahan koneksi');
        } finally {
            this.isLoading = false;
        }
    },

    submitForm() {
        this.$refs.attendanceForm.submit();
    },

    initMap() {
        if (this.map) return;

        const OFFICE_LAT = {{ $officeLocation['latitude'] }};
        const OFFICE_LNG = {{ $officeLocation['longitude'] }};
        const OFFICE_RADIUS = {{ $officeLocation['radius'] }};

        this.map = L.map('map', {
            zoomControl: false,
            attributionControl: false
        }).setView([OFFICE_LAT, OFFICE_LNG], 15);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(this.map);

        const officeIcon = L.divIcon({
            className: 'custom-div-icon',
            html: `<div style='background-color: #EF4444; width: 12px; height: 12px; border-radius: 50%; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3);'></div>`,
            iconSize: [12, 12],
            iconAnchor: [6, 6]
        });

        L.marker([OFFICE_LAT, OFFICE_LNG], { icon: officeIcon }).addTo(this.map);

        L.circle([OFFICE_LAT, OFFICE_LNG], {
            color: '#3B82F6',
            fillColor: '#3B82F6',
            fillOpacity: 0.1,
            weight: 1,
            radius: OFFICE_RADIUS
        }).addTo(this.map);
    },

    getLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.watchPosition(
                (position) => {
                    this.latitude = position.coords.latitude;
                    this.longitude = position.coords.longitude;
                    this.locationError = null;
                    this.updateUserLocation(position.coords);
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
                }, {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        } else {
            this.locationError = 'Geolocation tidak didukung oleh browser ini.';
        }
    },

    updateUserLocation(coords) {
        if (!this.map) return;

        const lat = coords.latitude;
        const lng = coords.longitude;

        if (this.userMarker) {
            this.userMarker.setLatLng([lat, lng]);
        } else {
            const userIcon = L.divIcon({
                className: 'custom-div-icon',
                html: `<div style='background-color: #3B82F6; width: 12px; height: 12px; border-radius: 50%; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3);'></div>`,
                iconSize: [12, 12],
                iconAnchor: [6, 6]
            });
            this.userMarker = L.marker([lat, lng], { icon: userIcon }).addTo(this.map);
        }

        this.map.setView([lat, lng], 16);

        const dist = this.calculateDistance(lat, lng, {{ $officeLocation['latitude'] }}, {{ $officeLocation['longitude'] }});

        const distEl = document.getElementById('distance-display');
        const accEl = document.getElementById('accuracy-display');

        if (distEl) distEl.innerText = Math.round(dist) + ' m';
        if (accEl) accEl.innerText = Math.round(coords.accuracy) + ' m';
    },

    calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371e3;
        const φ1 = lat1 * Math.PI / 180;
        const φ2 = lat2 * Math.PI / 180;
        const Δφ = (lat2 - lat1) * Math.PI / 180;
        const Δλ = (lon2 - lon1) * Math.PI / 180;

        const a = Math.sin(Δφ / 2) * Math.sin(Δφ / 2) +
            Math.cos(φ1) * Math.cos(φ2) *
            Math.sin(Δλ / 2) * Math.sin(Δλ / 2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

        return R * c;
    }
}">
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

                <!-- Main Form View -->
                <div x-show="!showConfirm">
                    <form action="{{ route('attendance.direct') }}" method="POST" x-ref="attendanceForm"
                        @submit.prevent="checkAttendance">
                        @csrf
                        <input type="hidden" name="latitude" x-model="latitude">
                        <input type="hidden" name="longitude" x-model="longitude">
                        <!-- Hidden inputs for email/pass to be submitted with the form -->
                        <input type="hidden" name="email" x-model="email">
                        <input type="hidden" name="password" x-model="password">

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
                                            Masukkan kredensial Anda untuk melakukan absensi (Masuk/Pulang) secara
                                            cepat.
                                        </p>

                                        <!-- Map & Location Status -->
                                        <div class="mb-4">
                                            <div id="map"
                                                class="w-full h-48 rounded-lg map-container bg-gray-100 mb-3 z-0"></div>

                                            <div class="grid grid-cols-2 gap-2 text-xs text-gray-600 mb-2">
                                                <div class="flex justify-between">
                                                    <span>Akurasi:</span>
                                                    <span id="accuracy-display" class="font-mono font-medium">-</span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span>Jarak:</span>
                                                    <span id="distance-display" class="font-mono font-medium">-</span>
                                                </div>
                                            </div>

                                            <div class="text-sm text-left">
                                                <template x-if="latitude && longitude">
                                                    <p class="text-green-600 font-medium flex items-center">
                                                        <svg class="w-4 h-4 mr-1" fill="none"
                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                        </svg>
                                                        Lokasi terdeteksi
                                                    </p>
                                                </template>
                                                <template x-if="!latitude || !longitude">
                                                    <p class="text-amber-600 font-medium flex items-center">
                                                        <svg class="w-4 h-4 mr-1 animate-spin" fill="none"
                                                            viewBox="0 0 24 24">
                                                            <circle class="opacity-25" cx="12" cy="12"
                                                                r="10" stroke="currentColor" stroke-width="4">
                                                            </circle>
                                                            <path class="opacity-75" fill="currentColor"
                                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                            </path>
                                                        </svg>
                                                        Mendeteksi lokasi...
                                                    </p>
                                                </template>
                                                <template x-if="locationError">
                                                    <p class="text-red-600 font-medium flex items-center"
                                                        x-text="locationError"></p>
                                                </template>
                                            </div>
                                        </div>

                                        <div class="mb-4">
                                            <label for="email"
                                                class="block text-sm font-medium text-gray-700">Email</label>
                                            <input type="email" x-model="email" id="email" required
                                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                        </div>

                                        <div class="mb-4">
                                            <label for="password"
                                                class="block text-sm font-medium text-gray-700">Password</label>
                                            <input type="password" x-model="password" id="password" required
                                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" :disabled="!latitude || !longitude || isLoading"
                                :class="{ 'opacity-50 cursor-not-allowed': !latitude || !longitude || isLoading }"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                                <span x-show="!isLoading">Konfirmasi Absen</span>
                                <span x-show="isLoading">Memproses...</span>
                            </button>
                            <button type="button" @click="openDirect = false"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Confirmation View -->
                <div x-show="showConfirm" style="display: none;">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div
                                class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-yellow-600" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">
                                    Konfirmasi Absen Pulang
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500" x-text="confirmMessage"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" @click="submitForm()"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Ya, Absen Pulang
                        </button>
                        <button type="button" @click="showConfirm = false"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Kembali
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
