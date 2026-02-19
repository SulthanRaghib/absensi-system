{{--
  AdminScheduleInfoWidget — beautiful full-width schedule card for admin dashboard.
  Two themes: blue/emerald (Normal) · amber/gold (Ramadan)
--}}

@php
    $isRamadan = $ramadanActive;
@endphp

<div x-data="{
    now: dayjs(),
    tick() { this.now = dayjs(); },
    init() { setInterval(() => this.tick(), 1000); },
    get clock() { return this.now.format('HH:mm:ss'); },
    get dateLabel() { return this.now.format('dddd, DD MMMM YYYY'); },
}" x-init="init()" class="relative w-full overflow-hidden rounded-2xl shadow-md border"
    @if ($isRamadan)
    style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 40%, #fbbf24 100%); border-color: #f59e0b;"
@else
    style="background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 40%, #bfdbfe 100%); border-color: #60a5fa;"
    @endif
    >
    {{-- ======== decorative circles ======== --}}
    <div class="absolute -top-10 -right-10 w-40 h-40 rounded-full opacity-10"
        @if ($isRamadan) style="background:#d97706;" @else style="background:#2563eb;" @endif></div>
    <div class="absolute -bottom-8 -left-8 w-28 h-28 rounded-full opacity-10"
        @if ($isRamadan) style="background:#b45309;" @else style="background:#1d4ed8;" @endif></div>

    <div class="relative z-10 p-5">

        {{-- ===== TOP ROW: title + status badge + live clock ===== --}}
        <div class="flex flex-wrap items-start justify-between gap-4 mb-5">

            {{-- Left: icon + title + day --}}
            <div class="flex items-center gap-3">
                @if ($isRamadan)
                    <div class="flex items-center justify-center w-12 h-12 rounded-xl text-2xl shadow-md"
                        style="background:rgba(217,119,6,0.15); border:2px solid rgba(217,119,6,0.3);">
                        🌙
                    </div>
                @else
                    <div class="flex items-center justify-center w-12 h-12 rounded-xl shadow-md"
                        style="background:rgba(37,99,235,0.12); border:2px solid rgba(37,99,235,0.25);">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.8" style="color:#2563eb;">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                @endif

                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h3 class="text-base font-bold"
                            @if ($isRamadan) style="color:#78350f;" @else style="color:#1e3a8a;" @endif>
                            Jadwal Kerja Hari Ini
                        </h3>
                        @if ($isRamadan)
                            <span
                                class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold"
                                style="background:#fef3c7; color:#92400e; border:1px solid #f59e0b;">
                                🌙 Mode Ramadan Aktif
                            </span>
                        @else
                            <span
                                class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold"
                                style="background:#dbeafe; color:#1d4ed8; border:1px solid #93c5fd;">
                                ✅ Jadwal Normal
                            </span>
                        @endif
                    </div>
                    <p class="text-sm mt-0.5"
                        @if ($isRamadan) style="color:#92400e;" @else style="color:#1d4ed8;" @endif>
                        {{ $hariBulan }}
                    </p>
                </div>
            </div>

            {{-- Right: Live clock + working status --}}
            <div class="flex flex-col items-end gap-1">
                <div class="font-mono text-2xl font-bold tabular-nums" x-text="clock"
                    @if ($isRamadan) style="color:#78350f;" @else style="color:#1e3a8a;" @endif>
                </div>

                @php
                    $statusStyles = match ($statusColor) {
                        'green' => 'background:#d1fae5; color:#065f46; border-color:#6ee7b7;',
                        'blue' => 'background:#dbeafe; color:#1e40af; border-color:#93c5fd;',
                        default => 'background:#f3f4f6; color:#374151; border-color:#d1d5db;',
                    };
                @endphp
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold border"
                    style="{{ $statusStyles }}">
                    @if ($statusColor === 'green')
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75"
                                style="background:#34d399;"></span>
                            <span class="relative inline-flex h-2 w-2 rounded-full" style="background:#10b981;"></span>
                        </span>
                    @elseif($statusColor === 'blue')
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                clip-rule="evenodd" />
                        </svg>
                    @else
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                                clip-rule="evenodd" />
                        </svg>
                    @endif
                    {{ $statusLabel }}
                </span>
            </div>

        </div>

        {{-- ===== SCHEDULE PILLS GRID ===== --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-{{ $isRamadan ? '4' : '4' }} gap-3">

            {{-- Jam Masuk --}}
            <div class="rounded-xl p-3.5 flex flex-col gap-1 shadow-sm"
                @if ($isRamadan) style="background:rgba(255,255,255,0.6); border:1px solid rgba(245,158,11,0.4);"
                 @else style="background:rgba(255,255,255,0.6); border:1px solid rgba(96,165,250,0.4);" @endif>
                <div class="text-xs font-medium uppercase tracking-wider"
                    @if ($isRamadan) style="color:#92400e;" @else style="color:#1d4ed8;" @endif>
                    ⏰ Jam Masuk
                </div>
                <div class="text-2xl font-bold tabular-nums"
                    @if ($isRamadan) style="color:#78350f;" @else style="color:#1e3a8a;" @endif>
                    {{ $schedule['jam_masuk'] }}
                </div>
                <div class="text-xs"
                    @if ($isRamadan) style="color:#b45309;" @else style="color:#3b82f6;" @endif>
                    Batas keterlambatan
                </div>
            </div>

            {{-- Jam Pulang (Senin–Kamis) --}}
            <div class="rounded-xl p-3.5 flex flex-col gap-1 shadow-sm"
                @if ($isRamadan) style="background:rgba(255,255,255,0.6); border:1px solid rgba(245,158,11,0.4);"
                 @else style="background:rgba(255,255,255,0.6); border:1px solid rgba(96,165,250,0.4);" @endif>
                <div class="text-xs font-medium uppercase tracking-wider"
                    @if ($isRamadan) style="color:#92400e;" @else style="color:#1d4ed8;" @endif>
                    🏠 Pulang (Sen–Kam)
                </div>
                <div class="text-2xl font-bold tabular-nums"
                    @if ($isRamadan) style="color:#78350f;" @else style="color:#1e3a8a;" @endif>
                    @if ($isRamadan)
                        {{ $schedule['jam_pulang'] }}
                    @else
                        {{ $default['jam_pulang'] }}
                    @endif
                </div>
                <div class="text-xs"
                    @if ($isRamadan) style="color:#b45309;" @else style="color:#3b82f6;" @endif>
                    Jam kerja berakhir
                </div>
            </div>

            {{-- Jam Pulang Jumat --}}
            @if (!$isRamadan)
                <div class="rounded-xl p-3.5 flex flex-col gap-1 shadow-sm"
                    style="background:rgba(255,255,255,0.6); border:1px solid rgba(96,165,250,0.4);">
                    <div class="text-xs font-medium uppercase tracking-wider" style="color:#1d4ed8;">
                        🕌 Pulang (Jum'at)
                    </div>
                    <div class="text-2xl font-bold tabular-nums" style="color:#1e3a8a;">
                        {{ $default['jam_pulang_jumat'] }}
                    </div>
                    <div class="text-xs" style="color:#3b82f6;">Jam pulang hari Jum'at</div>
                </div>
            @endif

            {{-- Today's effective pulang --}}
            <div class="rounded-xl p-3.5 flex flex-col gap-1 shadow-sm"
                @if ($isRamadan) style="background:rgba(255,255,255,0.7); border:2px solid rgba(217,119,6,0.5);"
                 @else style="background:rgba(255,255,255,0.7); border:2px solid rgba(37,99,235,0.4);" @endif>
                <div class="text-xs font-medium uppercase tracking-wider"
                    @if ($isRamadan) style="color:#92400e;" @else style="color:#1d4ed8;" @endif>
                    📌 Pulang Hari Ini
                    @if ($isFriday)
                        <span class="ml-1 text-xs normal-case font-normal">(Jum'at)</span>
                    @endif
                </div>
                <div class="text-2xl font-bold tabular-nums"
                    @if ($isRamadan) style="color:#78350f;" @else style="color:#1e3a8a;" @endif>
                    {{ $jamPulangDisplay }}
                </div>
                <div class="text-xs"
                    @if ($isRamadan) style="color:#b45309;" @else style="color:#3b82f6;" @endif>
                    Berlaku untuk hari ini
                </div>
            </div>

            {{-- Ramadan: period info pill --}}
            @if ($isRamadan && $ramadan['start_date'] && $ramadan['end_date'])
                <div class="rounded-xl p-3.5 flex flex-col gap-1 shadow-sm sm:col-span-3 lg:col-span-1"
                    style="background:rgba(255,255,255,0.6); border:1px solid rgba(245,158,11,0.5);">
                    <div class="text-xs font-medium uppercase tracking-wider" style="color:#92400e;">
                        📅 Periode Ramadan
                    </div>
                    <div class="text-sm font-bold" style="color:#78350f;">
                        {{ \Carbon\Carbon::parse($ramadan['start_date'])->translatedFormat('d M') }}
                        –
                        {{ \Carbon\Carbon::parse($ramadan['end_date'])->translatedFormat('d M Y') }}
                    </div>
                    @if ($daysRemaining !== null && $daysTotal !== null)
                        <div class="mt-1">
                            <div class="flex items-center justify-between text-xs mb-1" style="color:#b45309;">
                                <span>Sisa {{ $daysRemaining }} hari</span>
                                <span>{{ $daysTotal }} hari total</span>
                            </div>
                            @php $pct = $daysTotal > 0 ? max(0, min(100, round((($daysTotal - $daysRemaining) / $daysTotal) * 100))) : 0; @endphp
                            <div class="w-full rounded-full h-1.5" style="background:rgba(245,158,11,0.25);">
                                <div class="h-1.5 rounded-full transition-all"
                                    style="width:{{ $pct }}%; background:#f59e0b;"></div>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

        </div>

    </div>{{-- /relative z-10 --}}
</div>

@push('scripts')
    <script>
        if (typeof dayjs === 'undefined') {
            var s = document.createElement('script');
            s.src = 'https://cdn.jsdelivr.net/npm/dayjs@1/dayjs.min.js';
            document.head.appendChild(s);
            var s2 = document.createElement('script');
            s2.src = 'https://cdn.jsdelivr.net/npm/dayjs@1/locale/id.js';
            s2.onload = function() {
                if (window.dayjs) dayjs.locale('id');
            };
            document.head.appendChild(s2);
        } else {
            dayjs.locale('id');
        }
    </script>
@endpush
