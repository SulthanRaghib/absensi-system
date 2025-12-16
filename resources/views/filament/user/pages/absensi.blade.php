<div>
    <x-filament-panels::page>
        @include('filament.user.pages.absensi.partials.head')

        <div x-data="absensiMapData({
            officeLat: {{ $officeLocation['latitude'] }},
            officeLng: {{ $officeLocation['longitude'] }},
            officeRadius: {{ $officeLocation['radius'] }},
            checkInRoute: '{{ route('absensi.check-in') }}',
            checkOutRoute: '{{ route('absensi.check-out') }}',
            csrfToken: '{{ csrf_token() }}',
            faceRecognitionEnabled: {{ $faceRecognitionEnabled ? 'true' : 'false' }},
            userAvatar: '{{ Auth::user()->avatar_url ? asset('storage/' . Auth::user()->avatar_url) : null }}'
        })" class="grid grid-cols-1 md:grid-cols-12 gap-6">

            @include('filament.user.pages.absensi.partials.modals.face')
            @include('filament.user.pages.absensi.partials.modals.cheat')
            @include('filament.user.pages.absensi.partials.modals.no-avatar')

            @include('filament.user.pages.absensi.partials.left-column')
            @include('filament.user.pages.absensi.partials.right-column')

            @include('filament.user.pages.absensi.partials.modals.early-checkout')
            @include('filament.user.pages.absensi.partials.alert-container')
        </div>

        @include('filament.user.pages.absensi.partials.scripts')
    </x-filament-panels::page>
</div>
