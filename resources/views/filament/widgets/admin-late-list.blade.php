@php
    $late = $this->getLateRecords();
    $schedule = $this->getScheduleInfo();
    $count = $late->count();

    /* Avatar palette — pick background by first letter charcode */
    $avatarPalette = [
        'bg' => [
            '#fde68a',
            '#a7f3d0',
            '#bfdbfe',
            '#ddd6fe',
            '#fbcfe8',
            '#fed7aa',
            '#ccfbf1',
            '#e0e7ff',
            '#fce7f3',
            '#d1fae5',
        ],
        'fg' => [
            '#92400e',
            '#065f46',
            '#1e40af',
            '#5b21b6',
            '#9d174d',
            '#9a3412',
            '#134e4a',
            '#312e81',
            '#831843',
            '#064e3b',
        ],
    ];
    $avatarIdx = fn(string $name) => ord(strtoupper($name)[0] ?? 'A') % count($avatarPalette['bg']);

    /* Severity config */
    $sConfig = [
        'low' => ['bg' => '#fef9c3', 'fg' => '#854d0e', 'border' => '#fef08a'],
        'medium' => ['bg' => '#ffedd5', 'fg' => '#9a3412', 'border' => '#fed7aa'],
        'high' => ['bg' => '#fee2e2', 'fg' => '#991b1b', 'border' => '#fca5a5'],
        'critical' => ['bg' => '#ffe4e6', 'fg' => '#881337', 'border' => '#fda4af'],
    ];

    $isRamadan = $schedule['is_ramadan'];
    $accentFg = $isRamadan ? '#d97706' : '#dc2626';
    $accentBg = $isRamadan ? '#fffbeb' : '#fff1f2';
    $accentBorder = $isRamadan ? '#fde68a' : '#fecaca';
@endphp

