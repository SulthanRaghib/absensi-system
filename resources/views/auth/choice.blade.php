<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Masuk — Absensi</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-gray-50 flex items-center justify-center">
    <div class="max-w-xl w-full p-6">
        <div class="bg-white shadow rounded-lg p-6 text-center">
            <h1 class="text-2xl font-semibold mb-2">Selamat datang di Sistem Absensi</h1>
            <p class="text-sm text-gray-600 mb-6">Pilih jenis akun untuk masuk. Untuk karyawan biasa, klik "Masuk
                sebagai User". Untuk administrasi sistem, klik "Masuk sebagai Admin".</p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <a href="{{ route('filament.user.auth.login') }}"
                    class="inline-block bg-amber-500 hover:bg-amber-600 text-white font-semibold py-3 px-4 rounded shadow">Masuk
                    sebagai User</a>

                <a href="{{ route('filament.admin.auth.login') }}"
                    class="inline-block border border-gray-200 hover:bg-gray-50 text-gray-700 font-medium py-3 px-4 rounded">Masuk
                    sebagai Admin</a>
            </div>

            <p class="mt-6 text-xs text-gray-400">Jika Anda sudah masuk, Anda akan dialihkan sesuai peran Anda.</p>
        </div>

        <p class="mt-4 text-center text-xs text-gray-500">Contact: admin@contoh.local</p>
    </div>
</body>

</html>
