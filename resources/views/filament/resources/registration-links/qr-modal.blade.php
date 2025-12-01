<div class="flex flex-col items-center justify-center p-4 space-y-4">
    <img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data={{ urlencode($url) }}" alt="QR Code"
        class="border rounded-lg shadow-sm">
    <div class="text-center">
        <p class="text-sm text-gray-500 mb-2">Scan to register</p>
        <a href="{{ $url }}" target="_blank" class="text-primary-600 hover:underline text-sm break-all">
            {{ $url }}
        </a>
    </div>
</div>