<x-filament-widgets::widget>
    <style>
        .late-scroll::-webkit-scrollbar {
            width: 5px;
        }

        .late-scroll::-webkit-scrollbar-track {
            background: transparent;
        }

        .late-scroll::-webkit-scrollbar-thumb {
            background: #e5e7eb;
            border-radius: 99px;
        }

        .late-scroll::-webkit-scrollbar-thumb:hover {
            background: #d1d5db;
        }

        .dark .late-scroll::-webkit-scrollbar-thumb {
            background: #374151;
        }

        .dark .late-scroll::-webkit-scrollbar-thumb:hover {
            background: #4b5563;
        }

        .late-row {
            transition: background 0.14s;
        }

        .late-row:hover {
            background: rgba(0, 0, 0, .025);
        }
    </style>

    <div class="rounded-2xl border dark:border-gray-700 overflow-hidden"
        style="background:#fff; border-color:#f1f5f9; box-shadow:0 1px 3px rgba(0,0,0,.06),0 0 0 1px rgba(0,0,0,.03);">

        {{-- ── Header ── --}}
        <div class="px-5 pt-4 pb-3 flex items-start justify-between gap-4" style="border-bottom:1px solid #f1f5f9;">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                    style="background:{{ $accentBg }}; border:1.5px solid {{ $accentBorder }};">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                        stroke-width="1.8" stroke="{{ $accentFg }}">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="font-semibold text-gray-800 dark:text-gray-100 text-[15px] leading-snug">
                            Pegawai Terlambat
                        </span>
                        @if ($isRamadan)
                            <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold"
                                style="background:#fef3c7; color:#92400e; border:1px solid #fde68a;">
                                🌙 Ramadan
                            </span>
                        @endif
                    </div>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                        Batas masuk:
                        <span class="font-bold" style="color:{{ $accentFg }};">{{ $schedule['jam_masuk'] }}</span>
                        &nbsp;·&nbsp;{{ now()->translatedFormat('l, d M Y') }}
                    </p>
                </div>
            </div>
            {{-- big count --}}
            <div class="flex-shrink-0 flex flex-col items-end">
                <span class="text-2xl font-extrabold leading-none"
                    style="color:{{ $accentFg }};">{{ $count }}</span>
                <span class="text-[11px] text-gray-400 mt-0.5">orang</span>
            </div>
        </div>

        {{-- ── Empty state ── --}}
        @if ($late->isEmpty())
            <div class="py-10 flex flex-col items-center gap-3 text-center px-5">
                <div
                    class="w-12 h-12 rounded-full bg-emerald-50 dark:bg-emerald-900/20 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-emerald-500" fill="none"
                        viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
                <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">Tepat Waktu Semua! 🎉</p>
                <p class="text-xs text-gray-400">Tidak ada pegawai terlambat hari ini.</p>
            </div>
        @else
            {{-- ── Severity legend ── --}}
            <div class="px-5 py-2 flex items-center gap-4 flex-wrap"
                style="background:#fafafa; border-bottom:1px solid #f8fafc;">
                @foreach (['low' => '≤5 mnt', 'medium' => '6–15 mnt', 'high' => '16–30 mnt', 'critical' => '>30 mnt'] as $s => $lbl)
                    @php $sc = $sConfig[$s]; @endphp
                    <span class="inline-flex items-center gap-1.5 text-[11px] font-medium"
                        style="color:{{ $sc['fg'] }};">
                        <span class="w-2 h-2 rounded-full"
                            style="background:{{ $sc['fg'] }};"></span>{{ $lbl }}
                    </span>
                @endforeach
            </div>

            {{-- ── Scrollable list ── --}}
            <div class="late-scroll overflow-y-auto divide-y dark:divide-gray-700/40"
                style="max-height:390px; divide-color:#f9fafb;">
                @foreach ($late as $idx => $r)
                    @php
                        $ai = $avatarIdx($r->name);
                        $sc = $sConfig[$r->severity];
                        $ini = strtoupper(mb_substr($r->name, 0, 1));
                    @endphp
                    <div class="late-row px-5 py-3 flex items-center gap-3">
                        {{-- rank --}}
                        <span
                            class="w-4 flex-shrink-0 text-center text-[11px] font-bold text-gray-300 dark:text-gray-600 tabular-nums">
                            {{ $idx + 1 }}
                        </span>

                        {{-- avatar --}}
                        <div class="flex-shrink-0 w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold"
                            style="background:{{ $avatarPalette['bg'][$ai] }}; color:{{ $avatarPalette['fg'][$ai] }};">
                            {{ $ini }}
                        </div>

                        {{-- name & threshold --}}
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-800 dark:text-gray-100 truncate leading-snug">
                                {{ $r->name }}
                            </p>
                            <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-0.5 leading-none">
                                @if ($r->is_ramadan)
                                    <span class="text-amber-400">🌙</span>
                                @endif
                                Batas {{ $r->threshold }}
                            </p>
                        </div>

                        {{-- time + severity pill --}}
                        <div class="flex-shrink-0 flex flex-col items-end gap-1">
                            <span class="text-sm font-bold tabular-nums leading-none"
                                style="color:{{ $sc['fg'] }};">
                                {{ $r->time }}
                            </span>
                            <span class="px-2 py-0.5 rounded-full text-[11px] font-bold"
                                style="background:{{ $sc['bg'] }}; color:{{ $sc['fg'] }}; border:1px solid {{ $sc['border'] }};">
                                +{{ $r->diff_min }}&thinsp;mnt
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- ── Footer summary ── --}}
            @php
                $crit = $late->where('severity', 'critical')->count();
                $maxLate = $late->max('diff_min');
                $avgLate = $late->count() ? round($late->avg('diff_min')) : 0;
            @endphp
            <div class="px-5 py-2.5 flex items-center justify-between text-[11px]"
                style="border-top:1px solid #f1f5f9; background:#fafafa;">
                <div class="flex items-center gap-4 text-gray-400">
                    <span>
                        Rata-rata: <strong class="text-gray-600 dark:text-gray-300">{{ $avgLate }} mnt</strong>
                    </span>
                    @if ($crit > 0)
                        <span class="font-semibold" style="color:#881337;">
                            ⚠ {{ $crit }} parah
                        </span>
                    @endif
                </div>
                <span class="text-gray-400">
                    Maks: <strong class="text-gray-600 dark:text-gray-300">{{ $maxLate }} mnt</strong>
                </span>
            </div>
        @endif
    </div>
</x-filament-widgets::widget>
