<div>
    <x-filament-panels::page>
        <!-- Leaflet CSS & JS -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>

        <style>
            .glass-card {
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255, 255, 255, 0.2);
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

        <div x-data="absensiMapData({
            officeLat: {{ $officeLocation['latitude'] }},
            officeLng: {{ $officeLocation['longitude'] }},
            officeRadius: {{ $officeLocation['radius'] }},
            checkInRoute: '{{ route('absensi.check-in') }}',
            checkOutRoute: '{{ route('absensi.check-out') }}',
            csrfToken: '{{ csrf_token() }}'
        })" class="grid grid-cols-1 md:grid-cols-12 gap-6">

            <!-- Left Column: Profile, Status, Actions -->
            <div class="md:col-span-5 lg:col-span-4 space-y-6">

                <!-- Profile Section -->
                <div
                    class="flex items-center space-x-4 bg-white p-4 rounded-3xl shadow-sm border border-gray-100 md:bg-transparent md:shadow-none md:border-0 md:p-0">
                    <div class="relative">
                        <div
                            class="w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white text-2xl font-bold shadow-lg shadow-blue-500/30">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <div class="absolute -bottom-1 -right-1 w-5 h-5 bg-green-500 border-2 border-white rounded-full">
                        </div>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900 leading-tight">{{ $user->name }}</h1>
                        <p class="text-sm text-gray-500 font-medium">{{ $user->email }}</p>
                        <p
                            class="text-xs text-blue-600 font-semibold mt-1 bg-blue-50 inline-block px-2 py-0.5 rounded-md">
                            {{ now()->isoFormat('dddd, D MMMM Y') }}
                        </p>
                    </div>
                </div>

                <!-- Attendance Status Card -->
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Status Hari Ini</h2>
                        @if ($todayAbsence?->jam_masuk && $todayAbsence?->jam_pulang)
                            <span
                                class="px-2 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full">Selesai</span>
                        @elseif($todayAbsence?->jam_masuk)
                            <span
                                class="px-2 py-1 bg-blue-100 text-blue-700 text-xs font-bold rounded-full">Bekerja</span>
                        @else
                            <span class="px-2 py-1 bg-gray-100 text-gray-600 text-xs font-bold rounded-full">Belum
                                Absen</span>
                        @endif
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <!-- Check In Time -->
                        <div
                            class="bg-green-50/50 rounded-2xl p-4 border border-green-100 flex flex-col items-center justify-center text-center">
                            <div
                                class="w-8 h-8 rounded-full bg-green-100 text-green-600 flex items-center justify-center mb-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                </svg>
                            </div>
                            <span class="text-xs text-gray-500 font-medium mb-0.5">Jam Masuk</span>
                            <span x-ref="jamMasukDisplay" class="text-xl font-bold text-gray-900 tracking-tight">
                                {{ $todayAbsence?->jam_masuk ? $todayAbsence->jam_masuk->format('H:i') : '--:--' }}
                            </span>
                        </div>

                        <!-- Check Out Time -->
                        <div
                            class="bg-orange-50/50 rounded-2xl p-4 border border-orange-100 flex flex-col items-center justify-center text-center">
                            <div
                                class="w-8 h-8 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center mb-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                            </div>
                            <span class="text-xs text-gray-500 font-medium mb-0.5">Jam Pulang</span>
                            <span x-ref="jamPulangDisplay" class="text-xl font-bold text-gray-900 tracking-tight">
                                {{ $todayAbsence?->jam_pulang ? $todayAbsence->jam_pulang->format('H:i') : '--:--' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="grid grid-cols-2 gap-4">
                    <button @click="performAttendance('in')"
                        class="btn-action group relative w-full flex flex-col items-center justify-center p-4 rounded-2xl bg-gray-900 text-white shadow-lg shadow-gray-900/20 disabled:opacity-50 disabled:cursor-not-allowed disabled:shadow-none overflow-hidden"
                        :disabled="!userLocation || isLoading || {{ $todayAbsence?->jam_masuk ? 'true' : 'false' }}">
                        <div class="absolute inset-0 bg-white/10 opacity-0 group-hover:opacity-100 transition-opacity">
                        </div>
                        <div
                            class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center mb-2 group-disabled:bg-white/10">
                            <template x-if="isLoading && actionType === 'in'">
                                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                            </template>
                            <template x-if="!isLoading || actionType !== 'in'">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                </svg>
                            </template>
                        </div>
                        <span class="font-semibold">Absen Masuk</span>
                    </button>

                    <button @click="performAttendance('out')"
                        class="btn-action group relative w-full flex flex-col items-center justify-center p-4 rounded-2xl bg-white text-gray-900 border border-gray-200 shadow-sm hover:border-gray-300 disabled:opacity-50 disabled:cursor-not-allowed disabled:bg-gray-50"
                        :disabled="!userLocation || isLoading ||
                            {{ !$todayAbsence?->jam_masuk || $todayAbsence?->jam_pulang ? 'true' : 'false' }}">
                        <div
                            class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center mb-2 group-disabled:bg-gray-200">
                            <template x-if="isLoading && actionType === 'out'">
                                <svg class="animate-spin h-5 w-5 text-gray-600" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                            </template>
                            <template x-if="!isLoading || actionType !== 'out'">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                            </template>
                        </div>
                        <span class="font-semibold">Absen Pulang</span>
                    </button>
                </div>
            </div>

            <!-- Right Column: Map -->
            <div class="md:col-span-7 lg:col-span-8 h-full">
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden h-full flex flex-col">
                    <div class="p-5 pb-0 flex justify-between items-center shrink-0">
                        <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Lokasi Anda</h2>
                        <button @click="startTracking()"
                            class="text-xs font-semibold text-blue-600 hover:text-blue-700 flex items-center space-x-1 bg-blue-50 px-3 py-1.5 rounded-full transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            <span>Update Lokasi</span>
                        </button>
                    </div>

                    <div class="p-5 flex-grow flex flex-col">
                        <div id="map"
                            class="w-full rounded-2xl map-container bg-gray-100 mb-4 z-0 flex-grow min-h-[300px] md:min-h-[400px]">
                        </div>

                        <div class="grid grid-cols-2 md:grid-cols-4 gap-y-3 gap-x-4 text-sm shrink-0">
                            <div class="flex flex-col">
                                <span class="text-xs text-gray-400">Latitude</span>
                                <span x-text="userLocation ? userLocation.lat.toFixed(5) : '-'"
                                    class="font-mono font-medium text-gray-700"></span>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-xs text-gray-400">Longitude</span>
                                <span x-text="userLocation ? userLocation.lng.toFixed(5) : '-'"
                                    class="font-mono font-medium text-gray-700"></span>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-xs text-gray-400">Akurasi</span>
                                <span x-text="userLocation ? '±' + userLocation.accuracy.toFixed(0) + 'm' : '-'"
                                    class="font-mono font-medium text-gray-700"></span>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-xs text-gray-400">Jarak Kantor</span>
                                <span x-text="userLocation ? userLocation.distance.toFixed(0) + 'm' : '-'"
                                    class="font-mono font-medium text-gray-700"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alert Container -->
        <div id="alert-container" class="fixed bottom-6 left-4 right-4 z-50 flex flex-col gap-2 pointer-events-none">
        </div>

        <script>
            function absensiMapData(config) {
                return {
                    map: null,
                    userMarker: null,
                    officeCircle: null,
                    userLocation: null,
                    watchId: null,
                    isLoading: false,
                    actionType: null,

                    init() {
                        this.$nextTick(() => {
                            this.initMap();
                            this.startTracking();
                        });
                    },

                    initMap() {
                        if (this.map) return;

                        this.map = L.map('map', {
                            zoomControl: false,
                            attributionControl: false
                        }).setView([config.officeLat, config.officeLng], 15);

                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(this.map);

                        // Office marker
                        const officeIcon = L.divIcon({
                            className: 'custom-div-icon',
                            html: `<div style="background-color: #EF4444; width: 12px; height: 12px; border-radius: 50%; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3);"></div>`,
                            iconSize: [12, 12],
                            iconAnchor: [6, 6]
                        });

                        L.marker([config.officeLat, config.officeLng], {
                            icon: officeIcon
                        }).addTo(this.map);

                        // Office radius circle
                        this.officeCircle = L.circle([config.officeLat, config.officeLng], {
                            color: '#3B82F6',
                            fillColor: '#3B82F6',
                            fillOpacity: 0.1,
                            weight: 1,
                            radius: config.officeRadius
                        }).addTo(this.map);
                    },

                    startTracking() {
                        if (!navigator.geolocation) {
                            this.showAlert('Browser tidak mendukung GPS!', 'error');
                            return;
                        }

                        if (this.watchId) {
                            navigator.geolocation.clearWatch(this.watchId);
                        }

                        this.watchId = navigator.geolocation.watchPosition(
                            (position) => {
                                const lat = position.coords.latitude;
                                const lng = position.coords.longitude;
                                const accuracy = position.coords.accuracy;
                                const distance = this.calculateDistance(lat, lng, config.officeLat, config.officeLng);

                                this.userLocation = {
                                    lat,
                                    lng,
                                    accuracy,
                                    distance
                                };
                                this.updateMapMarker(lat, lng);
                            },
                            (error) => {
                                console.error('Geolocation error:', error);
                                let message = 'Gagal mengambil lokasi.';
                                if (error.code === 1) message = 'Izin lokasi ditolak.';
                                if (error.code === 2) message = 'Sinyal GPS tidak ditemukan.';
                                if (error.code === 3) message = 'Waktu permintaan habis.';
                                this.showAlert(message, 'error');
                            }, {
                                enableHighAccuracy: true,
                                timeout: 10000,
                                maximumAge: 0
                            }
                        );
                    },

                    updateMapMarker(lat, lng) {
                        if (this.userMarker) {
                            this.userMarker.setLatLng([lat, lng]);
                        } else {
                            const userIcon = L.divIcon({
                                className: 'custom-div-icon',
                                html: `<div class="relative">
                                        <div class="w-4 h-4 bg-blue-500 rounded-full border-2 border-white shadow-md"></div>
                                        <div class="absolute -inset-2 bg-blue-500/20 rounded-full animate-ping"></div>
                                       </div>`,
                                iconSize: [16, 16],
                                iconAnchor: [8, 8]
                            });

                            this.userMarker = L.marker([lat, lng], {
                                icon: userIcon
                            }).addTo(this.map);
                        }

                        // Only fit bounds if it's the first update or user is far away
                        // this.map.fitBounds(L.latLngBounds([[config.officeLat, config.officeLng], [lat, lng]]), { padding: [50, 50] });
                    },

                    calculateDistance(lat1, lon1, lat2, lon2) {
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
                    },

                    async performAttendance(action) {
                        if (!this.userLocation) {
                            this.showAlert('Lokasi belum ditemukan!', 'error');
                            return;
                        }

                        this.isLoading = true;
                        this.actionType = action;
                        const url = action === 'in' ? config.checkInRoute : config.checkOutRoute;

                        try {
                            const response = await fetch(url, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': config.csrfToken
                                },
                                body: JSON.stringify({
                                    latitude: this.userLocation.lat,
                                    longitude: this.userLocation.lng,
                                    accuracy: this.userLocation.accuracy
                                })
                            });

                            const data = await response.json();

                            if (data.success) {
                                this.showAlert(action === 'in' ? 'Berhasil Check-in!' : 'Berhasil Check-out!', 'success');
                                if (action === 'in') {
                                    this.$refs.jamMasukDisplay.textContent = data.data.jam_masuk;
                                } else {
                                    this.$refs.jamPulangDisplay.textContent = data.data.jam_pulang;
                                }
                                setTimeout(() => location.reload(), 1500);
                            } else {
                                this.showAlert(data.message, 'error');
                            }
                        } catch (error) {
                            this.showAlert('Terjadi kesalahan koneksi.', 'error');
                        } finally {
                            this.isLoading = false;
                            this.actionType = null;
                        }
                    },

                    showAlert(message, type = 'info') {
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

                        requestAnimationFrame(() => {
                            alert.classList.remove('translate-y-10', 'opacity-0');
                        });

                        setTimeout(() => {
                            alert.classList.add('translate-y-10', 'opacity-0');
                            setTimeout(() => alert.remove(), 300);
                        }, 4000);
                    }
                }
            }
        </script>
    </x-filament-panels::page>
</div>
