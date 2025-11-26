@php
    use Filament\Facades\Filament;
    $panel = Filament::getCurrentPanel();
    $brand = $panel?->getBrandName() ?? 'Maganghub - BAPETEN';
@endphp

<div style="text-align:center;margin-bottom:1rem;">
    <div style="font-weight:700;font-size:1rem;margin-bottom:0.25rem;">{{ $brand }}</div>
</div>
