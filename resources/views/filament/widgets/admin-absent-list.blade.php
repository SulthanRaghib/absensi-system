@php
    $absent = $this->getAbsentRecords();
    $count  = $absent->count();

    $avatarPalette = [
        'bg' => ['#fde68a','#a7f3d0','#bfdbfe','#ddd6fe','#fbcfe8','#fed7aa','#ccfbf1','#e0e7ff','#fce7f3','#d1fae5'],
        'fg' => ['#92400e','#065f46','#1e40af','#5b21b6','#9d174d','#9a3412','#134e4a','#312e81','#831843','#064e3b'],
    ];
    $avatarIdx = fn(string $name) => ord(strtoupper($name)[0] ?? 'A') % count($avatarPalette['bg']);
@endphp

<x-filament-widgets::widget>
<style>
  .absent-scroll::-webkit-scrollbar        { width:5px; }
  .absent-scroll::-webkit-scrollbar-track  { background:transparent; }
  .absent-scroll::-webkit-scrollbar-thumb  { background:#e5e7eb; border-radius:99px; }
  .absent-scroll::-webkit-scrollbar-thumb:hover { background:#d1d5db; }
  .dark .absent-scroll::-webkit-scrollbar-thumb { background:#374151; }
  .dark .absent-scroll::-webkit-scrollbar-thumb:hover { background:#4b5563; }
  .absent-row { transition: background 0.14s; }
  .absent-row:hover { background: rgba(0,0,0,.025); }
</style>

<div class="rounded-2xl border dark:border-gray-700 overflow-hidden"
     style="background:#fff; border-color:#f1f5f9; box-shadow:0 1px 3px rgba(0,0,0,.06),0 0 0 1px rgba(0,0,0,.03);">

  {{-- ── Header ── --}}
  <div class="px-5 pt-4 pb-3 flex items-start justify-between gap-4"
       style="border-bottom:1px solid #f1f5f9;">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
           style="background:#fff1f2; border:1.5px solid #fecaca;">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
             stroke-width="1.8" stroke="#dc2626">
          <path stroke-linecap="round" stroke-linejoin="round"
                d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
        </svg>
      </div>
      <div>
        <p class="font-semibold text-gray-800 dark:text-gray-100 text-[15px] leading-snug">
          Pegawai Belum Absen
        </p>
        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
          Peserta Magang &nbsp;·&nbsp; {{ now()->translatedFormat('l, d M Y') }}
        </p>
      </div>
    </div>
    {{-- big count --}}
    <div class="flex-shrink-0 flex flex-col items-end">
      <span class="text-2xl font-extrabold leading-none text-red-500">{{ $count }}</span>
      <span class="text-[11px] text-gray-400 mt-0.5">orang</span>
    </div>
  </div>

  @if ($absent->isEmpty())
    <div class="py-10 flex flex-col items-center gap-3 text-center px-5">
      <div class="w-12 h-12 rounded-full bg-emerald-50 dark:bg-emerald-900/20 flex items-center justify-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-emerald-500" fill="none"
             viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round"
                d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
        </svg>
      </div>
      <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">Semua Sudah Absen! 🎉</p>
      <p class="text-xs text-gray-400">Semua peserta magang sudah absen hari ini.</p>
    </div>

  @else
    {{-- ── Scrollable list ── --}}
    <div class="absent-scroll overflow-y-auto divide-y dark:divide-gray-700/40"
         style="max-height:430px; border-color:#f9fafb;">
      @foreach ($absent as $idx => $r)
        @php
          $ai  = $avatarIdx($r->name);
          $ini = strtoupper(mb_substr($r->name, 0, 1));
        @endphp
        <div class="absent-row px-5 py-3 flex items-center gap-3">
          {{-- rank --}}
          <span class="w-4 flex-shrink-0 text-center text-[11px] font-bold text-gray-300 dark:text-gray-600 tabular-nums">
            {{ $idx + 1 }}
          </span>

          {{-- avatar --}}
          <div class="flex-shrink-0 w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold"
               style="background:{{ $avatarPalette['bg'][$ai] }}; color:{{ $avatarPalette['fg'][$ai] }};">
            {{ $ini }}
          </div>

          {{-- name --}}
          <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-gray-800 dark:text-gray-100 truncate leading-snug">
              {{ $r->name }}
            </p>
            @if (!empty($r->email))
              <p class="text-[11px] text-gray-400 dark:text-gray-500 truncate mt-0.5">{{ $r->email }}</p>
            @endif
          </div>

          {{-- status pill --}}
          <span class="flex-shrink-0 px-2 py-0.5 rounded-full text-[11px] font-semibold"
                style="background:#fff1f2; color:#dc2626; border:1px solid #fecaca;">
            Belum
          </span>
        </div>
      @endforeach
    </div>

    {{-- ── Footer ── --}}
    <div class="px-5 py-2.5 text-[11px] text-gray-400 flex items-center justify-between"
         style="border-top:1px solid #f1f5f9; background:#fafafa;">
      <span>Total tidak hadir: <strong class="text-gray-600 dark:text-gray-300">{{ $count }} pegawai</strong></span>
      <span class="text-red-400 font-semibold">● Perlu tindak lanjut</span>
    </div>
  @endif
</div>
</x-filament-widgets::widget>
