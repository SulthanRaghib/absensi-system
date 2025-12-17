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
            showEarlyCheckoutModal: false,
            showCheatModal: false,
            deviceToken: null,

            // Face Recognition State
            showFaceModal: false,
            showNoAvatarModal: false,
            isFaceLoading: false,
            isModelLoaded: false,
            modelLoadingPromise: null,
            descriptorLoadingPromise: null,
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

            // Hold-still stability
            stabilityCounter: 0,
            stabilityTarget: 10, // e.g., 10 counts = 5 seconds (500ms interval)
            faceProgressPercent: 0,
            faceProgressDashArray: 0,
            faceProgressDashOffset: 0,

            async init() {
                // Device Binding Logic
                let token = localStorage.getItem('device_token');
                if (!token) {
                    token = crypto.randomUUID();
                    localStorage.setItem('device_token', token);
                }
                this.deviceToken = token;

                this.$nextTick(async () => {
                    await this.ensureLeafletAssets();
                    await this.waitForMapContainerReady();

                    this.initMap();
                    this.forceMapReflow();
                    this.startTracking(true); // Center map on init

                    if (config.faceRecognitionEnabled) {
                        // Preload in background (don’t block UI).
                        this.ensureFaceModelsLoaded();
                        // Descriptor will be loaded on-demand after models are ready.
                    }
                });
            },

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

            isFaceCentered(detection, video) {
                try {
                    const box = detection?.detection?.box;
                    if (!box || !video?.videoWidth || !video?.videoHeight) return false;

                    const w = video.videoWidth;
                    const h = video.videoHeight;
                    const cx = box.x + box.width / 2;
                    const cy = box.y + box.height / 2;

                    // Must be reasonably centered (avoid edges)
                    const minX = w * 0.20;
                    const maxX = w * 0.80;
                    const minY = h * 0.20;
                    const maxY = h * 0.80;

                    // Also require face to be large enough
                    const minFaceSize = Math.min(w, h) * 0.18;
                    const sizeOk = box.width >= minFaceSize && box.height >= minFaceSize;

                    return cx >= minX && cx <= maxX && cy >= minY && cy <= maxY && sizeOk;
                } catch (e) {
                    return false;
                }
            },

            drawFaceOverlay(video, detection, color = 'rgba(59,130,246,0.9)') {
                const canvas = this.$refs.canvas;
                if (!canvas || !video) return;

                const ctx = canvas.getContext('2d');
                canvas.width = video.videoWidth || 640;
                canvas.height = video.videoHeight || 480;

                ctx.clearRect(0, 0, canvas.width, canvas.height);
                if (!detection?.detection?.box) return;

                const box = detection.detection.box;
                ctx.lineWidth = Math.max(3, Math.round(Math.min(canvas.width, canvas.height) * 0.006));
                ctx.strokeStyle = color;
                ctx.shadowColor = color;
                ctx.shadowBlur = 10;
                ctx.strokeRect(box.x, box.y, box.width, box.height);
                ctx.shadowBlur = 0;
            },

            waitForFaceApi() {
                return new Promise((resolve, reject) => {
                    if (window.faceapi) return resolve(true);
                    const startedAt = Date.now();
                    const tick = () => {
                        if (window.faceapi) return resolve(true);
                        if (Date.now() - startedAt > 10000) {
                            return reject(new Error('faceapi tidak siap'));
                        }
                        setTimeout(tick, 50);
                    };
                    tick();
                });
            },

            ensureFaceModelsLoaded() {
                if (this.isModelLoaded) return Promise.resolve(true);
                if (this.modelLoadingPromise) return this.modelLoadingPromise;
                this.modelLoadingPromise = this.loadFaceModels().finally(() => {
                    // Allow re-try if load failed
                    if (!this.isModelLoaded) this.modelLoadingPromise = null;
                });
                return this.modelLoadingPromise;
            },

            ensureUserDescriptorLoaded() {
                if (this.userDescriptor) return Promise.resolve(true);
                if (!config.userAvatar) return Promise.resolve(false);
                if (this.descriptorLoadingPromise) return this.descriptorLoadingPromise;
                this.descriptorLoadingPromise = (async () => {
                    // Always ensure models are loaded before descriptor detection to avoid
                    // "load model before inference" errors.
                    const modelsOk = await this.ensureFaceModelsLoaded();
                    if (!modelsOk) return false;
                    return this.loadUserDescriptor();
                })().finally(() => {
                    // Allow re-try if descriptor load failed
                    if (!this.userDescriptor) this.descriptorLoadingPromise = null;
                });
                return this.descriptorLoadingPromise;
            },

            ensureLeafletAssets() {
                return new Promise((resolve, reject) => {
                    // Leaflet already available
                    if (window.L && typeof window.L.map === 'function') {
                        resolve();
                        return;
                    }

                    // Ensure CSS
                    const cssHref = 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css';
                    if (!document.querySelector(`link[href="${cssHref}"]`)) {
                        const link = document.createElement('link');
                        link.rel = 'stylesheet';
                        link.href = cssHref;
                        document.head.appendChild(link);
                    }

                    // Load JS (in case SPA navigation skipped executing the inline script tag)
                    const jsSrc = 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js';
                    let script = document.querySelector(`script[src="${jsSrc}"]`);

                    if (script && (window.L && typeof window.L.map === 'function')) {
                        resolve();
                        return;
                    }

                    if (!script) {
                        script = document.createElement('script');
                        script.src = jsSrc;
                        script.async = true;
                        script.onload = () => resolve();
                        script.onerror = () => reject(new Error('Gagal memuat Leaflet.js'));
                        document.head.appendChild(script);
                    } else {
                        // Existing script tag present; wait a bit for it to finish loading
                        const startedAt = Date.now();
                        const tick = () => {
                            if (window.L && typeof window.L.map === 'function') {
                                resolve();
                                return;
                            }
                            if (Date.now() - startedAt > 10000) {
                                reject(new Error('Leaflet.js tidak siap'));
                                return;
                            }
                            setTimeout(tick, 50);
                        };
                        tick();
                    }
                }).catch((e) => {
                    console.error(e);
                    this.showAlert('Gagal memuat peta. Coba refresh sekali.', 'error');
                });
            },

            waitForMapContainerReady() {
                return new Promise((resolve) => {
                    const startedAt = Date.now();
                    const tick = () => {
                        const el = document.getElementById('map');
                        if (el && el.offsetWidth > 0 && el.offsetHeight > 0) {
                            resolve();
                            return;
                        }
                        if (Date.now() - startedAt > 3000) {
                            // Don't block forever; still try init + invalidateSize.
                            resolve();
                            return;
                        }
                        requestAnimationFrame(tick);
                    };
                    tick();
                });
            },

            forceMapReflow() {
                // Leaflet often renders blank if the container is still transitioning/resizing.
                // These delayed invalidateSize calls make it reliable on sidebar navigation.
                if (!this.map) return;

                const invalidate = () => {
                    if (!this.map) return;
                    try {
                        this.map.invalidateSize(true);
                    } catch (e) {
                        // ignore
                    }
                };

                requestAnimationFrame(invalidate);
                setTimeout(invalidate, 50);
                setTimeout(invalidate, 250);
                setTimeout(invalidate, 800);
            },

            async loadFaceModels() {
                try {
                    await this.waitForFaceApi();

                    // Match Direct Attendance: load from public /models
                    const MODEL_URL = '/models';

                    await Promise.all([
                        faceapi.nets.ssdMobilenetv1.loadFromUri(MODEL_URL),
                        faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
                        faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL)
                    ]);

                    // Extra guard: verify nets actually marked as loaded
                    const ok = faceapi.nets.ssdMobilenetv1.isLoaded &&
                        faceapi.nets.faceLandmark68Net.isLoaded &&
                        faceapi.nets.faceRecognitionNet.isLoaded;

                    this.isModelLoaded = ok;
                    console.log('Face API Models Loaded');
                    if (!ok) {
                        throw new Error('Model belum siap (isLoaded=false)');
                    }
                    return true;
                } catch (error) {
                    console.error('Error loading face models:', error);
                    this.showAlert('Gagal memuat sistem pengenalan wajah. Pastikan folder /public/models ada.',
                        'error');
                    return false;
                }
            },

            async loadUserDescriptor() {
                if (!config.userAvatar) return;
                try {
                    // Use fetchImage to handle CORS if needed
                    const img = await faceapi.fetchImage(config.userAvatar);
                    const detection = await faceapi.detectSingleFace(img).withFaceLandmarks()
                        .withFaceDescriptor();

                    if (detection) {
                        this.userDescriptor = detection.descriptor;
                        console.log('User descriptor loaded');
                        return true;
                    } else {
                        console.warn('No face found in user avatar');
                        this.showAlert(
                            'Foto profil Anda tidak valid (wajah tidak terdeteksi). Harap ganti foto profil.',
                            'error');
                        return false;
                    }
                } catch (e) {
                    console.error('Error loading user avatar descriptor', e);
                    this.showAlert('Gagal memuat data wajah user.', 'error');
                    return false;
                }
            },

            async initiateCheckIn() {
                console.log('Initiating Check-in...');
                if (config.faceRecognitionEnabled) {
                    if (!config.userAvatar) {
                        this.showNoAvatarModal = true;
                        return;
                    }

                    // Match Direct Attendance UX: open modal + camera immediately,
                    // while models/descriptor load in the background.
                    this.openFaceModal();
                } else {
                    this.performAttendance('in');
                }
            },

            async openFaceModal() {
                this.showFaceModal = true;
                this.showRetry = false;
                this.isMatched = false;
                this.faceStatus = 'scanning';
                this.faceMessage = 'Memulai kamera...';

                this.resetStability();
                this.initFaceProgressRing();
                this.updateFaceProgress();

                // Start loading models in background ASAP (don’t await here).
                this.ensureFaceModelsLoaded();

                await this.startCamera();

                // After camera starts, wait for prerequisites then scan.
                // (Keeps modal responsive even if models are still downloading.)
                this.prepareFaceVerification();
            },

            async prepareFaceVerification() {
                try {
                    if (!this.showFaceModal) return;

                    // Show a clear message while waiting.
                    if (!this.isModelLoaded) {
                        this.faceStatus = 'scanning';
                        this.faceMessage = 'Memuat Model Wajah...';
                    }

                    await Promise.all([
                        this.ensureFaceModelsLoaded(),
                        this.ensureUserDescriptorLoaded(),
                    ]);

                    if (!this.showFaceModal) return;

                    if (!this.isModelLoaded) {
                        this.showAlert('Sistem wajah gagal dimuat. Coba refresh sekali.', 'error');
                        this.closeFaceModal();
                        return;
                    }

                    // If threshold == 0.0 => detection only (no matching required)
                    const threshold = Number(config.faceThreshold ?? 0.5);
                    const detectionOnly = threshold === 0;
                    if (!detectionOnly) {
                        await this.ensureUserDescriptorLoaded();
                        if (!this.userDescriptor) {
                            this.showAlert(
                                'Wajah tidak ditemukan di foto profil. Harap ganti foto profil yang jelas.',
                                'error');
                            this.closeFaceModal();
                            return;
                        }
                    }

                    this.startScanning();
                } catch (e) {
                    console.error(e);
                    this.showAlert('Gagal menyiapkan verifikasi wajah. Coba lagi.', 'error');
                    this.closeFaceModal();
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
                    this.showAlert('Gagal mengakses kamera. Pastikan izin kamera diberikan.', 'error');
                    this.closeFaceModal();
                }
            },

            startScanning() {
                this.isScanning = true;
                this.faceStatus = 'scanning';
                this.faceMessage = this.isModelLoaded ? 'Mencari wajah...' : 'Memuat Model Wajah...';
                this.resetStability();

                // Timeout 30s
                if (this.scanTimeout) clearTimeout(this.scanTimeout);
                this.scanTimeout = setTimeout(() => {
                    this.stopScanning();
                    if (!this.isMatched) {
                        this.showRetry = true;
                        this.faceStatus = 'error';
                        this.faceMessage = 'Waktu habis';
                        this.resetStability();
                    }
                }, 30000);

                if (this.scanInterval) clearInterval(this.scanInterval);
                this.scanInterval = setInterval(async () => {
                    if (!this.videoStream || this.isMatched || !this.isModelLoaded || !this.isScanning)
                        return;

                    const video = this.$refs.video;

                    // Threshold config
                    const threshold = Number(config.faceThreshold ?? 0.5);
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

                    // Must be centered (hold still)
                    const centered = this.isFaceCentered(detection, video);
                    if (!centered) {
                        this.faceStatus = 'scanning';
                        this.faceMessage = 'Posisikan wajah di tengah';
                        this.resetStability();
                        this.drawFaceOverlay(video, detection, 'rgba(59,130,246,0.9)');
                        return;
                    }

                    if (detectionOnly) {
                        // Threshold 0.0: detection-only, but still requires "hold still" stability.
                        this.stabilityCounter += 1;
                        this.updateFaceProgress();

                        this.faceStatus = 'detecting';
                        this.faceMessage = 'Tahan... Jangan Bergerak 📸';

                        const p = Math.max(0, Math.min(1, this.stabilityCounter / this.stabilityTarget));
                        const g = Math.round(130 + (80 * p));
                        const color = `rgba(34, ${g}, 94, 0.95)`; // green-ish
                        this.drawFaceOverlay(video, detection, color);

                        if (this.stabilityCounter >= this.stabilityTarget) {
                            this.handleMatch(video);
                        }
                        return;
                    }

                    // Threshold != 0.0: verify match and capture immediately when matched (no waiting).
                    if (!this.userDescriptor) {
                        // Descriptor missing should not happen here (prepareFaceVerification guards),
                        // but keep it safe.
                        this.faceStatus = 'scanning';
                        this.faceMessage = 'Menyiapkan verifikasi...';
                        this.resetStability();
                        return;
                    }

                    const distance = faceapi.euclideanDistance(detection.descriptor, this.userDescriptor);
                    const isOk = distance < threshold;

                    if (isOk) {
                        this.faceStatus = 'success';
                        this.faceMessage = 'Berhasil! Memproses...';
                        this.drawFaceOverlay(video, detection, 'rgba(34,197,94,0.95)');
                        this.handleMatch(video);
                    } else {
                        this.faceStatus = 'error';
                        this.faceMessage = 'Wajah Tidak Cocok';
                        this.resetStability();
                        this.drawFaceOverlay(video, detection, 'rgba(239,68,68,0.95)');
                        console.log('Distance:', distance, 'threshold:', threshold);
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
                    this.performAttendance('in', false, this.capturedImage);
                }, 1000);
            },

            retryScan() {
                this.showRetry = false;
                this.startScanning();
            },

            closeFaceModal() {
                this.showFaceModal = false;
                this.stopScanning();
                if (this.videoStream) {
                    this.videoStream.getTracks().forEach(track => track.stop());
                    this.videoStream = null;
                }
            },

            initMap() {
                if (this.map) return;

                if (!window.L || typeof window.L.map !== 'function') {
                    // Leaflet not ready yet; init() will call ensureLeafletAssets and retry.
                    return;
                }

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

                this.forceMapReflow();
            },

            manualUpdateLocation() {
                this.showAlert('Mencari lokasi terkini...', 'info');
                this.startTracking(true);
            },

            startTracking(centerOnUpdate = false) {
                if (!navigator.geolocation) {
                    this.showAlert('Browser tidak mendukung GPS!', 'error');
                    return;
                }

                const updateFromPosition = (position) => {
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

                    if (centerOnUpdate) {
                        this.map.setView([lat, lng], 18);
                        centerOnUpdate = false;
                    }
                };

                if (this.watchId) {
                    navigator.geolocation.clearWatch(this.watchId);
                }

                // Fast path: cached / low-power fix first to avoid initial TIMEOUT on some devices
                navigator.geolocation.getCurrentPosition(
                    (position) => updateFromPosition(position),
                    () => {}, {
                        enableHighAccuracy: false,
                        timeout: 8000,
                        maximumAge: 60000
                    }
                );

                this.watchId = navigator.geolocation.watchPosition(
                    (position) => {
                        updateFromPosition(position);
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
                        timeout: 60000,
                        maximumAge: 10000
                    }
                );
            },

            updateMapMarker(lat, lng) {
                if (!this.map) return;
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

                const a =
                    Math.sin(Δφ / 2) * Math.sin(Δφ / 2) +
                    Math.cos(φ1) * Math.cos(φ2) *
                    Math.sin(Δλ / 2) * Math.sin(Δλ / 2);
                const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

                return R * c;
            },

            async performAttendance(action, force = false, image = null) {
                if (!this.userLocation) {
                    this.showAlert('Lokasi belum ditemukan!', 'error');
                    return;
                }

                // Check Avatar for Check Out
                if (action === 'out') {
                    if (!config.userAvatar) {
                        this.showNoAvatarModal = true;
                        return;
                    }
                }

                // Early Checkout Validation
                if (action === 'out' && !force) {
                    const now = new Date();
                    const day = now.getDay();
                    const hour = now.getHours();
                    const minute = now.getMinutes();

                    let isEarly = false;

                    console.log('Debug Time:', {
                        day,
                        hour,
                        minute
                    });

                    // Friday (5): 16:30
                    if (day === 5) {
                        if (hour < 16 || (hour === 16 && minute < 30)) isEarly = true;
                    }
                    // Other days (Mon-Thu, Sat, Sun): 16:00
                    else {
                        if (hour < 16) isEarly = true;
                    }

                    if (isEarly) {
                        console.log('Showing early checkout modal');
                        this.showEarlyCheckoutModal = true;
                        return;
                    }
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
                            accuracy: this.userLocation.accuracy,
                            device_token: this.deviceToken,
                            image: image // Send captured image if available
                        })
                    });

                    const data = await response.json();

                    // Debugging response
                    console.log('Attendance Response:', {
                        status: response.status,
                        data
                    });

                    if (data.cheat_alert) {
                        this.showCheatModal = true;
                        return;
                    }

                    if (data.success) {
                        // Update Device Token if rotated
                        if (data.data.new_device_id) {
                            console.log('Rotating Device ID:', data.data.new_device_id);
                            localStorage.setItem('device_token', data.data.new_device_id);
                            this.deviceToken = data.data.new_device_id;
                        }

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

            confirmEarlyCheckout() {
                this.showEarlyCheckoutModal = false;
                this.performAttendance('out', true);
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
