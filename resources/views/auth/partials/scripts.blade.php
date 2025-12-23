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
            faceThreshold: {{ (float) ($faceThreshold ?? 0.5) }},
            showFaceModal: false,
            isFaceLoading: false,
            isModelLoaded: false,
            faceStatus: 'scanning', // scanning, detecting, success, error
            faceMessage: 'Memulai kamera...',
            capturedImage: null,
            videoStream: null,

            // Auto-Scan State
            isScanning: false,
            isMatched: false,
            showRetry: false,
            scanInterval: null,
            scanTimeout: null,
            userDescriptor: null,

            // Hold-still stability (only for threshold 0.0)
            stabilityCounter: 0,
            stabilityTarget: 6, // e.g., 6 counts = 3 seconds (500ms interval)

            // Progress ring state (used when faceThreshold == 0.0)
            faceProgressPercent: 0,
            faceProgressDashArray: 0,
            faceProgressDashOffset: 0,

            initFaceProgressRing() {
                const r = 52;
                const c = 2 * Math.PI * r;
                this.faceProgressDashArray = c;
                this.faceProgressDashOffset = c;
            },

            updateFaceProgress() {
                const r = 52;
                const c = 2 * Math.PI * r;
                const p = Math.max(0, Math.min(1, this.stabilityCounter / this.stabilityTarget));
                this.faceProgressPercent = Math.round(p * 100);
                this.faceProgressDashOffset = c * (1 - p);
            },

            resetStability() {
                this.stabilityCounter = 0;
                this.updateFaceProgress();
            },

            clearFaceCanvas() {
                const canvas = this.$refs.canvas;
                if (!canvas) return;
                const ctx = canvas.getContext('2d');
                ctx.clearRect(0, 0, canvas.width || 0, canvas.height || 0);
            },

            drawFaceOverlay(video, detection, color = 'rgba(59,130,246,0.9)') {
                const canvas = this.$refs.canvas;
                if (!canvas || !video) return;

                const ctx = canvas.getContext('2d');
                canvas.width = video.videoWidth || 640;
                canvas.height = video.videoHeight || 480;

                ctx.clearRect(0, 0, canvas.width, canvas.height);

                // If no detection => draw a red frame to indicate "not detected"
                if (!detection?.detection?.box) {
                    ctx.lineWidth = Math.max(4, Math.round(Math.min(canvas.width, canvas.height) *
                        0.01));
                    ctx.strokeStyle = 'rgba(239,68,68,0.95)';
                    ctx.strokeRect(0, 0, canvas.width, canvas.height);
                    return;
                }

                const box = detection.detection.box;
                ctx.lineWidth = Math.max(3, Math.round(Math.min(canvas.width, canvas.height) *
                    0.006));
                ctx.strokeStyle = color;
                ctx.shadowColor = color;
                ctx.shadowBlur = 10;
                ctx.strokeRect(box.x, box.y, box.width, box.height);
                ctx.shadowBlur = 0;
            },

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
                    this.faceStatus = 'scanning';
                    this.faceMessage = 'Memuat Model Wajah...';

                    // Load models from /models
                    const MODEL_URL = '/models';

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

            async loadUserDescriptor() {
                if (!this.userAvatar) return;
                try {
                    // Use fetchImage to handle CORS if needed
                    const img = await faceapi.fetchImage(this.userAvatar);
                    const detection = await faceapi.detectSingleFace(img).withFaceLandmarks()
                        .withFaceDescriptor();

                    if (detection) {
                        this.userDescriptor = detection.descriptor;
                        console.log('User descriptor loaded');
                    } else {
                        console.warn('No face found in user avatar');
                        alert(
                            'Foto profil Anda tidak valid (wajah tidak terdeteksi). Harap ganti foto profil.'
                        );
                    }
                } catch (e) {
                    console.error('Error loading user avatar descriptor', e);
                    alert('Gagal memuat data wajah user.');
                }
            },

            async openFaceModal() {
                this.showFaceModal = true;
                this.showRetry = false;
                this.isMatched = false;
                this.faceStatus = 'scanning';
                this.faceMessage = 'Memulai kamera...';

                this.initFaceProgressRing();
                this.resetStability();

                await this.startCamera();
                this.startScanning();
            },

            isFaceCentered(detection, video) {
                try {
                    const box = detection?.detection?.box;
                    if (!box || !video?.videoWidth || !video?.videoHeight) return false;

                    const w = video.videoWidth;
                    const h = video.videoHeight;
                    const cx = box.x + box.width / 2;
                    const cy = box.y + box.height / 2;

                    const minX = w * 0.20;
                    const maxX = w * 0.80;
                    const minY = h * 0.20;
                    const maxY = h * 0.80;

                    const minFaceSize = Math.min(w, h) * 0.18;
                    const sizeOk = box.width >= minFaceSize && box.height >= minFaceSize;

                    return cx >= minX && cx <= maxX && cy >= minY && cy <= maxY && sizeOk;
                } catch (e) {
                    return false;
                }
            },

            async startCamera() {
                try {
                    const stream = await navigator.mediaDevices.getUserMedia({
                        video: {
                            facingMode: 'user'
                        }
                    });
                    this.videoStream = stream;
                    this.$refs.video.srcObject = stream;

                    // Wait for video to be ready
                    await new Promise(resolve => {
                        this.$refs.video.onloadedmetadata = () => {
                            this.$refs.video.play();
                            resolve();
                        };
                    });

                    this.isFaceLoading = false;
                } catch (err) {
                    console.error(err);
                    alert('Gagal mengakses kamera. Pastikan izin kamera diberikan.');
                    this.closeFaceModal();
                }
            },

            startScanning() {
                this.isScanning = true;
                this.faceStatus = 'scanning';
                this.faceMessage = 'Mencari wajah...';
                this.resetStability();

                // Timeout 30s
                if (this.scanTimeout) clearTimeout(this.scanTimeout);
                this.scanTimeout = setTimeout(() => {
                    this.stopScanning();
                    if (!this.isMatched) {
                        this.showRetry = true;
                        this.faceStatus = 'error';
                        this.faceMessage = 'Waktu habis';
                    }
                }, 30000);

                if (this.scanInterval) clearInterval(this.scanInterval);
                this.scanInterval = setInterval(async () => {
                    if (!this.videoStream || this.isMatched || !this.isModelLoaded || !
                        this.isScanning) return;

                    const video = this.$refs.video;

                    const threshold = Number(this.faceThreshold ?? 0.5);
                    const detectionOnly = threshold === 0;

                    // Detect
                    const detection = await faceapi.detectSingleFace(video)
                        .withFaceLandmarks().withFaceDescriptor();

                    if (!detection) {
                        this.faceStatus = 'scanning';
                        this.faceMessage = 'Mencari wajah...';
                        this.resetStability();
                        this.drawFaceOverlay(video, null);
                        return;
                    }

                    // Require centered face (same as Absensi)
                    const centered = this.isFaceCentered(detection, video);
                    if (!centered) {
                        this.faceStatus = 'scanning';
                        this.faceMessage = 'Posisikan wajah di tengah';
                        this.resetStability();
                        this.drawFaceOverlay(video, detection, 'rgba(59,130,246,0.9)');
                        return;
                    }

                    if (detectionOnly) {
                        // Threshold 0.0: detection-only, but still requires hold-still stability.
                        this.faceStatus = 'detecting';
                        this.faceMessage = 'Tahan... Jangan Bergerak';
                        this.stabilityCounter += 1;
                        this.updateFaceProgress();

                        const p = Math.max(0, Math.min(1, this.stabilityCounter / this
                            .stabilityTarget));
                        const g = Math.round(130 + (80 * p));
                        const color = `rgba(34, ${g}, 94, 0.95)`;
                        this.drawFaceOverlay(video, detection, color);

                        if (this.stabilityCounter >= this.stabilityTarget) {
                            this.handleMatch(video);
                        }
                        return;
                    }

                    // Threshold != 0.0: capture immediately when matched.
                    this.faceStatus = 'detecting';
                    this.faceMessage = 'Verifikasi...';

                    if (!this.userDescriptor) {
                        this.faceMessage = 'Data wajah user tidak valid';
                        this.resetStability();
                        this.drawFaceOverlay(video, detection, 'rgba(239,68,68,0.95)');
                        return;
                    }

                    const distance = faceapi.euclideanDistance(detection.descriptor,
                        this.userDescriptor);
                    console.log('Distance:', distance, 'threshold:', threshold);

                    if (distance < threshold) {
                        this.drawFaceOverlay(video, detection, 'rgba(34,197,94,0.95)');
                        this.handleMatch(video);
                    } else {
                        this.faceStatus = 'error';
                        this.faceMessage = 'Wajah tidak cocok';
                        this.resetStability();
                        this.drawFaceOverlay(video, detection, 'rgba(239,68,68,0.95)');
                    }
                }, 500); // Check every 500ms
            },

            stopScanning() {
                this.isScanning = false;
                if (this.scanInterval) clearInterval(this.scanInterval);
                if (this.scanTimeout) clearTimeout(this.scanTimeout);
                this.resetStability();
            },

            handleMatch(video) {
                this.isMatched = true;
                this.stopScanning();
                this.faceStatus = 'success';
                this.faceMessage = 'Berhasil! Memproses...';

                // Capture Image
                const canvas = this.$refs.canvas;
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(video, 0, 0);
                this.capturedImage = canvas.toDataURL('image/png');

                // Submit after short delay
                setTimeout(() => {
                    this.closeFaceModal();
                    this.$nextTick(() => {
                        this.$refs.attendanceForm.submit();
                    });
                }, 1000);
            },

            retryScan() {
                this.showRetry = false;
                this.startScanning();
            },

            closeFaceModal() {
                this.showFaceModal = false;
                this.stopScanning();
                this.clearFaceCanvas();
                if (this.videoStream) {
                    this.videoStream.getTracks().forEach(track => track.stop());
                    this.videoStream = null;
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

                            // Load descriptor before opening modal
                            await this.loadUserDescriptor();
                            if (this.userDescriptor) {
                                this.openFaceModal();
                            } else {
                                this.isLoading = false;
                            }
                        } else {
                            this.$refs.attendanceForm.submit();
                        }
                    }
                } catch (error) {
                    console.error(error);
                    alert('Terjadi kesalahan koneksi');
                } finally {
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
