<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sistem Absensi</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Leaflet CSS & JS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
</head>

<body class="bg-gray-100">
    <div class="min-h-screen py-8">
        <div class="max-w-4xl mx-auto px-4">
            <!-- Header -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div
                            class="w-16 h-16 bg-blue-500 rounded-full flex items-center justify-center text-white text-2xl font-bold">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-800">{{ $user->name }}</h1>
                            <p class="text-gray-600">{{ $user->email }}</p>
                            <p class="text-sm text-gray-500">{{ now()->isoFormat('dddd, D MMMM Y') }}</p>
                        </div>
                    </div>
                    <a href="{{ route('dashboard') }}" class="text-blue-600 hover:text-blue-800">
                        ← Kembali
                    </a>
                </div>
            </div>

            <!-- Status Absensi -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Status Absensi Hari Ini</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div class="p-4 bg-green-50 rounded-lg">
                        <p class="text-sm text-gray-600 mb-1">Jam Masuk</p>
                        <p id="jam-masuk-display" class="text-2xl font-bold text-green-600">
                            {{ $todayAbsence?->jam_masuk ? $todayAbsence->jam_masuk->format('H:i:s') : '-' }}
                        </p>
                    </div>
                    <div class="p-4 bg-blue-50 rounded-lg">
                        <p class="text-sm text-gray-600 mb-1">Jam Pulang</p>
                        <p id="jam-pulang-display" class="text-2xl font-bold text-blue-600">
                            {{ $todayAbsence?->jam_pulang ? $todayAbsence->jam_pulang->format('H:i:s') : '-' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Map Preview -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Lokasi Anda</h2>
                <div id="map" class="h-64 rounded-lg mb-4"></div>

                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Latitude:</span>
                        <span id="lat-display" class="font-mono text-gray-800">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Longitude:</span>
                        <span id="lng-display" class="font-mono text-gray-800">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Akurasi:</span>
                        <span id="accuracy-display" class="font-mono text-gray-800">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Jarak dari kantor:</span>
                        <span id="distance-display" class="font-mono text-gray-800">-</span>
                    </div>
                </div>

                <button id="btn-get-location"
                    class="w-full mt-4 bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-3 px-4 rounded-lg transition">
                    📍 Ambil Lokasi Saya
                </button>
            </div>

            <!-- Tombol Absensi -->
            <div class="grid grid-cols-2 gap-4">
                <button id="btn-check-in"
                    class="bg-green-500 hover:bg-green-600 text-white font-bold py-4 px-6 rounded-lg transition disabled:bg-gray-300 disabled:cursor-not-allowed"
                    {{ $todayAbsence?->jam_masuk ? 'disabled' : '' }}>
                    ✓ Absen Masuk
                </button>

                <button id="btn-check-out"
                    class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-4 px-6 rounded-lg transition disabled:bg-gray-300 disabled:cursor-not-allowed"
                    {{ !$todayAbsence?->jam_masuk || $todayAbsence?->jam_pulang ? 'disabled' : '' }}>
                    ✓ Absen Pulang
                </button>
            </div>

            <!-- Alert Container -->
            <div id="alert-container" class="mt-6"></div>

            <!-- Location Permission Modal -->
            <div id="location-modal"
                class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50 flex items-center justify-center"
                style="z-index: 1000">
                <div class="relative p-5 border w-96 shadow-lg rounded-md bg-white">
                    <div class="mt-3 text-center">
                        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100">
                            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mt-4">Aktifkan Lokasi</h3>
                        <div class="mt-2 px-2 py-3">
                            <p class="text-sm text-gray-500">
                                Aplikasi ini memerlukan izin lokasi Anda untuk melakukan absensi. Silakan klik tombol di
                                bawah untuk mengizinkan.
                            </p>
                        </div>
                        <div class="items-center px-4 py-3">
                            <button id="btn-confirm-location"
                                class="w-full px-4 py-2 bg-blue-500 text-white text-base font-medium rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
                                Izinkan Lokasi
                            </button>
                            <button id="btn-cancel-location"
                                class="mt-3 w-full px-4 py-2 bg-gray-100 text-gray-700 text-base font-medium rounded-md shadow-sm hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-300">
                                Batal
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Configuration
        const OFFICE_LAT = {{ $officeLocation['latitude'] }};
        const OFFICE_LNG = {{ $officeLocation['longitude'] }};
        const OFFICE_RADIUS = {{ $officeLocation['radius'] }};
        const MAX_ACCURACY = 2500; // meters (Increased to allow wider range)

        // State
        let map, userMarker, officeCircle;
        let userLocation = null;

        // Initialize Map
        function initMap() {
            map = L.map('map').setView([OFFICE_LAT, OFFICE_LNG], 15);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            // Office marker
            L.marker([OFFICE_LAT, OFFICE_LNG], {
                icon: L.icon({
                    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
                    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
                    iconSize: [25, 41],
                    iconAnchor: [12, 41],
                })
            }).addTo(map).bindPopup('📍 Kantor BAPETEN');

            // Office radius circle
            officeCircle = L.circle([OFFICE_LAT, OFFICE_LNG], {
                color: 'blue',
                fillColor: '#3b82f6',
                fillOpacity: 0.1,
                radius: OFFICE_RADIUS
            }).addTo(map);
        }

        // Show Alert
        function showAlert(message, type = 'info') {
            const colors = {
                success: 'bg-green-100 border-green-500 text-green-700',
                error: 'bg-red-100 border-red-500 text-red-700',
                warning: 'bg-yellow-100 border-yellow-500 text-yellow-700',
                info: 'bg-blue-100 border-blue-500 text-blue-700'
            };

            const alert = document.createElement('div');
            alert.className = `border-l-4 p-4 rounded ${colors[type]}`;
            alert.innerHTML = `<p class="font-bold">${message}</p>`;

            const container = document.getElementById('alert-container');
            container.innerHTML = '';
            container.appendChild(alert);

            setTimeout(() => alert.remove(), 5000);
        }

        // Calculate Distance (Haversine)
        function calculateDistance(lat1, lon1, lat2, lon2) {
            const R = 6371000; // Earth radius in meters
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

        // Get User Location
        const locationModal = document.getElementById('location-modal');
        const btnGetLocation = document.getElementById('btn-get-location');
        const btnConfirmLocation = document.getElementById('btn-confirm-location');
        const btnCancelLocation = document.getElementById('btn-cancel-location');

        function toggleModal(show) {
            if (show) {
                locationModal.classList.remove('hidden');
            } else {
                locationModal.classList.add('hidden');
            }
        }

        btnGetLocation.addEventListener('click', function() {
            toggleModal(true);
        });

        btnCancelLocation.addEventListener('click', function() {
            toggleModal(false);
        });

        btnConfirmLocation.addEventListener('click', function() {
            toggleModal(false);
            getLocation();
        });

        function getLocation() {
            if (!navigator.geolocation) {
                showAlert('Browser Anda tidak mendukung GPS!', 'error');
                return;
            }

            btnGetLocation.innerHTML = '⏳ Mengambil lokasi...';
            btnGetLocation.disabled = true;

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    const accuracy = position.coords.accuracy;

                    // Validate accuracy
                    if (accuracy > MAX_ACCURACY) {
                        showAlert(
                            `Akurasi GPS terlalu buruk (${accuracy.toFixed(2)}m). Harap aktifkan GPS dengan baik!`,
                            'error');
                        btnGetLocation.innerHTML = '📍 Coba Lagi';
                        btnGetLocation.disabled = false;
                        return;
                    }

                    // Calculate distance
                    const distance = calculateDistance(lat, lng, OFFICE_LAT, OFFICE_LNG);

                    // Update display
                    document.getElementById('lat-display').textContent = lat.toFixed(6);
                    document.getElementById('lng-display').textContent = lng.toFixed(6);
                    document.getElementById('accuracy-display').textContent = `${accuracy.toFixed(2)} m`;
                    document.getElementById('distance-display').textContent = `${distance.toFixed(2)} m`;

                    // Update map
                    if (userMarker) map.removeLayer(userMarker);

                    userMarker = L.marker([lat, lng], {
                        icon: L.icon({
                            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
                            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
                            iconSize: [25, 41],
                            iconAnchor: [12, 41],
                        })
                    }).addTo(map).bindPopup('📍 Lokasi Anda');

                    map.setView([lat, lng], 16);

                    // Store location
                    userLocation = {
                        lat,
                        lng,
                        accuracy,
                        distance
                    };

                    // Show status
                    if (distance <= OFFICE_RADIUS) {
                        showAlert(`✓ Lokasi valid! Jarak: ${distance.toFixed(2)}m dari kantor.`, 'success');
                    } else {
                        showAlert(
                            `✗ Lokasi terlalu jauh! Jarak: ${distance.toFixed(2)}m (Maks: ${OFFICE_RADIUS}m)`,
                            'error');
                    }

                    btnGetLocation.innerHTML = '✓ Lokasi Berhasil Diambil';
                    btnGetLocation.disabled = false;
                },
                (error) => {
                    let message = 'Gagal mengambil lokasi!';
                    if (error.code === 1) message = 'Anda menolak izin lokasi!';
                    if (error.code === 2) message = 'Lokasi tidak tersedia!';
                    if (error.code === 3) message = 'Timeout mengambil lokasi!';

                    showAlert(message, 'error');
                    btnGetLocation.innerHTML = '📍 Ambil Lokasi Saya';
                    btnGetLocation.disabled = false;
                }, {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        }

        // Check In
        document.getElementById('btn-check-in').addEventListener('click', async function() {
            if (!userLocation) {
                showAlert('Harap ambil lokasi terlebih dahulu!', 'warning');
                return;
            }

            this.disabled = true;
            this.innerHTML = '⏳ Memproses...';

            try {
                const response = await fetch('{{ route('absensi.check-in') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        latitude: userLocation.lat,
                        longitude: userLocation.lng,
                        accuracy: userLocation.accuracy
                    })
                });

                const data = await response.json();

                if (data.success) {
                    showAlert(data.message, 'success');
                    document.getElementById('jam-masuk-display').textContent = data.data.jam_masuk;
                    this.disabled = true;
                    document.getElementById('btn-check-out').disabled = false;
                } else {
                    showAlert(data.message, 'error');
                    this.disabled = false;
                }
            } catch (error) {
                showAlert('Terjadi kesalahan sistem!', 'error');
                this.disabled = false;
            }

            this.innerHTML = '✓ Absen Masuk';
        });

        // Check Out
        document.getElementById('btn-check-out').addEventListener('click', async function() {
            if (!userLocation) {
                showAlert('Harap ambil lokasi terlebih dahulu!', 'warning');
                return;
            }

            this.disabled = true;
            this.innerHTML = '⏳ Memproses...';

            try {
                const response = await fetch('{{ route('absensi.check-out') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        latitude: userLocation.lat,
                        longitude: userLocation.lng,
                        accuracy: userLocation.accuracy
                    })
                });

                const data = await response.json();

                if (data.success) {
                    showAlert(data.message, 'success');
                    document.getElementById('jam-pulang-display').textContent = data.data.jam_pulang;
                    this.disabled = true;
                } else {
                    showAlert(data.message, 'error');
                    this.disabled = false;
                }
            } catch (error) {
                showAlert('Terjadi kesalahan sistem!', 'error');
                this.disabled = false;
            }

            this.innerHTML = '✓ Absen Pulang';
        });

        // Initialize on load
        document.addEventListener('DOMContentLoaded', function() {
            initMap();
        });
    </script>
</body>

</html>
