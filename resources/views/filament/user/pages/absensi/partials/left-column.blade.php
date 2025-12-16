<!-- Left Column: Profile, Status, Actions -->
<div class="md:col-span-5 lg:col-span-4 space-y-6">

    <!-- Profile Section -->
    <div
        class="flex items-center space-x-4 bg-white p-4 rounded-3xl shadow-sm border border-gray-100 md:bg-transparent md:shadow-none md:border-0 md:p-0">
        <div class="relative">
            @if ($user->avatar_url)
                <img src="{{ asset('storage/' . $user->avatar_url) }}" alt="{{ $user->name }}"
                    class="w-16 h-16 rounded-2xl object-cover shadow-lg shadow-blue-500/30">
            @else
                <div
                    class="w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white text-2xl font-bold shadow-lg shadow-blue-500/30">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
            @endif
            <div class="absolute -bottom-1 -right-1 w-5 h-5 bg-green-500 border-2 border-white rounded-full">
            </div>
        </div>
        <div>
            <h1 class="text-xl font-bold text-gray-900 leading-tight">{{ $user->name }}</h1>
            <p class="text-sm text-gray-500 font-medium">{{ $user->email }}</p>
            <p class="text-xs text-blue-600 font-semibold mt-1 bg-blue-50 inline-block px-2 py-0.5 rounded-md">
                {{ now()->isoFormat('dddd, D MMMM Y') }}
            </p>
        </div>
    </div>

    <!-- Attendance Status Card -->
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Status Hari Ini</h2>
            @if ($todayAbsence?->jam_masuk && $todayAbsence?->jam_pulang)
                <span class="px-2 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full">Selesai</span>
            @elseif($todayAbsence?->jam_masuk)
                <span class="px-2 py-1 bg-blue-100 text-blue-700 text-xs font-bold rounded-full">Bekerja</span>
            @else
                <span class="px-2 py-1 bg-gray-100 text-gray-600 text-xs font-bold rounded-full">Belum Absen</span>
            @endif
        </div>

        <div class="grid grid-cols-2 gap-4">
            <!-- Check In Time -->
            <div
                class="bg-green-50/50 rounded-2xl p-4 border border-green-100 flex flex-col items-center justify-center text-center">
                <div class="w-8 h-8 rounded-full bg-green-100 text-green-600 flex items-center justify-center mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                    </svg>
                </div>
                <span class="text-xs text-gray-500 font-medium mb-0.5">Jam Masuk</span>
                <span x-ref="jamMasukDisplay" class="text-xl font-bold text-gray-900 tracking-tight">
                    {{ $todayAbsence?->jam_masuk ? $todayAbsence->jam_masuk->format('H:i') : '--:--' }}
                </span>
            </div>

            <!-- Check Out Time -->
            <div
                class="bg-orange-50/50 rounded-2xl p-4 border border-orange-100 flex flex-col items-center justify-center text-center">
                <div class="w-8 h-8 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </div>
                <span class="text-xs text-gray-500 font-medium mb-0.5">Jam Pulang</span>
                <span x-ref="jamPulangDisplay" class="text-xl font-bold text-gray-900 tracking-tight">
                    {{ $todayAbsence?->jam_pulang ? $todayAbsence->jam_pulang->format('H:i') : '--:--' }}
                </span>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="grid grid-cols-2 gap-4">
        <button @click="initiateCheckIn()"
            class="btn-action group relative w-full flex flex-col items-center justify-center p-4 rounded-2xl bg-gray-900 text-white shadow-lg shadow-gray-900/20 disabled:opacity-50 disabled:cursor-not-allowed disabled:shadow-none overflow-hidden"
            :disabled="!userLocation || isLoading || {{ $todayAbsence?->jam_masuk ? 'true' : 'false' }}">
            <div class="absolute inset-0 bg-white/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <div
                class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center mb-2 group-disabled:bg-white/10">
                <template x-if="isLoading && actionType === 'in'">
                    <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                </template>
                <template x-if="!isLoading || actionType !== 'in'">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                    </svg>
                </template>
            </div>
            <span class="font-semibold">Absen Masuk</span>
        </button>

        <button @click="performAttendance('out')"
            class="btn-action group relative w-full flex flex-col items-center justify-center p-4 rounded-2xl bg-white text-gray-900 border border-gray-200 shadow-sm hover:border-gray-300 disabled:opacity-50 disabled:cursor-not-allowed disabled:bg-gray-50"
            :disabled="!userLocation || isLoading ||
                {{ !$todayAbsence?->jam_masuk || $todayAbsence?->jam_pulang ? 'true' : 'false' }}">
            <div
                class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center mb-2 group-disabled:bg-gray-200">
                <template x-if="isLoading && actionType === 'out'">
                    <svg class="animate-spin h-5 w-5 text-gray-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                </template>
                <template x-if="!isLoading || actionType !== 'out'">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </template>
            </div>
            <span class="font-semibold">Absen Pulang</span>
        </button>
    </div>
</div>
