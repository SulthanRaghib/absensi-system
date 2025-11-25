<div class="mt-4 text-center">
    <a href="{{ $home ?? route('home') }}"
        class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900" style="text-decoration:none;">
        {{-- Single, small inline SVG with explicit size to avoid relying on Tailwind utilities --}}
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" style="width:18px;height:18px;flex:0 0 18px;">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        <span style="font-size:0.9rem;line-height:1.2rem;color:inherit;">Kembali ke Beranda</span>
    </a>
</div>
