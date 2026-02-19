<?php

namespace App\Filament\User\Widgets;

use App\Services\AttendanceService;
use Filament\Widgets\Widget;

class RamadanBannerWidget extends Widget
{
    protected string $view = 'filament.user.widgets.ramadan-banner-widget';

    protected int | string | array $columnSpan = 'full';

    // Show before UserAttendanceAlert (which is -1)
    protected static ?int $sort = -2;

    public static function canView(): bool
    {
        // Skip weekends — no work, no banner needed
        if (now()->isWeekend()) {
            return false;
        }

        return (new AttendanceService)->getTodaySchedule()['is_ramadan'];
    }

    protected function getViewData(): array
    {
        $schedule = (new AttendanceService)->getTodaySchedule();

        // Rotating motivational quotes based on day of month (1-31)
        $quotes = [
            'Puasa bukan penghalang produktivitas. Yang hadir lebih awal hari ini, pahalanya double! 💪',
            'Ramadan bulan penuh berkah. Tiap ketikan keyboard di kantor bernilai ibadah! ⌨️✨',
            'Yang lapar pikirannya lebih jernih. Yang haus motivasinya makin tinggi. Kamu sudah punya keduanya! 🌙',
            'Semangat kerja di bulan Ramadan = investasi pahala terbaik. Nabung akhirat mulai dari absen tepat waktu! 🏦',
            'Perut kosong, semangat penuh! Buktikan kalau performa terbaik bisa dicapai bahkan saat berpuasa! 🔥',
            'Ramadan mengajarkan kita menahan diri. Termasuk menahan diri dari ngeluh soal deadline! 😄',
            'Lapar itu melatih fokus. Kamu sekarang sedang dalam mode ultra-fokus tanpa disadari! 🎯',
        ];

        // Funny "jangan mokel" messages for the modal
        $mokelJokes = [
            [
                'emoji' => '👀',
                'title' => 'CCTV Akhirat',
                'text'  => 'Mokel di kantin sudah dipantau CCTV kantor. Mokel di hati... Pak Atasan nggak tahu, tapi Yang Di Atas tahu.',
            ],
            [
                'emoji' => '😴',
                'title' => 'Ngantuk Bukan Alasan',
                'text'  => 'Ngantuk bukan alasan batal puasa. Tapi kalau ketiduran pas zoom meeting... itu bisa jadi alasan kena tegur HRD.',
            ],
            [
                'emoji' => '☕',
                'title' => 'Kopi Bisa Nunggu',
                'text'  => 'Kopi bisa nunggu sampai Maghrib. Iman udah nungguin kamu dari Subuh. Siapa yang lebih penting?',
            ],
            [
                'emoji' => '🍜',
                'title' => 'Strategi Ngabuburit',
                'text'  => 'Tips ngabuburit produktif: Selesaikan semua deadline sebelum azan Maghrib. Biar buka puasanya tenang tanpa WhatsApp berdering!',
            ],
            [
                'emoji' => '💡',
                'title' => 'Fakta Ilmiah',
                'text'  => 'Penelitian membuktikan: Orang yang berpuasa 20% lebih fokus karena darah tidak sibuk mencerna makanan. Jadi kamu sekarang sedang dalam mode Superman!',
            ],
            [
                'emoji' => '🏆',
                'title' => 'Challenge Diterima',
                'text'  => 'Tantangan hari ini: Selesaikan semua pekerjaan, jangan mokel, dan senyum ke semua orang. Kalau berhasil, kamu berhak atas gorengan terenak saat buka!',
            ],
        ];

        // Pick quote of the day
        $dayIndex = (now()->day - 1) % count($quotes);
        $quoteOfDay = $quotes[$dayIndex];

        // Pick 3 random jokes from the list (seed by date for consistency)
        srand(now()->dayOfYear);
        shuffle($mokelJokes);
        $selectedJokes = array_slice($mokelJokes, 0, 3);

        // Countdown to iftar (jam_pulang = proxy for end of work, near Maghrib)
        $iftarTimeStr = $schedule['jam_pulang']; // e.g. "15:30"
        $iftarCarbon  = \Carbon\Carbon::createFromFormat('H:i', $iftarTimeStr);
        $now          = now();

        $minutesLeft = $now->lt($iftarCarbon)
            ? (int) $now->diffInMinutes($iftarCarbon)
            : 0;

        $hoursLeft   = intdiv($minutesLeft, 60);
        $minsLeft    = $minutesLeft % 60;

        return [
            'schedule'      => $schedule,
            'quoteOfDay'    => $quoteOfDay,
            'selectedJokes' => $selectedJokes,
            'iftarTime'     => $iftarCarbon->format('H:i'),
            'hoursLeft'     => $hoursLeft,
            'minsLeft'      => $minsLeft,
            'isBeforeIftar' => $minutesLeft > 0,
        ];
    }
}
