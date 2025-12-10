<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('attendance', () => ({
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
                    const response = await fetch("{{ route('attendance.check-status') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector(
                                'meta[name="csrf-token"]').getAttribute('content')
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

                L.marker([OFFICE_LAT, OFFICE_LNG], {
                    icon: officeIcon
                }).addTo(this.map);

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
                                    this.locationError =
                                        'Izin lokasi ditolak. Mohon aktifkan izin lokasi.';
                                    break;
                                case error.POSITION_UNAVAILABLE:
                                    this.locationError = 'Informasi lokasi tidak tersedia.';
                                    break;
                                case error.TIMEOUT:
                                    this.locationError = 'Waktu permintaan lokasi habis.';
                                    break;
                                default:
                                    this.locationError =
                                        'Terjadi kesalahan yang tidak diketahui.';
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
                    this.userMarker = L.marker([lat, lng], {
                        icon: userIcon
                    }).addTo(this.map);
                }

                this.map.setView([lat, lng], 16);

                const dist = this.calculateDistance(lat, lng, {{ $officeLocation['latitude'] }},
                    {{ $officeLocation['longitude'] }});

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
        }));
    });

    function loginForm() {
        return {
            email: '',
            password: '',
            showPassword: false,
            remember: false,
            isLoading: false,
            errorMessage: '',
            fraudError: '',

            async submitLogin() {
                this.errorMessage = '';
                this.fraudError = '';
                this.isLoading = true;

                // Simulate small delay for UX consistency
                await new Promise(resolve => setTimeout(resolve, 500));

                if (!this.email || !this.password) {
                    this.errorMessage = 'Email dan Password wajib diisi.';
                    this.isLoading = false;
                    return;
                }

                try {
                    const response = await fetch("{{ route('login.perform') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content')
                        },
                        body: JSON.stringify({
                            email: this.email,
                            password: this.password,
                            remember: this.remember
                        })
                    });

                    const data = await response.json();

                    // Update CSRF token if provided in response
                    if (data.csrf_token) {
                        document.querySelector('meta[name="csrf-token"]').setAttribute('content', data.csrf_token);
                    }

                    if (response.ok && data.success) {
                        window.location.href = data.redirect;
                    } else {
                        if (data.errors && data.errors.fraud_alert) {
                            this.fraudError = data.errors.fraud_alert[0];
                        } else {
                            this.errorMessage = data.message || 'Terjadi kesalahan saat login.';
                            if (data.errors && data.errors.email) {
                                this.errorMessage = data.errors.email[0];
                            }
                        }
                    }
                } catch (error) {
                    this.errorMessage = 'Terjadi kesalahan jaringan. Silakan coba lagi.';
                    console.error(error);
                } finally {
                    this.isLoading = false;
                }
            }
        }
    }
</script>
