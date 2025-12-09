<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

# 📍 Sistem Absensi & Manajemen Pemagang BAPETEN

![Laravel](https://img.shields.io/badge/Laravel-v12-FF2D20?style=for-the-badge&logo=laravel)
![Filament](https://img.shields.io/badge/Filament-v4-F2C94C?style=for-the-badge&logo=laravel)
![PWA](https://img.shields.io/badge/PWA-Ready-5A0FC8?style=for-the-badge&logo=pwa)
![TailwindCSS](https://img.shields.io/badge/TailwindCSS-v3-38B2AC?style=for-the-badge&logo=tailwind-css)

> **Platform Absensi Modern** berbasis Geolocation dan validasi perangkat, dirancang khusus untuk mempermudah manajemen kehadiran pegawai dan peserta magang di lingkungan BAPETEN.

---

## 📋 Daftar Isi
1. [Tentang Sistem](#-tentang-sistem)
2. [Fitur Unggulan](#-fitur-unggulan)
3. [Teknologi & Arsitektur](#-teknologi--arsitektur)
4. [Struktur Project](#-struktur-project)
5. [Prasyarat Instalasi](#-prasyarat-instalasi)
6. [Panduan Instalasi & Setup](#-panduan-instalasi--setup)
7. [Penggunaan Aplikasi](#-penggunaan-aplikasi)

---

## 📖 Tentang Sistem

Aplikasi ini adalah solusi *end-to-end* untuk pencatatan kehadiran. Tidak seperti sistem absensi konvensional, aplikasi ini menggunakan **Panel Filament** ganda (Admin & User) dan teknologi **PWA (Progressive Web App)**, memungkinkan pengguna menginstal aplikasi langsung di HP mereka tanpa melalui App Store/Play Store.

Sistem dilengkapi dengan validasi ketat:
* **Geo-Fencing:** Memastikan user berada dalam radius kantor.
* **Device Fingerprinting:** Mencatat informasi perangkat yang digunakan.
* **Time Validation:** Validasi jam masuk/pulang sesuai setting dinamis.

---

## 🚀 Fitur Unggulan

### 🔐 Panel Autentikasi & Keamanan
* **Single Login Page:** Halaman login terpusat yang cerdas mengarahkan user sesuai Role (Admin -> Admin Panel, Staff/Magang -> User Panel).
* **Registration Links:** Fitur *Generate QR Code* atau Link unik dengan *expiration time* untuk pendaftaran massal peserta magang.
* **Role-Based Access Control (RBAC):** Pemisahan hak akses yang ketat.

### 📱 User Panel (Karyawan/Magang)
* **PWA Installable:** Dapat diinstal di Android/iOS layaknya aplikasi native.
* **Smart Attendance:** Tombol Check-In/Out hanya aktif jika GPS valid dan jam sesuai.
* **Alert System:** Widget peringatan di dashboard jika user lupa absen hari ini.
* **Riwayat Mandiri:** Tabel riwayat kehadiran pribadi yang mudah dipantau.

### 🛠 Admin Panel (Manajemen)
* **Dashboard Statistik:** Grafik kehadiran 7 hari terakhir, total telat, dan widget ringkasan *real-time*.
* **Manajemen Master Data:** CRUD lengkap untuk User, Jabatan, dan Unit Kerja.
* **Pengaturan Sistem (Settings):** Mengatur jam masuk, jam pulang (Senin-Kamis/Jumat), dan radius kantor secara dinamis tanpa koding.
* **Laporan & Ekspor:** Fitur ekspor data absensi bulanan ke format **CSV/Excel** dengan *styling* header otomatis.

### 🤖 Logika Validasi (Backend Logic)
* **GeoLocationService:** Menghitung jarak User vs Kantor menggunakan *Haversine Formula*.
* **Device Detector:** Menyimpan metadata browser dan OS user (`jenssegers/agent`).

---

## 🛠 Teknologi & Arsitektur

Project ini dibangun di atas ekosistem **TALL Stack** yang telah dimodernisasi:

| Komponen | Teknologi | Keterangan |
| :--- | :--- | :--- |
| **Framework** | Laravel 12 | Core backend framework (PHP 8.2+). |
| **Admin Panel** | Filament 4 | Generator dashboard admin, form, dan tabel. |
| **Database** | MySQL | Relational Database Management System. |
| **Frontend** | Blade & Livewire 3 | Interaktivitas reaktif tanpa menulis banyak JS. |
| **Styling** | Tailwind CSS | Utility-first CSS framework via Vite. |
| **PWA** | Laravel PWA | Dukungan Service Worker & Manifest JSON. |
| **Package Pendukung** | Spatie, Maatwebsite, Jenssegers | Handling permission, Excel export, & User Agent. |

---

## 📂 Struktur Project

Berikut adalah peta struktur folder penting untuk memahami alur kode:

```bash
├── app
│   ├── Filament
│   │   ├── Exports         # Logika Export Excel (AbsenceExporter)
│   │   ├── Imports         # Logika Import User Massal
│   │   ├── Resources       # CRUD Modules (Absence, User, Jabatan, UnitKerja)
│   │   ├── Widgets         # Komponen Dashboard (Chart, Stats, Warning Banner)
│   │   ├── AdminPanelProvider.php # Konfigurasi Panel Admin
│   │   └── UserPanelProvider.php  # Konfigurasi Panel User
│   ├── Http
│   │   ├── Controllers     # Custom Logic (Absensi, Auth, Export)
│   ├── Models              # Eloquent (Absence, User, Setting, RegistrationLink)
│   └── Services            # Business Logic (GeoLocationService)
├── config
│   ├── laravelpwa.php      # Konfigurasi Icon & Manifest PWA
│   └── filament.php        # Konfigurasi Global Filament
├── database
│   ├── migrations          # Schema Database
│   └── seeders             # Data Awal (Admin, Unit Kerja, Settings)
├── resources
│   └── views
│       ├── auth            # Tampilan Login Custom
│       ├── components      # Reusable Blade Components (SEO Head)
│       └── filament        # Custom View Widget/Pages
└── routes                  # Definisi Route (web.php)
````

-----

## ⚙️ Prasyarat Instalasi

Sebelum memulai, pastikan server atau komputer lokal Anda memiliki:

  * **PHP** \>= 8.2
  * **Composer** (Terbaru)
  * **Node.js** & **NPM** (Versi LTS, min v18)
  * **MySQL** Database

-----

## 📥 Panduan Instalasi & Setup

Ikuti langkah-langkah berikut untuk menjalankan aplikasi di *local environment*:

### 1\. Clone & Install Dependencies

```bash
git clone [https://github.com/username/absensi-system.git](https://github.com/username/absensi-system.git)
cd absensi-system

# Install PHP Dependencies
composer install

# Install Frontend Dependencies
npm install
```

### 2\. Konfigurasi Environment

Duplikat file `.env.example` dan sesuaikan koneksi database.

```bash
cp .env.example .env
php artisan key:generate
```

*Buka file `.env` dan atur `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`.*

### 3\. Database Migration & Seeding

Jalankan migrasi untuk membuat tabel dan mengisi data awal (Admin default, Unit Kerja, Jabatan).

```bash
php artisan migrate --seed
```

*\> **Catatan:** Seeder akan membuat Unit Kerja BAPETEN, Jabatan standar, dan Setting jam kerja default.*

### 4\. Build Aset Frontend (Vite)

```bash
npm run build
```

### 5\. Setup Storage Link & PWA

```bash
php artisan storage:link
php artisan pwa:publish
```

### 6\. Jalankan Server

```bash
php artisan serve
```

Akses aplikasi di: `http://127.0.0.1:8000`

-----

## 📱 Penggunaan Aplikasi

### Login Akun

  * **Admin:** Gunakan kredensial yang dibuat di seeder (atau buat manual via `tinker`).
  * **User/Pemagang:** Gunakan email/password yang didaftarkan atau scan QR Code pendaftaran.

### Cara Melakukan Absensi (User)

1.  Login ke panel aplikasi.
2.  Pastikan izin lokasi (GPS) di browser aktif.
3.  Klik menu **"Absensi"**.
4.  Peta akan memuat lokasi Anda. Jika dalam radius, tombol **Check-In** akan aktif.
5.  Klik tombol. Data lokasi dan perangkat akan tersimpan otomatis.

### Cara Export Laporan (Admin)

1.  Masuk ke **Admin Panel**.
2.  Buka menu **Absences**.
3.  Filter data berdasarkan tanggal/bulan (opsional).
4.  Klik tombol **Export** di header tabel.
5.  Pilih format (CSV/Excel) -\> Unduh.

-----

<p align="center">
<b>Dibuat dengan ❤️ dan ☕ menggunakan Laravel Filament.</b>
</p>
