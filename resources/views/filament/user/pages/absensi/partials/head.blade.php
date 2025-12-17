<!-- Leaflet CSS & JS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>

{{-- Face API is loaded globally for the user panel via HEAD_END hook (see smart-profile-assets). --}}

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

    video {
        transform: scaleX(-1);
        /* Mirror the video */
    }

    @keyframes scan {
        0% {
            top: 0%;
            opacity: 0;
        }

        10% {
            opacity: 1;
        }

        90% {
            opacity: 1;
        }

        100% {
            top: 100%;
            opacity: 0;
        }
    }

    .animate-scan {
        animation: scan 2s linear infinite;
    }
</style>
