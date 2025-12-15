{{-- Assets + Alpine component registration for Smart Profile (SPA-safe) --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>

<script>
    (function() {
        // Avoid double-registering on SPA navigations.
        if (window.__smartProfileRegistered) return;
        window.__smartProfileRegistered = true;

        document.addEventListener('alpine:init', () => {
            Alpine.data('smartProfile', (opts = {}) => ({
                previewUrl: null,
                isAnalyzing: false,
                validationStatus: 'idle', // idle, analyzing, valid, invalid
                errorMessage: '',
                modelsLoaded: false,

                // Delete Avatar Modal
                showDeleteAvatarModal: false,

                // Cropper State
                showCropperModal: false,
                cropper: null,
                cropperImage: null,

                timestamp: Date.now(),

                // Data from PHP (passed from x-data)
                userAvatar: opts.userAvatar ?? '',
                userName: opts.userName ?? '',
                storageUrl: opts.storageUrl ?? '',

                get currentAvatar() {
                    if (this.userAvatar) {
                        return `${this.storageUrl}/${this.userAvatar}?v=${this.timestamp}`;
                    }
                    return `https://ui-avatars.com/api/?name=${encodeURIComponent(this.userName)}&background=random`;
                },

                async init() {
                    // Load models in background (don’t block UI)
                    this.loadModels();

                    // Update timestamp periodically to avoid aggressive browser caching
                    setInterval(() => (this.timestamp = Date.now()), 60000);
                },

                async loadModels() {
                    if (this.modelsLoaded) return true;

                    try {
                        const CDN_URL =
                            'https://justadudewhohacks.github.io/face-api.js/models';

                        await Promise.all([
                            faceapi.nets.tinyFaceDetector.loadFromUri(CDN_URL),
                            faceapi.nets.faceLandmark68TinyNet.loadFromUri(CDN_URL),
                        ]);

                        this.modelsLoaded = true;
                        return true;
                    } catch (error) {
                        console.error('Failed to load models', error);
                        this.errorMessage =
                            'Gagal memuat sistem deteksi wajah. Periksa koneksi internet.';
                        this.validationStatus = 'invalid';
                        return false;
                    }
                },

                resetUpload() {
                    this.previewUrl = null;
                    this.validationStatus = 'idle';
                    this.errorMessage = '';
                    this.$refs.fileInput.value = '';
                    if (this.$wire) {
                        this.$wire.set('newAvatar', null);
                    }
                },

                openDeleteAvatarModal() {
                    this.showDeleteAvatarModal = true;
                },

                closeDeleteAvatarModal() {
                    this.showDeleteAvatarModal = false;
                },

                async confirmDeleteAvatar() {
                    if (!this.$wire) return;

                    await this.$wire.call('deleteAvatar');
                    this.userAvatar = '';
                    this.timestamp = Date.now();
                    this.showDeleteAvatarModal = false;
                },

                async handleFileSelect(e) {
                    const file = e.target.files[0];
                    if (!file) return;

                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.cropperImage = e.target.result;
                        this.showCropperModal = true;

                        this.$nextTick(() => {
                            if (this.cropper) {
                                this.cropper.destroy();
                            }

                            const image = document.getElementById(
                                'cropper-image');
                            this.cropper = new Cropper(image, {
                                aspectRatio: 1,
                                viewMode: 2,
                                autoCropArea: 1,
                                dragMode: 'move',
                                responsive: true,
                                restore: false,
                                background: false,
                                guides: true,
                                center: true,
                                highlight: false,
                                toggleDragModeOnDblclick: false,
                                zoomOnWheel: true,
                                wheelZoomRatio: 0.1,
                            });
                        });
                    };
                    reader.readAsDataURL(file);
                },

                cancelCrop() {
                    this.showCropperModal = false;
                    this.cropperImage = null;
                    if (this.cropper) {
                        this.cropper.destroy();
                        this.cropper = null;
                    }
                    this.$refs.fileInput.value = '';
                },

                async applyCrop() {
                    if (!this.cropper) return;

                    this.isAnalyzing = true;
                    this.validationStatus = 'analyzing';
                    this.errorMessage = '';

                    const canvas = this.cropper.getCroppedCanvas({
                        width: 600,
                        height: 600,
                    });

                    this.previewUrl = canvas.toDataURL();
                    this.showCropperModal = false;

                    canvas.toBlob(async (blob) => {
                        const file = new File([blob], 'avatar.png', {
                            type: 'image/png'
                        });
                        await this.validateImage(this.previewUrl, file);
                    }, 'image/png');
                },

                async validateImage(imageUrl, file) {
                    if (!this.modelsLoaded) {
                        const loaded = await this.loadModels();
                        if (!loaded) {
                            this.isAnalyzing = false;
                            return;
                        }
                    }

                    const img = document.createElement('img');
                    img.crossOrigin = 'anonymous';
                    img.src = imageUrl;

                    const timeoutId = setTimeout(() => {
                        if (this.isAnalyzing) {
                            this.isAnalyzing = false;
                            this.validationStatus = 'invalid';
                            this.errorMessage =
                                'Waktu analisis habis. Koneksi internet mungkin lambat.';
                        }
                    }, 30000);

                    img.onload = async () => {
                        try {
                            const detections = await faceapi
                                .detectAllFaces(img, new faceapi
                                    .TinyFaceDetectorOptions())
                                .withFaceLandmarks(true);

                            clearTimeout(timeoutId);

                            if (detections.length === 0) {
                                throw new Error(
                                    'Wajah tidak terdeteksi. Pastikan pencahayaan cukup dan wajah terlihat jelas.'
                                );
                            }
                            if (detections.length > 1) {
                                throw new Error(
                                    'Terdeteksi lebih dari satu wajah. Harap gunakan foto sendiri.'
                                );
                            }

                            const detection = detections[0];
                            const box = detection.detection.box;
                            const score = detection.detection.score;

                            if (score < 0.5) {
                                throw new Error(
                                    'Kualitas foto kurang jelas atau buram. Harap ambil ulang dengan pencahayaan yang lebih baik.'
                                );
                            }

                            const faceHeightRatio = box.height / img.height;
                            if (faceHeightRatio < 0.30) {
                                throw new Error(
                                    'Wajah terlalu jauh atau kecil. Harap foto lebih dekat (Close Up).'
                                );
                            }

                            const nose = detection.landmarks.getNose()[3];
                            const imageCenter = img.width / 2;
                            const safeZone = img.width * 0.20;

                            if (nose.x < imageCenter - safeZone || nose.x >
                                imageCenter + safeZone) {
                                throw new Error(
                                    'Posisi wajah tidak di tengah. Harap posisikan wajah tepat di tengah frame.'
                                );
                            }

                            this.validationStatus = 'valid';

                            if (!this.$wire) {
                                throw new Error(
                                    'Livewire belum siap. Silakan coba lagi.');
                            }

                            this.$wire.upload(
                                'newAvatar',
                                file,
                                () => {
                                    this.isAnalyzing = false;
                                },
                                () => {
                                    this.validationStatus = 'invalid';
                                    this.errorMessage =
                                        'Gagal mengupload foto ke server.';
                                    this.isAnalyzing = false;
                                }
                            );
                        } catch (error) {
                            console.warn('Validation failed:', error.message);
                            this.validationStatus = 'invalid';
                            this.errorMessage = error.message;
                            this.isAnalyzing = false;
                        }
                    };

                    img.onerror = () => {
                        clearTimeout(timeoutId);
                        this.validationStatus = 'invalid';
                        this.errorMessage =
                            'File gambar rusak atau tidak dapat dibaca.';
                        this.isAnalyzing = false;
                    };
                },
            }));
        });
    })();
</script>
