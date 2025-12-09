<!DOCTYPE html>
<html lang="id">

<head>
    <x-head title="Offline - Absensi BAPETEN">
        <script src="https://cdn.tailwindcss.com"></script>
    </x-head>
</head>

<body class="bg-gray-100 h-screen flex flex-col items-center justify-center p-4 text-center font-sans">
    <div class="bg-white p-8 rounded-3xl shadow-xl max-w-sm w-full border border-gray-100">
        <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829m-4.243 2.829a4.978 4.978 0 01-1.414-2.83m-1.414 5.658a9 9 0 01-2.167-9.238m7.824 2.167a1 1 0 111.414 1.414m-1.414-1.414L3 3m8.293 8.293l1.414 1.414" />
            </svg>
        </div>
        <h1 class="text-xl font-bold text-gray-900 mb-2">Anda sedang offline</h1>
        <p class="text-sm text-gray-500 mb-8 leading-relaxed">Koneksi internet terputus. Silakan periksa jaringan Anda
            dan coba lagi.</p>
        <button onclick="window.location.reload()"
            class="w-full py-3.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl shadow-lg shadow-blue-600/20 transition-all active:scale-95">
            Coba Lagi
        </button>
    </div>
</body>

</html>
