<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('attendance', () => ({
            openDirect: false,
            latitude: null,
            longitude: null,
            accuracy: null,
            locationError: null,
            map: null,
            userMarker: null,
            watchId: null,

            // Form & State
            email: '',
            password: '',
            isLoading: false,
            showConfirm: false,
            confirmMessage: '',
            status: '',
            deviceToken: null,
            userAvatar: null,

            // Face Recognition State
            faceRecognitionEnabled: {{ $faceRecognitionEnabled ? 'true' : 'false' }},
            showFaceModal: false,
            isFaceLoading: false,
            isModelLoaded: false,
            faceStatus: 'Memuat Model...',
            faceMessage: 'Posisikan wajah Anda di tengah kamera',
            capturedImage: null,
            videoStream: null,

            init() {
                // Check for rotated device ID from session (Direct Attendance)
                @if (session('new_device_id'))
                    const newDeviceId = "{{ session('new_device_id') }}";
                    console.log('Updating Device ID from Session:', newDeviceId);
                    localStorage.setItem('device_token', newDeviceId);
                @endif

                // Device Binding Logic
                let token = localStorage.getItem('device_token');
                if (!token) {
                    token = crypto.randomUUID();
                    localStorage.setItem('device_token', token);
                }
                this.deviceToken = token;

                this.$watch('openDirect', value => {
                    if (value) {
                        this.$nextTick(() => {
                            this.initMap();
                            this.getLocation();
                            if (this.faceRecognitionEnabled) {
                                this.loadFaceModels();
                            }
                        });
                    } else {
                        // Reset state when closed
                        this.showConfirm = false;
                        this.email = '';
                        this.password = '';
                        if (this.watchId) {
                            navigator.geolocation.clearWatch(this.watchId);
                            this.watchId = null;
                        }
                        this.closeFaceModal();
                    }
                });
            },

            async loadFaceModels() {
                try {
                    this.faceStatus = 'Memuat Model Wajah...';
                    const MODEL_URL = 'https://justadudewhohacks.github.io/face-api.js/models';

                    await Promise.all([
                        faceapi.nets.ssdMobilenetv1.loadFromUri(MODEL_URL),
                        faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
                        faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL)
                    ]);

                    this.isModelLoaded = true;
                    console.log('Face API Models Loaded');
                } catch (error) {
                    console.error('Error loading face models:', error);
                    alert('Gagal memuat sistem pengenalan wajah. Cek koneksi internet.');
                }
            },

            async openFaceModal() {
                this.showFaceModal = true;
                this.isFaceLoading = true;
                this.faceStatus = 'Menyiapkan Kamera...';
                this.faceMessage = 'Posisikan wajah Anda di tengah kamera';

                try {
                    const stream = await navigator.mediaDevices.getUserMedia({
                        video: {}
                    });
                    this.videoStream = stream;
                    this.$refs.video.srcObject = stream;
                    this.isFaceLoading = false;
                } catch (err) {
                    console.error(err);
                    alert('Gagal mengakses kamera. Pastikan izin kamera diberikan.');
                    this.closeFaceModal();
                }
            },

            closeFaceModal() {
                this.showFaceModal = false;
                if (this.videoStream) {
                    this.videoStream.getTracks().forEach(track => track.stop());
                    this.videoStream = null;
                }
            },

            async captureAndVerify() {
                if (!this.isModelLoaded) return;

                this.isFaceLoading = true;
                this.faceStatus = 'Memverifikasi Wajah...';

                const video = this.$refs.video;

                // 1. Detect Face from Camera
                const detection = await faceapi.detectSingleFace(video).withFaceLandmarks()
                    .withFaceDescriptor();

                if (!detection) {
                    this.isFaceLoading = false;
                    this.faceMessage = 'Wajah tidak terdeteksi! Pastikan pencahayaan cukup.';
                    return;
                }

                // 2. Load User Avatar and Compute Descriptor
                try {
                    const img = await faceapi.fetchImage(this.userAvatar);
                    const avatarDetection = await faceapi.detectSingleFace(img)
                        .withFaceLandmarks().withFaceDescriptor();

                    if (!avatarDetection) {
                        this.isFaceLoading = false;
                        alert(
                            'Foto profil Anda tidak valid (wajah tidak terdeteksi). Harap ganti foto profil.'
                        );
                        this.closeFaceModal();
                        return;
                    }

                    // 3. Compare Descriptors
                    const distance = faceapi.euclideanDistance(detection.descriptor,
                        avatarDetection.descriptor);
                    const threshold = 0.6;

                    console.log('Face Distance:', distance);

                    if (distance < threshold) {
                        // Match!
                        const canvas = this.$refs.canvas;
                        canvas.width = video.videoWidth;
                        canvas.height = video.videoHeight;
                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                        this.capturedImage = canvas.toDataURL('image/png');
                        console.log('Image captured, submitting form...');

                        this.closeFaceModal();

                        // Wait for Alpine to update the hidden input value
                        this.$nextTick(() => {
                            this.$refs.attendanceForm.submit();
                        });
                    } else {
                        this.isFaceLoading = false;
                        this.faceMessage = 'Wajah tidak cocok dengan foto profil!';
                    }

                } catch (error) {
                    console.error(error);
                    this.isFaceLoading = false;
                    alert('Terjadi kesalahan saat verifikasi wajah.');
                }
            },

            async checkAttendance() {
                this.isLoading = true;
                try {
                    const response = await fetch("{{ route('attendance.check-status') }}", {
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
                        this.userAvatar = data.avatar_url;

                        if (!this.userAvatar) {
                            alert(
                                'Anda belum mengatur foto profil! Wajib menggunakan foto profil untuk melakukan Check-out.'
                            );
                            this.isLoading = false;
                            return;
                        }

                        this.showConfirm = true;
                        this.confirmMessage = data.message;
                        this.status = 'check-out';
                    } else if (data.status === 'completed') {
                        alert(data.message);
                    } else {
                        // check-in
                        this.userAvatar = data.avatar_url;

                        if (this.faceRecognitionEnabled) {
                            if (!this.userAvatar) {
                                alert(
                                    'Anda belum mengatur foto profil! Harap hubungi admin atau login ke dashboard untuk mengatur foto profil.'
                                );
                                this.isLoading = false;
                                return;
                            }
                            this.openFaceModal();
                        } else {
                            this.$refs.attendanceForm.submit();
                        }
                    }
                } catch (error) {
                    console.error(error);
                    alert('Terjadi kesalahan koneksi');
                } finally {
                    // Only stop loading if we are NOT proceeding to face modal or submit
                    // If we open face modal, we keep loading? No, openFaceModal handles its own loading state.
                    // But the main button should probably stop loading.
                    if (!this.showFaceModal) {
                        this.isLoading = false;
                    }
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
                if (!navigator.geolocation) {
                    this.locationError = 'Geolocation tidak didukung oleh browser ini.';
                    return;
                }

                if (this.watchId) {
                    navigator.geolocation.clearWatch(this.watchId);
                    this.watchId = null;
                }

                const onPosition = (position) => {
                    this.latitude = position.coords.latitude;
                    this.longitude = position.coords.longitude;
                    this.accuracy = position.coords.accuracy;
                    this.locationError = null;
                    this.updateUserLocation(position.coords);
                };

                const onError = (error) => {
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
                            this.locationError = 'Terjadi kesalahan yang tidak diketahui.';
                    }
                };

                // Fast path: attempt cached / low-power fix
                navigator.geolocation.getCurrentPosition(
                    (pos) => onPosition(pos),
                    () => {}, {
                        enableHighAccuracy: false,
                        timeout: 8000,
                        maximumAge: 60000
                    }
                );

                // Keep updating with high accuracy, but allow caching and longer timeout to reduce TIMEOUT errors
                this.watchId = navigator.geolocation.watchPosition(
                    (pos) => onPosition(pos),
                    (err) => onError(err), {
                        enableHighAccuracy: true,
                        timeout: 60000,
                        maximumAge: 10000
                    }
                );
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

            async submitLogin() {
                this.errorMessage = '';
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
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            email: this.email,
                            password: this.password,
                            remember: this.remember
                        })
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        window.location.href = data.redirect;
                    } else {
                        this.errorMessage = data.message || 'Terjadi kesalahan saat login.';
                        if (data.errors && data.errors.email) {
                            this.errorMessage = data.errors.email[0];
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
