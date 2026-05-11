{{-- PWA Install Prompt Banner (SPA-safe, Alpine.js) --}}
<style>
    /* ── PWA Install Banner Styles ── */
    .pwa-banner-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.25);
        z-index: 9998;
        opacity: 0;
        transition: opacity 0.35s ease;
        pointer-events: none;
    }

    .pwa-banner-backdrop.active {
        opacity: 1;
        pointer-events: auto;
    }

    .pwa-banner {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        z-index: 9999;
        transform: translateY(100%);
        transition: transform 0.45s cubic-bezier(0.32, 0.72, 0, 1);
    }

    .pwa-banner.visible {
        transform: translateY(0);
    }

    .pwa-banner-inner {
        max-width: 28rem;
        margin: 0 auto;
        background: linear-gradient(135deg, #fffbeb 0%, #ffffff 50%, #fef3c7 100%);
        border-top-left-radius: 1.25rem;
        border-top-right-radius: 1.25rem;
        box-shadow:
            0 -4px 24px rgba(0, 0, 0, 0.10),
            0 -1px 6px rgba(0, 0, 0, 0.06);
        overflow: hidden;
    }

    /* Decorative pull handle */
    .pwa-banner-handle {
        display: flex;
        justify-content: center;
        padding: 0.625rem 0 0.25rem;
    }

    .pwa-banner-handle span {
        width: 2.25rem;
        height: 0.25rem;
        background: #d4d4d4;
        border-radius: 9999px;
    }

    .pwa-banner-body {
        padding: 0.5rem 1.25rem 1.25rem;
    }

    /* Header row */
    .pwa-banner-header {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 0.625rem;
    }

    .pwa-banner-icon {
        flex-shrink: 0;
        width: 3rem;
        height: 3rem;
        border-radius: 0.75rem;
        box-shadow: 0 2px 8px rgba(217, 119, 6, 0.18);
        object-fit: cover;
    }

    .pwa-banner-title {
        font-size: 0.9375rem;
        font-weight: 700;
        color: #1c1917;
        line-height: 1.25;
        margin: 0;
    }

    .pwa-banner-subtitle {
        font-size: 0.75rem;
        color: #78716c;
        margin: 0.125rem 0 0;
        line-height: 1.35;
    }

    .pwa-banner-close {
        flex-shrink: 0;
        margin-left: auto;
        width: 2rem;
        height: 2rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 9999px;
        border: none;
        background: #f5f5f4;
        color: #78716c;
        cursor: pointer;
        transition: background 0.2s, color 0.2s;
        padding: 0;
    }

    .pwa-banner-close:hover {
        background: #e7e5e4;
        color: #44403c;
    }

    /* Description / iOS instructions */
    .pwa-banner-desc {
        font-size: 0.8125rem;
        color: #57534e;
        line-height: 1.5;
        margin: 0 0 0.875rem;
    }

    .pwa-banner-desc strong {
        color: #44403c;
    }

    /* iOS step-by-step */
    .pwa-ios-steps {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        background: #fefce8;
        border: 1px solid #fde68a;
        border-radius: 0.625rem;
        padding: 0.625rem 0.75rem;
        margin-bottom: 0.875rem;
        font-size: 0.75rem;
        color: #78716c;
        line-height: 1.45;
    }

    .pwa-ios-steps svg {
        flex-shrink: 0;
        color: #d97706;
    }

    /* Actions row */
    .pwa-banner-actions {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .pwa-install-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        width: 100%;
        padding: 0.6875rem 1rem;
        border: none;
        border-radius: 0.625rem;
        background: linear-gradient(135deg, #f59e0b, #d97706);
        color: #fff;
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        box-shadow: 0 2px 8px rgba(217, 119, 6, 0.3);
        transition: transform 0.15s, box-shadow 0.15s, filter 0.15s;
        letter-spacing: 0.01em;
    }

    .pwa-install-btn:hover {
        filter: brightness(1.05);
        box-shadow: 0 4px 14px rgba(217, 119, 6, 0.35);
    }

    .pwa-install-btn:active {
        transform: scale(0.97);
    }

    /* Dismiss checkbox row */
    .pwa-dismiss-row {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.375rem;
        padding-top: 0.25rem;
    }

    .pwa-dismiss-row input[type="checkbox"] {
        width: 0.875rem;
        height: 0.875rem;
        accent-color: #d97706;
        cursor: pointer;
        margin: 0;
    }

    .pwa-dismiss-row label {
        font-size: 0.6875rem;
        color: #a8a29e;
        cursor: pointer;
        user-select: none;
    }

    /* Dark mode adjustments */
    .dark .pwa-banner-inner {
        background: linear-gradient(135deg, #1c1917 0%, #292524 50%, #1c1917 100%);
        box-shadow:
            0 -4px 24px rgba(0, 0, 0, 0.4),
            0 -1px 6px rgba(0, 0, 0, 0.25);
    }

    .dark .pwa-banner-handle span {
        background: #44403c;
    }

    .dark .pwa-banner-title {
        color: #fafaf9;
    }

    .dark .pwa-banner-subtitle {
        color: #a8a29e;
    }

    .dark .pwa-banner-close {
        background: #292524;
        color: #a8a29e;
    }

    .dark .pwa-banner-close:hover {
        background: #44403c;
        color: #d6d3d1;
    }

    .dark .pwa-banner-desc {
        color: #a8a29e;
    }

    .dark .pwa-banner-desc strong {
        color: #d6d3d1;
    }

    .dark .pwa-ios-steps {
        background: #292524;
        border-color: #44403c;
        color: #a8a29e;
    }

    .dark .pwa-dismiss-row label {
        color: #78716c;
    }
</style>

<div x-data="pwaInstallBanner" x-cloak>
    {{-- Subtle backdrop --}}
    <div class="pwa-banner-backdrop" :class="{ 'active': show }" @click="dismiss()"></div>

    {{-- Bottom-sheet banner --}}
    <div class="pwa-banner" :class="{ 'visible': show }" role="dialog" aria-label="Install aplikasi">

        <div class="pwa-banner-inner">
            {{-- Pull handle --}}
            <div class="pwa-banner-handle"><span></span></div>

            <div class="pwa-banner-body">
                {{-- Header --}}
                <div class="pwa-banner-header">
                    <img src="/images/icons/icon-96x96.png" alt="Absensi BAPETEN" class="pwa-banner-icon"
                        loading="lazy">
                    <div>
                        <p class="pwa-banner-title">Install Absensi BAPETEN</p>
                        <p class="pwa-banner-subtitle">Akses lebih cepat dari home screen</p>
                    </div>
                    <button class="pwa-banner-close" @click="dismiss()" aria-label="Tutup">
                        <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                            <path d="M10.5 3.5L3.5 10.5M3.5 3.5L10.5 10.5" stroke="currentColor" stroke-width="1.5"
                                stroke-linecap="round" />
                        </svg>
                    </button>
                </div>

                {{-- Description: Android / default --}}
                <template x-if="!isIos">
                    <p class="pwa-banner-desc">
                        Dapatkan pengalaman terbaik dengan <strong>menginstall aplikasi</strong> langsung ke perangkat
                        Anda — tanpa perlu buka browser, lebih cepat & bisa akses offline.
                    </p>
                </template>

                {{-- Description: iOS Safari --}}
                <template x-if="isIos">
                    <div>
                        <p class="pwa-banner-desc">
                            Tambahkan aplikasi ke <strong>home screen</strong> untuk akses instan tanpa buka browser.
                        </p>
                        <div class="pwa-ios-steps">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 5v14M5 12l7-7 7 7" />
                                <rect x="4" y="17" width="16" height="2" rx="1" fill="currentColor"
                                    stroke="none" opacity="0.3" />
                            </svg>
                            <span>
                                Tap ikon <strong>Share</strong> (⬆️) di bawah layar, lalu pilih <strong>"Add to Home
                                    Screen"</strong>
                            </span>
                        </div>
                    </div>
                </template>

                {{-- Actions --}}
                <div class="pwa-banner-actions">
                    {{-- Install button (Android/Chrome) --}}
                    <template x-if="!isIos">
                        <button class="pwa-install-btn" @click="install()">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 5v14M19 12l-7 7-7-7" />
                            </svg>
                            Install Sekarang
                        </button>
                    </template>

                    {{-- iOS: "Mengerti" button --}}
                    <template x-if="isIos">
                        <button class="pwa-install-btn" @click="dismiss()">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 6L9 17l-5-5" />
                            </svg>
                            Saya Mengerti
                        </button>
                    </template>

                    {{-- Dismiss forever checkbox --}}
                    <div class="pwa-dismiss-row">
                        <input type="checkbox" id="pwa-dismiss-forever" x-model="dismissForever">
                        <label for="pwa-dismiss-forever">Jangan tampilkan lagi</label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        // Avoid double-registering on SPA navigations.
        if (window.__pwaInstallBannerRegistered) return;
        window.__pwaInstallBannerRegistered = true;

        // Capture the beforeinstallprompt event globally (fires before Alpine init).
        let _deferredPrompt = null;
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            _deferredPrompt = e;
            // If Alpine component is already mounted, notify it.
            window.dispatchEvent(new CustomEvent('pwa:prompt-ready'));
        });

        // Detect installation success.
        window.addEventListener('appinstalled', () => {
            _deferredPrompt = null;
            localStorage.setItem('pwa_installed', '1');
            window.dispatchEvent(new CustomEvent('pwa:installed'));
        });

        document.addEventListener('alpine:init', () => {
            Alpine.data('pwaInstallBanner', () => ({
                show: false,
                isIos: false,
                isMobile: false,
                isStandalone: false,
                dismissForever: false,
                deferredPrompt: null,

                init() {
                    this.detectEnvironment();

                    // Pick up any already-captured prompt.
                    this.deferredPrompt = _deferredPrompt;

                    // Listen for late-arriving prompt.
                    window.addEventListener('pwa:prompt-ready', () => {
                        this.deferredPrompt = _deferredPrompt;
                        this.maybeShow();
                    });

                    // Auto-hide if user installs.
                    window.addEventListener('pwa:installed', () => {
                        this.show = false;
                    });

                    // Delay before first show (let user orient first).
                    setTimeout(() => this.maybeShow(), 3500);
                },

                detectEnvironment() {
                    // Standalone check (already installed & opened as PWA).
                    this.isStandalone =
                        window.matchMedia('(display-mode: standalone)').matches ||
                        window.matchMedia('(display-mode: fullscreen)').matches ||
                        window.navigator.standalone === true; // Safari iOS

                    // Mobile detection.
                    const ua = navigator.userAgent || '';
                    this.isMobile =
                        /Android|iPhone|iPad|iPod|webOS|BlackBerry|IEMobile|Opera Mini/i.test(
                            ua) ||
                        ('ontouchstart' in window && window.innerWidth < 1024);

                    // iOS detection (iPhone/iPad Safari — no beforeinstallprompt).
                    const isIosDevice = /iPhone|iPad|iPod/i.test(ua);
                    const isIosSafari = isIosDevice && /Safari/i.test(ua) && !
                        /CriOS|FxiOS|OPiOS|EdgiOS/i.test(ua);
                    // Also detect iPad masquerading as Mac (iPadOS 13+).
                    const isIpadDesktop = /Macintosh/i.test(ua) && 'ontouchend' in document;
                    this.isIos = isIosSafari || isIpadDesktop;
                },

                maybeShow() {
                    // Never show if already in standalone PWA mode.
                    if (this.isStandalone) return;

                    // Never show on desktop (unless iOS iPad desktop mode).
                    if (!this.isMobile && !this.isIos) return;

                    // Check if user explicitly dismissed forever.
                    if (localStorage.getItem('pwa_dismiss_forever') === '1') return;

                    // Check if already installed previously.
                    if (localStorage.getItem('pwa_installed') === '1') return;

                    // Check session dismiss (sessionStorage = per-tab lifetime).
                    if (sessionStorage.getItem('pwa_dismissed_session') === '1') return;

                    // For non-iOS, we need the deferred prompt to be available.
                    // For iOS, always show (since there's no native prompt API).
                    if (!this.isIos && !this.deferredPrompt) return;

                    this.show = true;
                },

                async install() {
                    if (!this.deferredPrompt) return;

                    this.deferredPrompt.prompt();
                    const {
                        outcome
                    } = await this.deferredPrompt.userChoice;

                    if (outcome === 'accepted') {
                        localStorage.setItem('pwa_installed', '1');
                    }

                    this.deferredPrompt = null;
                    _deferredPrompt = null;
                    this.show = false;
                },

                dismiss() {
                    this.show = false;

                    if (this.dismissForever) {
                        localStorage.setItem('pwa_dismiss_forever', '1');
                    } else {
                        // Only dismiss for this browser session.
                        sessionStorage.setItem('pwa_dismissed_session', '1');
                    }
                },
            }));
        });
    })();
</script>
