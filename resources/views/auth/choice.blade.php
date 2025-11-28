<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Masuk — Absensi</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Leaflet CSS & JS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>

    @include('auth.partials.styles')
</head>

<body class="min-h-screen bg-gray-50 flex items-center justify-center" x-data="attendance">
    <div class="max-w-xl w-full p-6">
        <div class="bg-white shadow rounded-lg p-6 text-center">
            <h1 class="text-2xl font-semibold mb-2">Selamat datang di Sistem Absensi</h1>
            <p class="text-sm text-gray-600 mb-6">Pilih jenis akun untuk masuk. Untuk karyawan biasa, klik "Masuk
                sebagai User". Untuk administrasi sistem, klik "Masuk sebagai Admin".</p>

            @if (session('success'))
                <div class="mb-4 p-4 bg-green-100 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <a href="{{ route('filament.user.auth.login') }}"
                    class="inline-block bg-amber-500 hover:bg-amber-600 text-white font-semibold py-3 px-4 rounded shadow">Masuk
                    sebagai User</a>

                <a href="{{ route('filament.admin.auth.login') }}"
                    class="inline-block border border-gray-200 hover:bg-gray-50 text-gray-700 font-medium py-3 px-4 rounded">Masuk
                    sebagai Admin</a>
            </div>

            <div class="mt-4">
                <button @click="openDirect=true; getLocation()"
                    class="w-full inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded shadow">
                    Absen Langsung
                </button>
            </div>

            <p class="mt-6 text-xs text-gray-400">Jika Anda sudah masuk, Anda akan dialihkan sesuai peran Anda.</p>
        </div>

        <p class="mt-4 text-center text-xs text-gray-500">Contact: admin@contoh.local</p>
    </div>

    @include('auth.partials.modal')
    @include('auth.partials.scripts')
</body>

</html>
