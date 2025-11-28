<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

# 📍 Sistem Absensi & Manajemen Kepegawaian (Auth Service Focus)

![Laravel](https://img.shields.io/badge/Laravel-v12-FF2D20?style=for-the-badge&logo=laravel)
![Filament](https://img.shields.io/badge/Filament-v4-F2C94C?style=for-the-badge&logo=laravel)
![Livewire](https://img.shields.io/badge/Livewire-v3-4E56A6?style=for-the-badge&logo=livewire)
![TailwindCSS](https://img.shields.io/badge/TailwindCSS-v3-38B2AC?style=for-the-badge&logo=tailwind-css)

> **Sistem Absensi Modern** yang terintegrasi dengan validasi Geolocation, dan dashboard admin yang _powerful_ menggunakan FilamentPHP.

---

## 📋 Daftar Isi

1. [Tentang Proyek](#-tentang-proyek)
2. [Fitur Unggulan](#-fitur-unggulan)
3. [Arsitektur & Teknologi](#-arsitektur--teknologi)
4. [Struktur Project](#-struktur-project)
5. [Layanan Autentikasi (Auth Service)](#-layanan-autentikasi-auth-service-deep-dive)
6. [Instalasi & Konfigurasi](#-instalasi--konfigurasi)

---

## 📖 Tentang Proyek

Aplikasi ini dirancang untuk mempermudah proses pencatatan kehadiran karyawan dengan validasi lokasi yang akurat. Dibangun di atas ekosistem **TALL Stack** (Tailwind, Alpine, Laravel, Livewire), sistem ini menawarkan performa tinggi dengan _load time_ yang cepat berkat implementasi SPA (Single Page Application) mode pada Filament.

Sistem ini memisahkan logika antara **Administrator** (Manajemen Data) dan **User/Karyawan** (Pencatatan Absensi) untuk menjaga keamanan dan kenyamanan penggunaan.

---

## 🚀 Fitur Unggulan

### 🔐 Authentication & Security

-   **Multi-Guard Login:** Pemisahan akses antara Admin Panel dan User Panel.
-   **Quick Attendance Mode (Absen Langsung):** Fitur unik di mana karyawan bisa melakukan absen cepat melalui modal popup tanpa perlu masuk ke dashboard penuh.
-   **Role-Based Access Control:** Manajemen hak akses berdasarkan jabatan (Admin, Staff, dll).

### 📍 Attendance & Tracking

-   **Geo-Location Validation:** Validasi koordinat GPS saat _Check-in_ dan _Check-out_ menggunakan `GeoLocationService`.
-   **Real-time Status:** Deteksi otomatis status "Terlambat" (Late) atau "Tepat Waktu".
-   **Export Data:** Kemampuan unduh laporan absensi ke format Excel/CSV secara efisien.

### 📊 Dashboard & Management

-   **Interactive Widgets:** Grafik statistik kehadiran 7 hari terakhir.
-   **User Management:** CRUD lengkap untuk data pengguna dan jabatan.
-   **Settings Management:** Konfigurasi global sistem yang dinamis.

---

## 🛠 Arsitektur & Teknologi

Project ini dibangun menggunakan teknologi mutakhir untuk menjamin skalabilitas:

| Komponen        | Teknologi         | Deskripsi                                                   |
| :-------------- | :---------------- | :---------------------------------------------------------- |
| **Framework**   | Laravel 12        | Core backend framework yang robust dan aman.                |
| **Admin Panel** | Filament 4        | Generator dashboard admin dan form builder.                 |
| **Frontend**    | Blade & Alpine.js | Templating engine dan interaktivitas ringan (modal, state). |
| **Styling**     | Tailwind CSS      | Utility-first CSS framework untuk desain responsif.         |
| **Database**    | MySQL             | Penyimpanan data relasional (User, Absensi, Jabatan).       |

---

## 📂 Struktur Project

Berikut adalah susunan direktori utama yang perlu dipahami pengembang:

```bash
├── app
│   ├── Filament
│   │   ├── Resources       # Logika CRUD (Absence, User, Jabatan)
│   │   ├── Widgets         # Komponen Statistik Dashboard
│   │   └── Exports         # Logika Export Excel (AbsenceExporter)
│   ├── Http
│   │   ├── Controllers     # Autentikasi & Logika Absensi Cepat
│   │   └── Middleware      # Validasi Request
│   ├── Models              # Eloquent Models (Absence, User, Setting)
│   └── Services            # Business Logic (GeoLocationService)
├── database
│   ├── migrations          # Skema Database
│   └── seeders             # Data Awal (Admin, Jabatan Dummy)
├── resources
│   └── views
│       ├── auth            # Tampilan Login & Choice Page
│       └── filament        # Custom View untuk Widget/Component
└── routes                  # Definisi Jalur URL (web.php, api.php)
```

---

## 🔐 Layanan Autentikasi (Auth Service) Deep Dive

Bagian ini menjelaskan alur unik autentikasi pada sistem ini yang terdapat pada `resources/views/auth/choice.blade.php`.

### 1\. Halaman Pilihan (Choice Page)

Sebelum masuk, pengguna disuguhkan halaman landing (`/`) yang memberikan opsi navigasi:

-   **Masuk sebagai Admin:** Mengarahkan ke `/admin/login`.
-   **Masuk sebagai Karyawan:** Mengarahkan ke `/app/login` (User Panel).
-   **Absen Langsung (Quick Action):** Tombol khusus untuk efisiensi.

### 2\. Fitur "Absen Langsung"

Fitur ini menggunakan **Alpine.js** dan **AJAX** untuk mempercepat proses.

-   **Mekanisme:** Saat tombol diklik, sebuah Modal Popup muncul.
-   **Input:** User memasukkan Email & Password.
-   **Proses:** Sistem memvalidasi kredensial di `AbsensiController`.
-   **Hasil:**
    -   _Sukses:_ Absensi tercatat, user diarahkan ke halaman sukses/ringkasan.
    -   _Gagal:_ Pesan error muncul di modal tanpa reload halaman.

### 3\. Panel Dashboard (Filament)

Sistem menggunakan `AdminPanelProvider` dan `UserPanelProvider` untuk memisahkan _scope_ akses. Ini memastikan karyawan tidak bisa mengakses menu konfigurasi sistem yang sensitif.

---

## ⚙️ Instalasi & Konfigurasi

Ikuti langkah berikut untuk menjalankan proyek di lingkungan lokal:

### Prasyarat

-   PHP \>= 8.2
-   Composer
-   Node.js & NPM
-   MySQL Server

### Langkah-langkah

1.  **Clone Repository**

    ```bash
    git clone [https://github.com/username/absensi-system.git](https://github.com/username/absensi-system.git)
    cd absensi-system
    ```

2.  **Install Dependencies**

    ```bash
    composer install
    npm install
    ```

3.  **Konfigurasi Environment**
    Salin file `.env.example` menjadi `.env` dan atur koneksi database:

    ```bash
    cp .env.example .env
    # Edit DB_DATABASE, DB_USERNAME, DB_PASSWORD di file .env
    ```

4.  **Generate Key & Migrate**

    ```bash
    php artisan key:generate
    php artisan migrate --seed
    ```

    _\> Perintah `--seed` akan membuat akun Admin default dan data Jabatan awal._

5.  **Build Aset Frontend**

    ```bash
    npm run build
    ```

6.  **Jalankan Server**

    ```bash
    php artisan serve
    ```

    Akses aplikasi di `http://127.0.0.1:8000`.

---

<p align="center">
Dibuat dengan ❤️ untuk efisiensi manajemen SDM.
</p>

---
