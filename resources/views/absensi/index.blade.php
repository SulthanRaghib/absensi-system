<!DOCTYPE html>
<html lang="id">

<head>
    <x-head title="Sistem Absensi">
        <script>
            // Mirror Filament's dark mode logic — same key ('theme') and same behavior
            (function() {
                var theme = localStorage.getItem('theme') || 'system';
                if (
                    theme === 'dark' ||
                    (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches)
                ) {
                    document.documentElement.classList.add('dark');
                }
            })();
        </script>
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                darkMode: 'class',
            }
        </script>
        <!-- Leaflet CSS & JS -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>

        <style>
            body {
                font-family: 'Inter', sans-serif;
            }

            .glass-card {
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255, 255, 255, 0.2);
            }

            .dark .glass-card {
                background: rgba(17, 24, 39, 0.95);
                border: 1px solid rgba(255, 255, 255, 0.08);
            }

            .btn-action {
                transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            }

            .btn-action:active {
                transform: scale(0.98);
            }

            .map-container {
                box-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.06);
            }
        </style>
    </x-head>
</head>

<body class="bg-gray-100 dark:bg-gray-950 text-gray-800 dark:text-gray-200 antialiased min-h-screen flex flex-col">

    <!-- Top Navigation Bar -->
    <nav
        class="bg-white/90 dark:bg-gray-900/90 backdrop-blur-md sticky top-0 z-40 border-b border-gray-200 dark:border-gray-800 px-4 py-3">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <a href="{{ route('dashboard') }}"
                class="p-2 -ml-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-600 dark:text-gray-400 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <span class="font-semibold text-gray-900 dark:text-white">Absensi</span>
            <div class="w-10"></div> <!-- Spacer for centering -->
        </div>
    </nav>

    <main class="flex-grow px-4 py-6 max-w-7xl mx-auto w-full">

        <div class="grid grid-cols-1 md:grid-cols-12 gap-6">

            <!-- Left Column: Profile, Status, Actions -->
            <div class="md:col-span-5 lg:col-span-4 space-y-6">

                <!-- Profile Section -->
                <div
                    class="flex items-center space-x-4 bg-white dark:bg-gray-900 p-4 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-800 md:bg-transparent md:dark:bg-transparent md:shadow-none md:border-0 md:p-0">
                    <div class="relative">
                        <div
                            class="w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white text-2xl font-bold shadow-lg shadow-blue-500/30">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <div
                            class="absolute -bottom-1 -right-1 w-5 h-5 bg-green-500 border-2 border-white dark:border-gray-900 rounded-full">
                        </div>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900 dark:text-white leading-tight">{{ $user->name }}
                        </h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">{{ $user->email }}</p>
                        <p
                            class="text-xs text-blue-600 dark:text-blue-400 font-semibold mt-1 bg-blue-50 dark:bg-blue-900/30 inline-block px-2 py-0.5 rounded-md">
                            {{ now()->isoFormat('dddd, D MMMM Y') }}
                        </p>
                    </div>
                </div>

                <!-- Attendance Status Card -->
                <div
                    class="bg-white dark:bg-gray-900 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-800 p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Status Hari Ini</h2>
                        @if ($todayAbsence?->jam_masuk && $todayAbsence?->jam_pulang)
                            <span
                                class="px-2 py-1 bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-400 text-xs font-bold rounded-full">Selesai</span>
                        @elseif($todayAbsence?->jam_masuk)
                            <span
                                class="px-2 py-1 bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-400 text-xs font-bold rounded-full">Bekerja</span>
                        @else
                            class="px-2 py-1 bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 text-xs font-bold rounded-full">Belum
                            Absen</span>
                        @endif
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <!-- Check In Time -->
                        <div
                            class="bg-green-50/50 dark:bg-green-900/20 rounded-2xl p-4 border border-green-100 dark:border-green-900/50 flex flex-col items-center justify-center text-center">
                            <div
                                class="w-8 h-8 rounded-full bg-green-100 dark:bg-green-900/40 text-green-600 dark:text-green-400 flex items-center justify-center mb-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                </svg>
                            </div>
                            <span class="text-xs text-gray-500 dark:text-gray-400 font-medium mb-0.5">Jam Masuk</span>
                            <span id="jam-masuk-display"
                                class="text-xl font-bold text-gray-900 dark:text-white tracking-tight">
                                {{ $todayAbsence?->jam_masuk ? $todayAbsence->jam_masuk->format('H:i') : '--:--' }}
                            </span>
                        </div>

                        <!-- Check Out Time -->
                        <div
                            class="bg-orange-50/50 dark:bg-orange-900/20 rounded-2xl p-4 border border-orange-100 dark:border-orange-900/50 flex flex-col items-center justify-center text-center">
                            <div
                                class="w-8 h-8 rounded-full bg-orange-100 dark:bg-orange-900/40 text-orange-600 dark:text-orange-400 flex items-center justify-center mb-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                            </div>
                            <span class="text-xs text-gray-500 dark:text-gray-400 font-medium mb-0.5">Jam Pulang</span>
                            <span id="jam-pulang-display"
                                class="text-xl font-bold text-gray-900 dark:text-white tracking-tight">
                                {{ $todayAbsence?->jam_pulang ? $todayAbsence->jam_pulang->format('H:i') : '--:--' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="grid grid-cols-2 gap-4">
                    <button id="btn-check-in"
                        class="btn-action group relative w-full flex flex-col items-center justify-center p-4 rounded-2xl bg-gray-900 dark:bg-gray-100 text-white dark:text-gray-900 shadow-lg shadow-gray-900/20 dark:shadow-gray-900/40 disabled:opacity-50 disabled:cursor-not-allowed disabled:shadow-none overflow-hidden"
                        {{ $todayAbsence?->jam_masuk ? 'disabled' : '' }}>
                        <div class="absolute inset-0 bg-white/10 opacity-0 group-hover:opacity-100 transition-opacity">
                        </div>
                        <div
                            class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center mb-2 group-disabled:bg-white/10">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                            </svg>
                        </div>
                        <span class="font-semibold">Absen Masuk</span>
                    </button>

                    <button id="btn-check-out"
                        class="btn-action group relative w-full flex flex-col items-center justify-center p-4 rounded-2xl bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 border border-gray-200 dark:border-gray-700 shadow-sm hover:border-gray-300 dark:hover:border-gray-600 disabled:opacity-50 disabled:cursor-not-allowed disabled:bg-gray-50 dark:disabled:bg-gray-900"
                        {{ !$todayAbsence?->jam_masuk || $todayAbsence?->jam_pulang ? 'disabled' : '' }}>
                        <div
                            class="w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center mb-2 group-disabled:bg-gray-200 dark:group-disabled:bg-gray-800">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600 dark:text-gray-300"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </div>
                        <span class="font-semibold">Absen Pulang</span>
                    </button>
                </div>
            </div>

            <!-- Right Column: Map -->
            <div class="md:col-span-7 lg:col-span-8 h-full">
                <div
                    class="bg-white dark:bg-gray-900 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-800 overflow-hidden h-full flex flex-col">
                    <div class="p-5 pb-0 flex justify-between items-center shrink-0">
                        <h2 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Lokasi Anda</h2>
                        <button id="btn-get-location"
                            class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 flex items-center space-x-1 bg-blue-50 dark:bg-blue-900/30 px-3 py-1.5 rounded-full transition-colors">
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
                                <span id="lat-display"
                                    class="font-mono font-medium text-gray-700 dark:text-gray-300">-</span>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-xs text-gray-400 dark:text-gray-500">Longitude</span>
                                <span id="lng-display"
                                    class="font-mono font-medium text-gray-700 dark:text-gray-300">-</span>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-xs text-gray-400 dark:text-gray-500">Akurasi</span>
                                <span id="accuracy-display"
                                    class="font-mono font-medium text-gray-700 dark:text-gray-300">-</span>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-xs text-gray-400 dark:text-gray-500">Jarak Kantor</span>
                                <span id="distance-display"
                                    class="font-mono font-medium text-gray-700 dark:text-gray-300">-</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alert Container -->
        <div id="alert-container" class="fixed bottom-6 left-4 right-4 z-50 flex flex-col gap-2 pointer-events-none">
        </div>

    </main>

    <!-- Location Permission Modal -->
    <div id="location-modal"
        class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4 opacity-0 transition-opacity duration-300">
        <div class="bg-white dark:bg-gray-900 rounded-3xl shadow-2xl max-w-sm w-full p-6 transform scale-95 transition-transform duration-300"
            id="modal-content">
            <div
                class="w-16 h-16 bg-blue-50 dark:bg-blue-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>
            <h3 class="text-xl font-bold text-center text-gray-900 dark:text-white mb-2">Izin Lokasi Diperlukan</h3>
            <p class="text-gray-500 dark:text-gray-400 text-center text-sm mb-6 leading-relaxed">
                Untuk memastikan validitas absensi, kami perlu mengakses lokasi Anda saat ini. Data lokasi hanya diambil
                saat Anda melakukan check-in/out.
            </p>
            <div class="space-y-3">
                <button id="btn-confirm-location"
                    class="w-full py-3.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl shadow-lg shadow-blue-600/30 transition-all active:scale-95">
                    Izinkan Akses Lokasi
                </button>
                <button id="btn-cancel-location"
                    class="w-full py-3.5 bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-semibold rounded-xl transition-colors">
                    Nanti Saja
                </button>
            </div>
        </div>
    </div>

    <script>
        // Configuration
        const OFFICE_LAT = {{ $officeLocation['latitude'] }};
        const OFFICE_LNG = {{ $officeLocation['longitude'] }};
        const OFFICE_RADIUS = {{ $officeLocation['radius'] }};
        const MAX_ACCURACY = 2500;

        // State
        let map, userMarker, officeCircle;
        let userLocation = null;

        // Initialize Map
        function initMap() {
            map = L.map('map', {
                zoomControl: false,
                attributionControl: false
            }).setView([OFFICE_LAT, OFFICE_LNG], 15);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

            // Office marker
            const officeIcon = L.divIcon({
                className: 'custom-div-icon',
                html: `<div style="background-color: #EF4444; width: 12px; height: 12px; border-radius: 50%; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3);"></div>`,
                iconSize: [12, 12],
                iconAnchor: [6, 6]
            });

            L.marker([OFFICE_LAT, OFFICE_LNG], {
                icon: officeIcon
            }).addTo(map);

            // Office radius circle
            officeCircle = L.circle([OFFICE_LAT, OFFICE_LNG], {
                color: '#3B82F6',
                fillColor: '#3B82F6',
                fillOpacity: 0.1,
                weight: 1,
                radius: OFFICE_RADIUS
            }).addTo(map);
        }

        // Show Alert (Toast Style)
        function showAlert(message, type = 'info') {
            const colors = {
                success: 'bg-green-500 text-white',
                error: 'bg-red-500 text-white',
                warning: 'bg-yellow-500 text-white',
                info: 'bg-gray-800 text-white'
            };

            const alert = document.createElement('div');
            alert.className =
                `${colors[type]} px-4 py-3 rounded-xl shadow-lg text-sm font-medium transform transition-all duration-300 translate-y-10 opacity-0 pointer-events-auto flex items-center justify-between`;
            alert.innerHTML = `<span>${message}</span>`;

            const container = document.getElementById('alert-container');
            container.appendChild(alert);

            // Animate in
            requestAnimationFrame(() => {
                alert.classList.remove('translate-y-10', 'opacity-0');
            });

            // Remove after 4s
            setTimeout(() => {
                alert.classList.add('translate-y-10', 'opacity-0');
                setTimeout(() => alert.remove(), 300);
            }, 4000);
        }

        // Calculate Distance (Haversine)
        function calculateDistance(lat1, lon1, lat2, lon2) {
            const R = 6371000;
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

        // Modal Logic
        const locationModal = document.getElementById('location-modal');
        const modalContent = document.getElementById('modal-content');
        const btnGetLocation = document.getElementById('btn-get-location');
        const btnConfirmLocation = document.getElementById('btn-confirm-location');
        const btnCancelLocation = document.getElementById('btn-cancel-location');

        function toggleModal(show) {
            if (show) {
                locationModal.classList.remove('hidden');
                // Small delay to allow display:block to apply before opacity transition
                setTimeout(() => {
                    locationModal.classList.remove('opacity-0');
                    modalContent.classList.remove('scale-95');
                    modalContent.classList.add('scale-100');
                }, 10);
            } else {
                locationModal.classList.add('opacity-0');
                modalContent.classList.remove('scale-100');
                modalContent.classList.add('scale-95');
                setTimeout(() => {
                    locationModal.classList.add('hidden');
                }, 300);
            }
        }

        btnGetLocation.addEventListener('click', () => toggleModal(true));
        btnCancelLocation.addEventListener('click', () => toggleModal(false));

        btnConfirmLocation.addEventListener('click', function() {
            toggleModal(false);
            getLocation();
        });

        let watchId = null;

        function getLocation() {
            if (!navigator.geolocation) {
                showAlert('Browser tidak mendukung GPS!', 'error');
                return;
            }

            // Check for secure context (HTTPS)
            if (window.location.protocol !== 'https:' &&
                window.location.hostname !== 'localhost' &&
                window.location.hostname !== '127.0.0.1') {
                showAlert('Akses lokasi memerlukan koneksi HTTPS (aman). Silakan gunakan HTTPS atau localhost.', 'error');
                return;
            }

            const originalBtnText = btnGetLocation.innerHTML;
            btnGetLocation.innerHTML = `
                <svg class="animate-spin h-3 w-3 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Mencari...</span>
            `;
            btnGetLocation.disabled = true;

            if (watchId) navigator.geolocation.clearWatch(watchId);

            const updateFromPosition = (position) => {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                const accuracy = position.coords.accuracy;

                // Update UI
                document.getElementById('lat-display').textContent = lat.toFixed(5);
                document.getElementById('lng-display').textContent = lng.toFixed(5);
                document.getElementById('accuracy-display').textContent = `±${accuracy.toFixed(0)}m`;

                const distance = calculateDistance(lat, lng, OFFICE_LAT, OFFICE_LNG);
                document.getElementById('distance-display').textContent = `${distance.toFixed(0)}m`;

                // Update Map
                if (userMarker) map.removeLayer(userMarker);

                const userIcon = L.divIcon({
                    className: 'custom-div-icon',
                    html: `<div class="relative">
                            <div class="w-4 h-4 bg-blue-500 rounded-full border-2 border-white shadow-md"></div>
                            <div class="absolute -inset-2 bg-blue-500/20 rounded-full animate-ping"></div>
                           </div>`,
                    iconSize: [16, 16],
                    iconAnchor: [8, 8]
                });

                userMarker = L.marker([lat, lng], {
                    icon: userIcon
                }).addTo(map);

                // Fit bounds to show both office and user
                const bounds = L.latLngBounds([
                    [OFFICE_LAT, OFFICE_LNG],
                    [lat, lng]
                ]);
                map.fitBounds(bounds, {
                    padding: [50, 50]
                });

                // Validate accuracy
                if (accuracy > MAX_ACCURACY) {
                    showAlert(`Akurasi GPS rendah (${accuracy.toFixed(0)}m). Menunggu sinyal lebih baik...`, 'warning');
                    btnGetLocation.innerHTML = `
                        <svg class="animate-spin h-3 w-3 text-yellow-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Akurasi: ${accuracy.toFixed(0)}m</span>
                    `;
                    return false;
                }

                userLocation = {
                    lat,
                    lng,
                    accuracy,
                    distance
                };

                if (distance <= OFFICE_RADIUS) {
                    showAlert('Lokasi terverifikasi! Anda berada di area kantor.', 'success');
                } else {
                    showAlert(`Anda berada di luar jangkauan (${distance.toFixed(0)}m).`, 'error');
                }

                btnGetLocation.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Lokasi OK</span>
                `;
                btnGetLocation.disabled = false;

                return true;
            };

            const handleError = (error) => {
                console.error('Geolocation error:', error);
                let message = 'Gagal mengambil lokasi.';
                if (error.code === 1) {
                    message = 'Izin lokasi ditolak. Pastikan GPS aktif dan izin browser diberikan.';
                    if (window.location.protocol !== 'https:' && window.location.hostname !== 'localhost') {
                        message += ' (Wajib HTTPS)';
                    }
                }
                if (error.code === 2) message = 'Sinyal GPS tidak ditemukan. Pastikan GPS aktif.';
                if (error.code === 3) message = 'Waktu permintaan habis. Coba lagi.';

                showAlert(message, 'error');
                btnGetLocation.innerHTML = `<span>Ambil Lokasi</span>`;
                btnGetLocation.disabled = false;

                if (watchId) navigator.geolocation.clearWatch(watchId);
                watchId = null;
            };

            // Fast path: try cached / low-power location first (reduces TIMEOUT frequency)
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const accepted = updateFromPosition(position);
                    if (accepted) {
                        if (watchId) navigator.geolocation.clearWatch(watchId);
                        watchId = null;
                    } else {
                        // If accuracy still poor, start high accuracy watch below.
                        startHighAccuracyWatch();
                    }
                },
                () => {
                    // Ignore and fallback to high accuracy watch.
                    startHighAccuracyWatch();
                }, {
                    enableHighAccuracy: false,
                    timeout: 8000,
                    maximumAge: 60000
                }
            );

            function startHighAccuracyWatch() {
                if (watchId) navigator.geolocation.clearWatch(watchId);

                watchId = navigator.geolocation.watchPosition(
                    (position) => {
                        const accepted = updateFromPosition(position);
                        if (accepted) {
                            navigator.geolocation.clearWatch(watchId);
                            watchId = null;
                        }
                    },
                    (error) => {
                        // With longer timeout, TIMEOUT should be rarer; handle normally.
                        handleError(error);
                    }, {
                        enableHighAccuracy: true,
                        timeout: 60000,
                        maximumAge: 10000
                    }
                );
            }
        }

        // Pending action when requesting location
        let pendingAction = null;

        async function performAttendance(action, payload) {
            const isCheckIn = action === 'in';
            const url = isCheckIn ? '{{ route('absensi.check-in') }}' : '{{ route('absensi.check-out') }}';
            const btn = isCheckIn ? document.getElementById('btn-check-in') : document.getElementById('btn-check-out');
            const originalContent = btn.innerHTML;

            btn.disabled = true;
            btn.innerHTML = isCheckIn ?
                `<svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle></svg>` :
                `<svg class="animate-spin h-5 w-5 text-gray-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle></svg>`;

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(payload)
                });

                const data = await response.json();

                if (data.success) {
                    showAlert(isCheckIn ? 'Berhasil Check-in!' : 'Berhasil Check-out!', 'success');
                    if (isCheckIn) document.getElementById('jam-masuk-display').textContent = data.data.jam_masuk;
                    else document.getElementById('jam-pulang-display').textContent = data.data.jam_pulang;
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showAlert(data.message, 'error');
                    btn.innerHTML = originalContent;
                    btn.disabled = false;
                }
            } catch (error) {
                showAlert('Terjadi kesalahan koneksi.', 'error');
                btn.innerHTML = originalContent;
                btn.disabled = false;
            }
        }

        document.getElementById('btn-check-in').addEventListener('click', function() {
            if (!userLocation) {
                pendingAction = 'in';
                toggleModal(true);
                return;
            }

            performAttendance('in', {
                latitude: userLocation.lat,
                longitude: userLocation.lng,
                accuracy: userLocation.accuracy
            });
        });

        // Check Out Action
        document.getElementById('btn-check-out').addEventListener('click', async function() {
            if (!userLocation) {
                toggleModal(true);
                return;
            }

            const btn = this;
            const originalContent = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML =
                `<svg class="animate-spin h-5 w-5 text-gray-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>`;

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
                    showAlert('Berhasil Check-out!', 'success');
                    document.getElementById('jam-pulang-display').textContent = data.data.jam_pulang;
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showAlert(data.message, 'error');
                    btn.innerHTML = originalContent;
                    btn.disabled = false;
                }
            } catch (error) {
                showAlert('Terjadi kesalahan koneksi.', 'error');
                btn.innerHTML = originalContent;
                btn.disabled = false;
            }
        });

        // Initialize
        document.addEventListener('DOMContentLoaded', initMap);
    </script>
</body>

</html>
