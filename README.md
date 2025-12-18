<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

# 📍 Sistem Absensi Pintar & Manajemen Kepegawaian (BAPETEN)

![Laravel](https://img.shields.io/badge/Laravel-v12-FF2D20?style=for-the-badge&logo=laravel)
![Filament](https://img.shields.io/badge/Filament-v4-F2C94C?style=for-the-badge&logo=filament)
![PWA](https://img.shields.io/badge/PWA-Ready-5A0FC8?style=for-the-badge&logo=pwa)
![FaceAPI](https://img.shields.io/badge/AI-Face%20Recognition-blue?style=for-the-badge&logo=google-lens)

> **Platform Absensi Generasi Berikutnya** yang menggabungkan Geolocation, Biometrik Wajah, dan Analisis Risiko Perangkat untuk mencegah kecurangan (anti-joki) dalam lingkungan kerja modern.

---

## 📋 Daftar Isi

1. [Tentang Sistem](#-tentang-sistem)
2. [Fitur Unggulan & Keamanan](#-fitur-unggulan--keamanan)
3. [Arsitektur & Teknologi](#-arsitektur--teknologi)
4. [Struktur Project](#-struktur-project)
5. [Prasyarat Sistem](#-prasyarat-sistem)
6. [Panduan Instalasi](#-panduan-instalasi)
7. [Alur Penggunaan (User Guide)](#-alur-penggunaan)

---

## 📖 Tentang Sistem

Aplikasi ini bukan sekadar pencatat jam masuk/pulang. Sistem ini dirancang sebagai **Progressive Web App (PWA)** yang berjalan di _Admin Panel_ dan _User Panel_ terpisah menggunakan ekosistem **FilamentPHP**.

Fokus utama sistem ini adalah **Validasi Berlapis** untuk memastikan integritas data kehadiran:

1.  **Dimana?** (Validasi Radius/GPS).
2.  **Siapa?** (Validasi Wajah Real-time).
3.  **Pakai Apa?** (Validasi Device ID & Risk Analysis).

---

## 🚀 Fitur Unggulan & Keamanan

### 🛡️ 1. Anti-Fraud & Risk Analysis (Logika Anti-Joki)

Sistem menggunakan algoritma _Risk-Based Authentication_ pada `AbsensiController`:

-   **Safe (Hijau):** Absen menggunakan perangkat pribadi yang terdaftar pertama kali.
-   **Warning (Kuning):** Perangkat pengguna dipinjam oleh orang lain untuk absen.
-   **Danger (Merah):** Pengguna terdeteksi melakukan "Joki" (menggunakan perangkat milik orang lain yang sudah terdaftar).
-   **Device History:** Melacak riwayat pemakaian perangkat di tabel `user_devices`.

### 📸 2. Smart Face Verification (AI Powered)

Menggunakan `face-api.js` di sisi klien untuk pengalaman yang cepat dan hemat server:

-   **Passive Liveness:** Fitur _"Hold Still"_ yang meminta pengguna tidak bergerak selama 1-2 detik sebelum otomatis mengambil gambar.
-   **Selfie Mode:** Tampilan kamera _mirrored_ (seperti cermin) agar natural saat digunakan.
-   **Dynamic Threshold:** Tingkat akurasi kecocokan wajah dapat diatur oleh Admin (Ketat/Longgar/Hanya Deteksi).

### 📱 3. User Experience (PWA)

-   **Installable:** Dapat diinstal di Android/iOS tanpa masuk App Store.
-   **Offline Support:** Halaman fallback cerdas saat internet putus.
-   **Interactive Dashboard:** Widget peringatan jika belum absen dan statistik kehadiran bulanan.

### 🛠 4. Admin Management

-   **Dynamic Settings:** Atur Radius (Meter), Jam Kerja, Toggle Validasi Wajah/Device secara _real-time_ tanpa menyentuh kodingan.
-   **Registration Links:** Generate QR Code/Link unik untuk pendaftaran massal peserta magang.
-   **Reporting:** Ekspor data kehadiran ke Excel/CSV dengan format rapi.

---

## 🛠 Arsitektur & Teknologi

Project ini dibangun dengan stack teknologi modern:

| Kategori                 | Teknologi                | Kegunaan                                      |
| :----------------------- | :----------------------- | :-------------------------------------------- |
| **Backend Framework**    | Laravel 11/12            | Core logic, routing, dan ORM.                 |
| **Admin/User Panel**     | FilamentPHP v3/v4        | Generator UI Dashboard yang elegan.           |
| **Frontend Interaction** | Alpine.js & Livewire     | Reaktivitas tanpa refresh halaman (SPA-like). |
| **Database**             | MySQL / MariaDB          | Penyimpanan data relasional.                  |
| **AI Library**           | Face-api.js (TensorFlow) | Deteksi dan pencocokan wajah di browser.      |
| **Styling**              | Tailwind CSS             | Utility-first CSS framework.                  |
| **PWA**                  | Silviolleite Laravel PWA | Service Worker & Manifest generator.          |

---

## 📂 Struktur Project

Peta direktori penting untuk memahami logika aplikasi:

```bash
├── app
│   ├── Filament
│   │   ├── Resources       # CRUD (Absence, User, UnitKerja)
│   │   ├── Pages           # Halaman Custom (Absensi.php, Profile.php)
│   │   └── Widgets         # Widget Dashboard & Statistik
│   ├── Http
│   │   ├── Controllers     # Logic Absensi (CheckIn/Out logic yang kompleks ada di sini)
│   │   └── Services        # GeoLocationService (Haversine Formula)
│   ├── Models              # User, Absence, UserDevice, Setting
├── public
│   ├── models              # File Model AI (.shard & .json) untuk Face API
│   └── images              # Aset Logo & Icon PWA
├── resources
│   └── views
│       └── filament
│           └── user
│               └── pages   # View Blade untuk Kamera & Peta (absensi.blade.php)
└── config
    └── laravelpwa.php      # Konfigurasi nama & warna aplikasi

```

---

## ⚙️ Prasyarat Sistem

Pastikan lingkungan server Anda memenuhi syarat berikut:

-   **PHP** >= 8.2 (Wajib, karena fitur Laravel modern).
-   **Extensions:** BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML.
-   **Database:** MySQL 8.0+ atau MariaDB 10.6+.
-   **Web Server:** Nginx atau Apache (dengan mod_rewrite).
-   **SSL (HTTPS):** **WAJIB** untuk fitur Kamera & GPS Browser.

---

## 📥 Panduan Instalasi

### 1. Clone & Setup Dependencies

```bash
git clone [https://github.com/username/absensi-system.git](https://github.com/username/absensi-system.git)
cd absensi-system

# Install PHP Packages
composer install

# Install JS Packages & Build Assets
npm install && npm run build

```

### 2. Konfigurasi Environment

Salin file `.env` dan atur database Anda.

```bash
cp .env.example .env
php artisan key:generate

```

_Edit file `.env`, sesuaikan `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, dan `APP_URL` (Pastikan HTTPS jika di production)._

### 3. Database & Seeding

```bash
php artisan migrate --seed

```

_Command ini akan membuat tabel dan mengisi data awal (Admin default, Setting default)._

### 4. Link Storage & PWA

```bash
php artisan storage:link
php artisan pwa:publish

```

### 5. Jalankan Server

```bash
php artisan serve

```

Akses di: `http://127.0.0.1:8000` (Gunakan Ngrok atau Valet jika butuh HTTPS di local untuk tes kamera).

---

## 📱 Alur Penggunaan

### Bagi Pengguna (Karyawan/Magang)

1. **Buka Aplikasi:** Login melalui halaman web atau PWA.
2. **Menu Absensi:** Klik menu "Absensi" di sidebar.
3. **Deteksi Wajah:**

-   Kamera akan terbuka otomatis (Mode Cermin).
-   Posisikan wajah di tengah layar.
-   Tunggu instruksi **"Tahan... Jangan Bergerak"**.
-   Sistem otomatis memotret jika wajah stabil.

4. **Cek Lokasi:** Jika wajah valid, sistem memverifikasi Radius Lokasi.
5. **Selesai:** Data tersimpan.

### Bagi Admin

1. **Dashboard:** Memantau grafik kehadiran harian.
2. **Laporan:** Masuk ke menu _Absences_ -> Filter Tanggal -> Export Excel.
3. **Pengaturan:** Masuk ke menu _Settings_ untuk mengubah:

-   Titik Koordinat Kantor.
-   Radius Maksimum (Meter).
-   Akurasi Wajah (Face Threshold).
-   Mengaktifkan/Mematikan Validasi Device ID.

---

<p align="center">
<b>Dibuat dengan ❤️ untuk efisiensi dan integritas data.</b>
</p>
