@props(['title' => 'Sistem Absensi BAPETEN'])

<x-seo-head :title="$title" {{ $attributes }} />

<!-- PWA -->
@laravelPWA

<!-- Fonts -->
<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600|inter:300,400,500,600,700" rel="stylesheet" />

<!-- Styles & Scripts -->
@vite(['resources/css/app.css', 'resources/js/app.js'])
@filamentStyles
@filamentScripts

{{ $slot }}
