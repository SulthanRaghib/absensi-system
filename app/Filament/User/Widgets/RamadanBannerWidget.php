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

        // 100 Tips & Semangat Ramadan di kantor
        $mokelJokes = [
            ['emoji' => '👀', 'title' => 'CCTV Akhirat',          'text' => 'Mokel di kantin sudah dipantau CCTV kantor. Mokel di hati... Pak Atasan nggak tahu, tapi Yang Di Atas tahu.'],
            ['emoji' => '😴', 'title' => 'Ngantuk Bukan Alasan',   'text' => 'Ngantuk bukan alasan batal puasa. Tapi kalau ketiduran pas Zoom meeting... itu bisa jadi alasan kena tegur HRD.'],
            ['emoji' => '☕', 'title' => 'Kopi Bisa Nunggu',        'text' => 'Kopi bisa nunggu sampai Maghrib. Iman udah nungguin kamu dari Subuh. Siapa yang lebih penting?'],
            ['emoji' => '🍜', 'title' => 'Strategi Ngabuburit',    'text' => 'Tips ngabuburit produktif: selesaikan semua deadline sebelum azan Maghrib. Biar buka puasanya tenang tanpa WhatsApp berdering!'],
            ['emoji' => '💡', 'title' => 'Fakta Ilmiah',           'text' => 'Penelitian membuktikan: orang yang berpuasa 20% lebih fokus karena darah tidak sibuk mencerna makanan. Kamu sekarang mode Superman!'],
            ['emoji' => '🏆', 'title' => 'Challenge Diterima',     'text' => 'Tantangan hari ini: selesaikan semua pekerjaan, jangan mokel, dan senyum ke semua orang. Kalau berhasil, kamu berhak atas gorengan terenak saat buka!'],
            ['emoji' => '🧠', 'title' => 'Otak Lebih Cerdas',      'text' => 'Puasa terbukti meningkatkan neuroplastisitas otak. Artinya kamu sekarang sedang aktif jadi lebih pinter. Manfaatkan sekarang, bukan malah rebahan!'],
            ['emoji' => '📱', 'title' => 'HP Juga Puasa',          'text' => 'Kalau HP kamu bisa silent notifikasi media sosial selama puasa, kamu juga pasti bisa silent rasa lapar selama kerja. Coba deh!'],
            ['emoji' => '🎯', 'title' => 'Fokus Mode On',          'text' => 'Perut kosong = pikiran bersih = fokus maksimal. Ini saat yang paling tepat untuk selesaikan tugas yang selalu ditunda-tunda itu!'],
            ['emoji' => '🌙', 'title' => 'Pahala Berlipat',        'text' => 'Tiap langkah menuju kantor di bulan Ramadan nilainya berlipat ganda. Jadi absen telat itu rugi banget, sayang pahala!'],
            ['emoji' => '🍩', 'title' => 'Visual Donat Berbahaya', 'text' => 'Jika rekan kerjamu makan donat di depanmu, sabar. Itu ujian. Kalau kamu lolos, level imanmu naik satu tingkat. Achievement unlocked!'],
            ['emoji' => '💪', 'title' => 'Otot Sabar Dilatih',     'text' => 'Ramadan adalah gym untuk otot sabar. Tiap "nahan diri" dari ngeluh, dari gosip, dan dari makan — itu repsnya. Kamu sudah berapa reps hari ini?'],
            ['emoji' => '🕐', 'title' => 'Waktu Berjalan',         'text' => 'Satu jam di bulan Ramadan = satu jam penuh berkah. Jangan habiskan scrolling media sosial. Mending scroll spreadsheet kantor!'],
            ['emoji' => '🚀', 'title' => 'Produktivitas Turbo',    'text' => 'Mode puasa = mode hemat energi tubuh + turbo otak. Kamu ini kayak HP low-battery yang tiba-tiba performa-nya malah makin kencang!'],
            ['emoji' => '🎪', 'title' => 'Drama Kantin',           'text' => 'Saat rekan kerja bilang "eh kamu nggak makan?", jawab dengan anggun: "Lagi diet spiritual, bro. 30 hari program premium gratis!"'],
            ['emoji' => '📋', 'title' => 'To-Do List Berkah',      'text' => 'Coba tuliskan to-do list hari ini dengan niat ibadah. Bukan cuma target kerja, tapi target pahala. Centang satu per satu, insyaAllah!'],
            ['emoji' => '🛌', 'title' => 'Ancaman Terlelap',       'text' => 'Waspadai kursi kantor setelah Dzuhur. Empuk + AC + perut kosong = jebakan tidur tingkat dewa. Duduk tegak, minum air, tetap semangat!'],
            ['emoji' => '🌟', 'title' => 'Bintang Hari Ini',       'text' => 'Kamu sudah bangun sahur, kerja, dan puasa sekaligus. Itu bukan hal kecil. Kamu adalah MVP hari ini tanpa diragukan!'],
            ['emoji' => '🧩', 'title' => 'Puzzle Sabar',           'text' => 'Deadline menumpuk + perut lapar = puzzle kesabaran. Tapi ingat, puzzle yang sulit hadiahnya lebih worth it. Selesaikan pelan-pelan!'],
            ['emoji' => '🎵', 'title' => 'Lagu Semangat',          'text' => 'Sambil nunggu jam pulang, coba dendangkan: "Tinggal sedikit lagiii... deadline ini beres duluu..." Dijamin mood naik!'],
            ['emoji' => '🌈', 'title' => 'Pelangi Setelah Puasa',  'text' => 'Makan malam pertama setelah puasa itu rasanya 10x lebih nikmat. Jadi bayangin sekarang — itu motivasinya!'],
            ['emoji' => '🦸', 'title' => 'Superhero Kantoran',     'text' => 'Pekerjaan + puasa + tarawih semalam = kamu itu sudah level superhero. Bedanya, kamu nggak pakai jubah, tapi pakai ID card kantor.'],
            ['emoji' => '🗺️', 'title' => 'Peta Pahala',           'text' => 'Bayangkan setiap jam kerja hari ini sebagai satu titik di peta pahala Ramadan. Semakin produktif, semakin jauh petualangannya!'],
            ['emoji' => '🔋', 'title' => 'Recharge Spiritual',     'text' => 'Kamu lagi dalam proses recharge spiritual terpanjang dalam setahun. Jangan putus di tengah jalan. Setrum penuh sampai Idul Fitri!'],
            ['emoji' => '🎮', 'title' => 'Boss Level Kantor',      'text' => 'Hari ini ada boss level bernama "ngantuk jam 2 siang". Ada juga boss level "bau martabak dari kantin sebelah". Kalahkan keduanya!'],
            ['emoji' => '🤝', 'title' => 'Tim Solid',              'text' => 'Kalau timmu solid, puasa bareng jadi lebih ringan. Saling ingatkan, saling semangatin. Satu buka, semua senang!'],
            ['emoji' => '🌍', 'title' => 'Global Fasting Day',     'text' => 'Kamu nggak sendirian lapar. Lebih dari 1,8 miliar orang di seluruh dunia puasa bareng kamu hari ini. Solidaritas sejati!'],
            ['emoji' => '📊', 'title' => 'KPI Akhirat',            'text' => 'Kalau kantor punya KPI, akhirat juga punya. Ramadan adalah bulan assessment KPI akhirat. Pastikan nilaimu hijau semua!'],
            ['emoji' => '🎁', 'title' => 'Hadiah Tersembunyi',     'text' => 'Ada hadiah tersembunyi di tiap hari Ramadan: rasa syukur yang lebih dalam. Kamu nggak bakal dapat itu dari kantin manapun!'],
            ['emoji' => '🏃', 'title' => 'Maraton Ibadah',         'text' => 'Puasa itu maraton, bukan sprint. Jaga ritme, jaga niat, jaga mood. Kamu sudah berlari dengan baik hari ini!'],
            ['emoji' => '🔮', 'title' => 'Prediksi Masa Depan',    'text' => 'Aku prediksi masa depanmu: setelah Lebaran, kamu akan bernostalgia dengan semangat Ramadan ini. Jadi live the moment sekarang!'],
            ['emoji' => '🧘', 'title' => 'Inner Peace Activated',  'text' => 'Zoom meeting chaos + email bertubi + lapar = chaos². Tapi kamu yang puasa punya inner peace ekstra. Use that power wisely!'],
            ['emoji' => '🌺', 'title' => 'Bunga Amal',             'text' => 'Setiap kebaikan kecil di Ramadan tumbuh jadi bunga pahala. Senyum ke rekan kerja = satu bunga. Hari ini sudah berapa bunga kamu?'],
            ['emoji' => '🍕', 'title' => 'Pizza Itu Nyata',        'text' => 'Bayangkan pizza paling enak sedunia. Itu rasanya kalau kamu berhasil sabar sampai jam pulang dan langsung buka dengan makanan favoritmu!'],
            ['emoji' => '🎓', 'title' => 'Wisuda Sabar',           'text' => 'Setiap hari Ramadan yang berhasil kamu lalui = satu SKS di Universitas Kesabaran. Kamu sedang menuju gelar S1 Sabar Sejati!'],
            ['emoji' => '🏅', 'title' => 'Medali Absen Ontime',    'text' => 'Di Olimpiade Ramadan, nomor paling bergengsi adalah "Hadir Tepat Waktu". Kamu sudah ambil medali emas hari ini dengan masuk kantor!'],
            ['emoji' => '🌊', 'title' => 'Gelombang Berkah',       'text' => 'Ramadan itu kayak surfing. Ada gelombang besar berkah yang datang sekali setahun. Kamu yang siap bisa nge-ride sampai puncak!'],
            ['emoji' => '🐢', 'title' => 'Kura-Kura Bijak',        'text' => 'Jangan terburu-buru. Kura-kura menang bukan karena cepat, tapi karena konsisten. Puasa hari ini, besok, sampai tuntas!'],
            ['emoji' => '🦅', 'title' => 'Terbang Tinggi',         'text' => 'Burung elang terbang paling tinggi saat anginnya paling kencang. Tekanan kerja Ramadan ini justru yang akan terbangkanmu lebih tinggi!'],
            ['emoji' => '💎', 'title' => 'Berlian Karir',          'text' => 'Berlian terbentuk dari tekanan. Karyawan terbaik terbentuk saat deadline + puasa + meeting semuanya datang bersamaan. Kamu berlian itu!'],
            ['emoji' => '🌅', 'title' => 'Fajar Produktivitas',    'text' => 'Kamu sudah lihat fajar hari ini karena sahur. Itu privilege yang nggak semua orang punya. Manfaatkan energi fajar untuk produktivitas!'],
            ['emoji' => '📚', 'title' => 'Ilmu Baru Hari Ini',     'text' => 'Target Ramadan: pelajari satu hal baru setiap hari. Bisa skill kantor, bisa ayat Al-Qur\'an. Dua-duanya bernilai tinggi!'],
            ['emoji' => '🎭', 'title' => 'Aktor Profesional',      'text' => 'Cara berpura-pura nggak lapar di depan klien: senyum, bicara semangat, dan bayangkan foto makanan favorit di background mental kamu!'],
            ['emoji' => '🧲', 'title' => 'Magnet Rezeki',          'text' => 'Kerja ikhlas di bulan Ramadan itu kayak jadi magnet rezeki. Pahalanya nempel, berkahnya menyebar ke sekelilingmu juga!'],
            ['emoji' => '🎯', 'title' => 'Arrow of Focus',         'text' => 'Anak panah paling jauh adalah yang paling ditarik ke belakang. Rasa lapar hari ini adalah tarikan yang akan meluncurkanmu jauh ke depan!'],
            ['emoji' => '🍃', 'title' => 'Detox Total',            'text' => 'Puasa bukan cuma detox perut, tapi detox mulut dari gosip, detox tangan dari hal sia-sia, detox hati dari iri. Level upgrade!'],
            ['emoji' => '🔑', 'title' => 'Kunci Surga',            'text' => 'Kata ulama, amal pembuka pintu surga itu banyak. Salah satunya: kerja jujur dan tidak korupsi waktu. Kamu lagi jaga itu hari ini!'],
            ['emoji' => '🌙', 'title' => 'Malam Lebih Indah',      'text' => 'Setelah seharian puasa dan kerja, malam ini terasa lebih indah. Tarawih lebih khusyuk, tidur lebih nyenyak. Worth it banget!'],
            ['emoji' => '🎊', 'title' => 'Pesta Nanti Malam',      'text' => 'Sebentar lagi ada "pesta kecil" bernama buka puasa. Persiapkan dirimu dengan menyelesaikan semua tugas sebelum jam pulang!'],
            ['emoji' => '⚡', 'title' => 'Petir Semangat',         'text' => 'INGAT: kamu sudah menolak makan dan minum berjam-jam hari ini. Itu kekuatan mental luar biasa. Pake energi itu untuk kerja keras!'],
            ['emoji' => '🦁', 'title' => 'Singa Kantoran',         'text' => 'Singa tidak makan setiap hari, tapi tetap jadi raja hutan. Kamu puasa tapi tetap jadi yang terdepan di kantor hari ini!'],
            ['emoji' => '🌸', 'title' => 'Mekar di Ramadan',       'text' => 'Bunga mekar butuh waktu. Amal Ramadanmu sedang mekar perlahan setiap hari. Jangan berhenti sebelum penuh mekar!'],
            ['emoji' => '🏗️', 'title' => 'Bangun Fondasi',        'text' => 'Kebiasaan baik yang dibangun di Ramadan bisa jadi fondasi setahun ke depan. Bangun disiplinmu mulai dari absen tepat waktu!'],
            ['emoji' => '🎸', 'title' => 'Rock the Fasting',       'text' => 'Puasamu hari ini adalah konser semangat. Kamu gitarisnya. Jangan tuning dulu sampai Maghrib — langsung ROCK dari sekarang!'],
            ['emoji' => '🤖', 'title' => 'AI Tidak Bisa Puasa',    'text' => 'AI secanggih apapun tidak bisa puasa. Tapi kamu bisa! Itu artinya kamu punya keistimewaan yang tidak dimiliki teknologi manapun.'],
            ['emoji' => '🧪', 'title' => 'Eksperimen Diri',        'text' => 'Coba eksperimen: kerja tanpa ngemil hari ini, catat hasilnya. Spoiler: kamu akan terkejut betapa banyak yang bisa diselesaikan!'],
            ['emoji' => '🗓️', 'title' => 'Countdown Lebaran',     'text' => 'Lebaran semakin dekat! Tiap hari yang berhasil kamu lewati dengan semangat = satu langkah menuju garis finish yang penuh kemenangan!'],
            ['emoji' => '🌻', 'title' => 'Bunga Matahari',         'text' => 'Bunga matahari selalu menghadap matahari. Kamu pun selalu hadapkan niatmu ke Yang Maha Cahaya — insyaAllah jalan menjadi terang!'],
            ['emoji' => '🎲', 'title' => 'Dadu Nasib',             'text' => 'Nasib itu seperti dadu — kadang kita tidak bisa kontrol hasilnya. Tapi usaha dan niat ikhlas hari ini adalah cara terbaik throw the dice!'],
            ['emoji' => '🐝', 'title' => 'Lebah Pekerja Keras',    'text' => 'Lebah tidak libur bahkan di bulan panas sekalipun. Kamu yang puasa dan tetap kerja adalah versi manusia dari lebah itu. Salut!'],
            ['emoji' => '🌐', 'title' => 'Koneksi Spiritual',      'text' => 'WiFi bisa putus, listrik bisa padam, tapi koneksi antara doamu dan langit tidak pernah ada gangguan jaringan. Signal full terus!'],
            ['emoji' => '🏋️', 'title' => 'Angkat Beban Amal',    'text' => 'Di gym, semakin berat beban, semakin kuat ototnya. Di Ramadan, semakin berat ujiannya, semakin kuat imannya. Angkat terus!'],
            ['emoji' => '🎨', 'title' => 'Kanvas Amal',            'text' => 'Setiap hari Ramadan adalah kanvas putih. Gambarlah hari ini dengan warna produktivitas, kesabaran, dan keikhlasan!'],
            ['emoji' => '📡', 'title' => 'Frekuensi Berkah',       'text' => 'Di bulan Ramadan, frekuensi berkah sedang di level tertinggi. Pastikan antenna hatimu ter-tune dengan benar untuk menangkapnya!'],
            ['emoji' => '🌮', 'title' => 'Takjil Imajinasi',       'text' => 'Sambil kerja, boleh bayangkan: gorengan hangat, es teh manis, kurma segar... tapi baru boleh dimakan setelah azan Maghrib ya!'],
            ['emoji' => '🏄', 'title' => 'Surfing Deadline',       'text' => 'Deadline itu seperti ombak — bisa bikin tenggelam atau bisa kamu naiki. Pilih jadi surfer, bukan korban ombak!'],
            ['emoji' => '🎯', 'title' => 'Sniper Produktif',       'text' => 'Sniper paling akurat adalah yang nggak tergesa-gesa. Kamu hari ini: tenang, fokus, bidik target, selesaikan satu per satu!'],
            ['emoji' => '🌿', 'title' => 'Tanaman Sabar',          'text' => 'Pohon besar dimulai dari benih kecil. Kesabaranmu hari ini adalah benih yang akan tumbuh jadi pohon keteduhan di masa depan!'],
            ['emoji' => '💻', 'title' => 'Update Firmware Iman',   'text' => 'Ramadan adalah waktu update firmware iman kamu. Jangan skip update ini — bug-bug hati butuh di-patch setiap tahun!'],
            ['emoji' => '🎭', 'title' => 'Nggak Perlu Akting',     'text' => 'Di Ramadan, nggak perlu akting baik-baikan. Cukup jadi diri sendiri yang lebih sabar, lebih ikhlas, lebih positif. Simple!'],
            ['emoji' => '🌠', 'title' => 'Shooting Star Amal',     'text' => 'Bintang jatuh di Ramadan itu doamu. Pastikan kamu udah wishing yang bener: bukan cuma nanya kapan gajinya naik ya!'],
            ['emoji' => '🧊', 'title' => 'Cool Under Pressure',    'text' => 'Tetap cool meski lapar, ngantuk, dan deadline numpuk — itu skill langka yang nggak ada di job description manapun tapi sangat berharga!'],
            ['emoji' => '🎪', 'title' => 'Sirkus Kehidupan',       'text' => 'Hidup itu kadang sirkus. Tapi sulap terhebat adalah mengubah hari berpuasa jadi hari paling produktif dalam seminggu. Let\'s go!'],
            ['emoji' => '🔐', 'title' => 'Password Pahala',        'text' => 'Password surga Ramadan: sabar + ikhlas + syukur. Jangan lupa pakai kombinasi ketiganya sekarang juga!'],
            ['emoji' => '🛸', 'title' => 'Alien Heran',            'text' => 'Kalau alien turun dan lihat kamu kerja dalam keadaan lapar tanpa ngeluh, pasti mereka bingung: "Makhluk apa ini? Luar biasa!"'],
            ['emoji' => '🎠', 'title' => 'Roda Kehidupan',         'text' => 'Roda kehidupan terus berputar. Hari ini mungkin berat, tapi roda yang sama akan membawa kamu ke posisi lebih tinggi besok!'],
            ['emoji' => '🦋', 'title' => 'Metamorfosis Ramadan',   'text' => 'Ulat berjuang keras di dalam kepompong. Kamu juga sedang dalam proses metamorfosis jadi versi terbaik dirimu di Ramadan ini!'],
            ['emoji' => '🗝️', 'title' => 'Buka Pintu Rezeki',     'text' => 'Datang tepat waktu + kerja sungguh-sungguh + niat ikhlas = kombinasi kunci yang membuka pintu rezeki yang tidak terduga!'],
            ['emoji' => '🏰', 'title' => 'Kastil Pribadi',         'text' => 'Tiap hari Ramadan yang kamu lewati dengan baik menambah satu batu bata ke kastil pribadimu di akhirat. Terus bangun!'],
            ['emoji' => '🎺', 'title' => 'Terompet Semangat',      'text' => 'TUUUUT TUUUT — ini adalah suara terompet semangat virtualmu! Ayo bangkit dari kursi, regangkan badan, dan selesaikan tugas!'],
            ['emoji' => '🌊', 'title' => 'Banjir Pahala',          'text' => 'Di Ramadan, pahala mengalir deras kayak banjir. Yang membedakan adalah siapa yang membawa ember besar dan siapa yang tidak!'],
            ['emoji' => '🐉', 'title' => 'Naga Semangat',          'text' => 'Kata legenda, naga itu kuat bukan karena nggak lapar, tapi karena niatnya membara. Kamu hari ini adalah naga itu!'],
            ['emoji' => '🌏', 'title' => 'Peta Dunia Amal',        'text' => 'Bayangkan setiap kebaikan hari ini adalah pin di peta dunia amalmu. Kamu sedang menjelajahi benua mana sekarang?'],
            ['emoji' => '🔭', 'title' => 'Teleskop Masa Depan',    'text' => 'Lihat jauh ke depan: setelah Lebaran, kamu akan bangga dengan pilihan untuk tetap semangat dan produktif hari ini. Trust the process!'],
            ['emoji' => '🎪', 'title' => 'Atraksi Terhebat',       'text' => 'Atraksi terhebat hari ini bukan di sirkus manapun, tapi ada di dirimu: berpuasa sambil tetap profesional dan semangat kerja!'],
            ['emoji' => '🌴', 'title' => 'Pohon Kurma',            'text' => 'Pohon kurma butuh bertahun-tahun sebelum berbuah. Amalmu hari ini mungkin tidak langsung terlihat hasilnya, tapi akan berbuah indah!'],
            ['emoji' => '🎯', 'title' => 'Bulls-Eye Hari Ini',     'text' => 'Target hari ini: absen tepat waktu ✅, kerja fokus ✅, nggak mokel ✅, pulang dengan kepala tegak ✅. Kamu sudah bulls-eye!'],
            ['emoji' => '🧲', 'title' => 'Tarik Positif',          'text' => 'Energi positifmu hari ini menarik orang-orang baik ke sekitarmu. Rekan kerja yang lihat kamu semangat pasti ikut semangat juga!'],
            ['emoji' => '🎸', 'title' => 'Chord Kesabaran',        'text' => 'Chord paling indah dalam musik kehidupan adalah ketika sabar dan syukur dimainkan bersamaan. Tuningnya sedang sempurna hari ini!'],
            ['emoji' => '🌙', 'title' => 'Malam Lailatul Qadar',   'text' => 'Malam Lailatul Qadar lebih baik dari seribu bulan. Pastikan siang harimu di kantor juga tidak kalah berkualitasnya!'],
            ['emoji' => '🎁', 'title' => 'Kado Untuk Diri Sendiri', 'text' => 'Kado terbaik yang bisa kamu berikan untuk dirimu sendiri di Ramadan ini: komitmen untuk tidak telat absen sampai akhir bulan!'],
            ['emoji' => '🔥', 'title' => 'Api Semangat',           'text' => 'Api semangat tidak padam meski perut sedang kosong. Justru perut kosong membuat api itu menyala lebih terang. Burn bright!'],
            ['emoji' => '🎓', 'title' => 'Gelar Kehormatan',       'text' => 'Tidak ada universitas di dunia yang mengajarkan "Manajemen Emosi Saat Lapar di Tempat Kerja". Kamu dapat gelar honorary-nya hari ini!'],
            ['emoji' => '🐸', 'title' => 'Kodok Bijak',            'text' => 'Kata kodok bijak: "Jangan loncat sebelum waktunya." Nah, jangan pulang sebelum jam pulang ya. Sabar sebentar lagi!'],
            ['emoji' => '🌺', 'title' => 'Aroma Syukur',           'text' => 'Orang yang bersyukur memancarkan aroma positif yang tidak terlihat tapi terasa oleh semua yang ada di sekitarnya. Jadilah itu!'],
            ['emoji' => '🏆', 'title' => 'Piala Kemenangan',       'text' => 'Piala sejati Ramadan bukan trophy yang bisa dipegang, tapi perubahan karakter yang dibawa pulang setelah 30 hari. Build it!'],
            ['emoji' => '✨', 'title' => 'Sparkle Iman',           'text' => 'Ada sparkle khusus di mata orang yang berpuasa dengan ikhlas. Pastikan matamu bersinar dengan semangat hari ini!'],
            ['emoji' => '🌍', 'title' => 'Warisan Dunia',          'text' => 'Warisan terbaik bukan harta, tapi rekam jejak amal. Hari ini adalah halamanmu di buku amal. Tuliskan hal-hal yang membanggakan!'],
            ['emoji' => '🎵', 'title' => 'Nada Kehidupan',         'text' => 'Hidup punya not naik dan turun. Hari ini mungkin terasa panjang dan berat, tapi nada yang paling indah lahir dari perjuangan!'],
            ['emoji' => '🦋', 'title' => 'Terbang Bebas',          'text' => 'Kepompong terasa sempit dan menyesakkan. Tapi dari situlah sayap terbentuk. Tekanan hari ini sedang membentuk sayapmu!'],
            ['emoji' => '🌟', 'title' => 'Bintang Pagi',           'text' => 'Bintang paling terang tidak terlihat di siang hari, tapi dia tetap ada dan bersinar. Usahamu yang tidak dilihat orang — Allah lihat!'],
            ['emoji' => '🏖️', 'title' => 'Pantai Janji',          'text' => 'Bayangkan pantai liburan setelah Lebaran. Nah, untuk sampai ke sana, selesaikan dulu semua kerjaan hari ini tanpa menunda!'],
            ['emoji' => '🧩', 'title' => 'Keping Sempurna',        'text' => 'Kamu adalah keping puzzle yang tak tergantikan di tempat kerjamu. Kalau kamu malas, gambar besarnya tidak akan pernah lengkap!'],
            ['emoji' => '🚂', 'title' => 'Kereta Berkah',          'text' => 'Kereta berkah Ramadan sudah berangkat dari bulan ini. Pastikan kamu tidak ketinggalan dengan terus semangat sampai stasiun terakhir!'],
            ['emoji' => '💫', 'title' => 'Cahaya Amal',            'text' => 'Setiap amal baik memancarkan cahaya sendiri. Kamu hari ini sudah mengumpulkan berapa watt cahaya dengan kerja dan puasamu?'],
            ['emoji' => '🍀', 'title' => 'Lucky Clover Ramadan',   'text' => 'Orang bilang daun semanggi empat daun itu beruntung. Tapi berpuasa di bulan Ramadan sambil tetap produktif? Itu keberuntungan 10 lipat!'],
        ];

        // Pick quote of the day
        $dayIndex = (now()->day - 1) % count($quotes);
        $quoteOfDay = $quotes[$dayIndex];

        // Pick 3 random tips per day (seed by date for consistency, refreshed each day)
        srand(abs(crc32(now()->format('Y-m-d'))));
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

        // Hijri year via PHP intl extension (Islamic Civil calendar)
        $hijriCal  = \IntlCalendar::fromDateTime(now()->toDateTime(), 'en@calendar=islamic-civil');
        $hijriYear = $hijriCal->get(\IntlCalendar::FIELD_YEAR);

        return [
            'schedule'      => $schedule,
            'quoteOfDay'    => $quoteOfDay,
            'selectedJokes' => $selectedJokes,
            'iftarTime'     => $iftarCarbon->format('H:i'),
            'hoursLeft'     => $hoursLeft,
            'minsLeft'      => $minsLeft,
            'isBeforeIftar' => $minutesLeft > 0,
            'hijriYear'     => $hijriYear,
        ];
    }
}
