@props([
    'title' => 'Sistem Absensi BAPETEN',
    'description' => 'Sistem Absensi Maganghub BAPETEN - Platform manajemen kehadiran untuk peserta magang.',
    'keywords' => 'absensi, bapeten, magang, kehadiran, internship, logbook',
    'image' => asset('images/Logo_bapeten.png'),
])

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">

<title>{{ $title }}</title>
<meta name="google-site-verification" content="D7lwUHT9cSFPvvz6Ad11J0QBbCgBTe7hi_0Lc7OfY3E" />
<meta name="description" content="{{ $description }}">
<meta name="keywords" content="{{ $keywords }}">
<meta name="robots" content="index, follow">

<!-- Open Graph / Facebook -->
<meta property="og:type" content="website">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:title" content="{{ $title }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:image" content="{{ $image }}">

<!-- Twitter -->
<meta property="twitter:card" content="summary_large_image">
<meta property="twitter:url" content="{{ url()->current() }}">
<meta property="twitter:title" content="{{ $title }}">
<meta property="twitter:description" content="{{ $description }}">
<meta property="twitter:image" content="{{ $image }}">

<!-- Canonical -->
<link rel="canonical" href="{{ url()->current() }}">

<!-- JSON-LD Schema -->
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "SoftwareApplication",
  "name": "{{ $title }}",
  "description": "{{ $description }}",
  "applicationCategory": "BusinessApplication",
  "operatingSystem": "Web",
  "url": "{{ url('/') }}",
  "image": "{{ $image }}",
  "author": {
    "@@type": "Organization",
    "name": "BAPETEN"
  }
}
</script>
