<div align="center">

<img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="320" alt="Laravel Logo" />

<br/><br/>

# 📍 Sistem Absensi BAPETEN

**Platform Absensi Pintar & Manajemen Kepegawaian berbasis Web**

[![Laravel](https://img.shields.io/badge/Laravel-v12-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![Filament](https://img.shields.io/badge/Filament-v4-FDBA08?style=for-the-badge&logo=filament&logoColor=black)](https://filamentphp.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![PWA](https://img.shields.io/badge/PWA-Ready-5A0FC8?style=for-the-badge&logo=pwa&logoColor=white)](https://web.dev/progressive-web-apps/)
[![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind-CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)

> Sistem absensi modern yang menggabungkan **Geolocation GPS**, **Biometrik Wajah (AI)**, dan **Analisis Risiko Perangkat** untuk mencegah kecurangan _(anti-joki)_ di lingkungan kerja. Dibangun di atas **FilamentPHP v4** dengan dua panel terpisah: **Admin** & **User**.

</div>

---

## 📋 Daftar Isi

- [Tentang Sistem](#-tentang-sistem)
- [Arsitektur Sistem](#-arsitektur-sistem)
- [Fitur Lengkap](#-fitur-lengkap)
- [Stack Teknologi](#-stack-teknologi)
- [Struktur Direktori](#-struktur-direktori)
- [Skema Database](#-skema-database)
- [Prasyarat Instalasi](#-prasyarat-instalasi)
- [Panduan Instalasi](#-panduan-instalasi)
- [Modul Admin Panel](#-modul-admin-panel)
- [Modul User Panel](#-modul-user-panel)
- [Logika Keamanan & Anti-Fraud](#-logika-keamanan--anti-fraud)
- [Jadwal Ramadan](#-jadwal-ramadan)
- [Ekspor Laporan Excel](#-ekspor-laporan-excel)
- [PWA (Progressive Web App)](#-pwa-progressive-web-app)
- [CI/CD Pipeline](#-cicd-pipeline)
- [Rute Aplikasi](#-rute-aplikasi-utama)

---

## 🏢 Tentang Sistem

Sistem Absensi BAPETEN adalah aplikasi **Progressive Web App (PWA)** yang dirancang untuk mengelola kehadiran karyawan dan peserta magang secara digital dengan **validasi berlapis**. Bukan sekadar pencatat jam masuk/pulang, melainkan platform yang memastikan **integritas data kehadiran** melalui tiga lapis validasi:

| Layer | Pertanyaan        | Validasi                           | Teknologi                          |
| :---: | :---------------- | :--------------------------------- | :--------------------------------- |
| **1** | 📍 **Dimana?**    | Apakah user benar-benar di kantor? | GPS + Haversine Formula            |
| **2** | 👤 **Siapa?**     | Apakah benar orangnya?             | Face Recognition (face-api.js)     |
| **3** | 📱 **Pakai Apa?** | Apakah device milik sendiri?       | Device Fingerprinting + Risk Score |

---

## 🏗 Arsitektur Sistem

```
┌──────────────────────────────────────────────────────────────┐
│                       BROWSER / PWA                         │
│   ┌───────────────────┐      ┌────────────────────────────┐ │
│   │   User Panel      │      │      Admin Panel           │ │
│   │   /user/*         │      │      /admin/*              │ │
│   │   (Filament v4)   │      │      (Filament v4)         │ │
│   └────────┬──────────┘      └──────────┬─────────────────┘ │
└────────────┼─────────────────────────────┼───────────────────┘
             │                             │
┌────────────▼─────────────────────────────▼───────────────────┐
│                      LARAVEL 12 (Backend)                    │
│                                                              │
│  AbsensiController          AttendanceService                │
│  (CheckIn / CheckOut)       (Jadwal Normal / Ramadan)        │
│                                                              │
│  GeoLocationService         Setting Model                    │
│  (Haversine GPS)            (Dynamic Config Key-Value)       │
└──────────────────────────────────┬───────────────────────────┘
                                   │
                      ┌────────────▼────────────┐
                      │     MySQL Database      │
                      │ users · absences        │
                      │ settings · user_devices │
                      └─────────────────────────┘
```

---

## 🚀 Fitur Lengkap

### 👥 Manajemen Pengguna

- ✅ Multi-role: **Admin** dan **User** (Peserta Magang)
- ✅ Pendaftaran via **Link/Token unik** — onboarding massal peserta magang
- ✅ Foto profil pengguna (avatar) ditampilkan di widget dashboard
- ✅ Manajemen **Jabatan** (posisi/jabatan kerja)
- ✅ Manajemen **Unit Kerja** (divisi/departemen)

### ⏰ Absensi & Presensi

- ✅ **Check-in** (Absen Masuk) dan **Check-out** (Absen Pulang)
- ✅ Validasi GPS radius dari koordinat kantor (algoritma Haversine)
- ✅ Validasi akurasi GPS (minimal akurasi dalam meter)
- ✅ Deteksi perangkat otomatis (OS, Browser, versi, type, IP Address)
- ✅ **Koreksi Kehadiran** — permintaan koreksi dengan approval admin
- ✅ **Izin / Surat Keterangan** — rentang tanggal izin masuk ke laporan ekspor

### 📸 Face Recognition (AI)

- ✅ Deteksi wajah real-time menggunakan **face-api.js** (TensorFlow.js di browser)
- ✅ Mode kamera **cermin (mirrored)** — natural untuk selfie
- ✅ **Passive Liveness Detection** — ambil foto otomatis saat wajah stabil
- ✅ Threshold akurasi kecocokan wajah dapat diatur admin
- ✅ Foto absensi tersimpan di storage sebagai **audit trail**
- ✅ Opsional — dapat di-toggle ON/OFF oleh admin tanpa menyentuh kode

### 🛡️ Anti-Fraud & Risk Analysis

- ✅ Device fingerprinting (device unique ID dari browser)
- ✅ **Three-level risk system**: `safe` 🟢 / `warning` 🟡 / `danger` 🔴
- ✅ Penandaan otomatis jika device dipinjam untuk joki
- ✅ Riwayat kepemilikan device tersimpan di `user_devices`
- ✅ Dapat di-toggle ON/OFF oleh admin

### 📅 Jadwal Kerja Adaptif

- ✅ **Jadwal Normal** — Jam masuk/pulang Sen–Kam & Jumat dikonfigurasi admin
- ✅ **Jadwal Ramadan** — Jadwal khusus dengan rentang tanggal otomatis
- ✅ Sistem **otomatis beralih** ke jadwal Ramadan saat tanggal sesuai
- ✅ **Immutable snapshot** — threshold & status Ramadan diabadikan per-record

### 📊 Dashboard Admin

- ✅ **Schedule Info Widget** — jadwal hari ini (Normal/Ramadan) full-width live clock
- ✅ **Stats Overview** — 6 stat cards (Total User, Mentor, Peserta, Hadir, Tidak Hadir, Tepat Waktu)
- ✅ **Pegawai Terlambat** — list scrollable, sorted by worst, severity heatmap 4 level
- ✅ **Pegawai Belum Absen** — list scrollable dengan foto profil / inisial berwarna
- ✅ **Chart 7 Hari** — grafik bar kehadiran mingguan

### 📁 Laporan & Ekspor

- ✅ Ekspor **Excel** bulanan dengan format per-hari berwarna
- ✅ Threshold Ramadan otomatis per hari pada kolom export
- ✅ Kolom izin (approved) ikut terhitung

### ⚙️ Pengaturan Sistem Dinamis

- ✅ Koordinat & radius kantor (GPS)
- ✅ Toggle GPS / Face Recognition / Device Validation
- ✅ Jadwal jam kerja biasa (masuk, pulang, pulang Jumat)
- ✅ Jadwal Ramadan (tanggal dan jam masuk/pulang)

### 🌐 SEO & PWA

- ✅ Installable di Android/iOS sebagai PWA
- ✅ Service Worker dengan offline fallback page
- ✅ Sitemap.xml otomatis (Spatie)
- ✅ robots.txt dinamis

---

## 🛠 Stack Teknologi

| Kategori               | Teknologi               |  Versi   | Keterangan                    |
| :--------------------- | :---------------------- | :------: | :---------------------------- |
| **Backend**            | Laravel                 |  `12.x`  | Core framework                |
| **Admin & User Panel** | Filament PHP            |  `4.x`   | Dual panel (Admin + User)     |
| **Database**           | MySQL / MariaDB         |  `8.0+`  | Relational database           |
| **Frontend Reactive**  | Alpine.js + Livewire    | bundled  | Reaktivitas tanpa full reload |
| **CSS Framework**      | Tailwind CSS            |  `4.x`   | Utility-first via Filament    |
| **Bundler**            | Vite                    | `latest` | Asset bundling + HMR          |
| **AI Face Library**    | face-api.js             | `latest` | TensorFlow.js di browser      |
| **Excel Export**       | Maatwebsite/Excel       |  `3.1`   | PhpSpreadsheet wrapper        |
| **Device Detection**   | Jenssegers/Agent        |  `2.6`   | User-agent parsing            |
| **PWA**                | silviolleite/laravelpwa |  `2.0`   | Manifest + Service Worker     |
| **Sitemap**            | spatie/laravel-sitemap  |  `7.3`   | Auto-generate sitemap.xml     |
| **PHP**                | PHP                     |  `8.2+`  | Minimum version required      |

---

## 📂 Struktur Direktori

```
absensi-system/
│
├── 📁 app/
│   ├── 📁 Exports/
│   │   └── AttendanceExport.php          # Excel export: format berwarna + threshold Ramadan
│   │
│   ├── 📁 Filament/
│   │   ├── 📁 Resources/                 # CRUD Admin
│   │   │   ├── Absences/                 # Data absensi
│   │   │   ├── AttendanceCorrections/    # Koreksi kehadiran
│   │   │   ├── Jabatans/                 # Jabatan/posisi
│   │   │   ├── Permissions/              # Izin pegawai
│   │   │   ├── RegistrationLinks/        # Link daftar peserta
│   │   │   ├── Settings/                 # Pengaturan + halaman jadwal
│   │   │   ├── UnitKerjas/               # Unit kerja/divisi
│   │   │   └── Users/                    # Manajemen pengguna
│   │   │
│   │   ├── 📁 User/                      # Panel khusus user
│   │   │   ├── Pages/                    # Absensi, profil, riwayat
│   │   │   ├── Resources/                # MyAbsences (riwayat absensi)
│   │   │   └── Widgets/                  # Widget dashboard user
│   │   │
│   │   ├── 📁 Widgets/                   # Widget dashboard admin
│   │   │   ├── AdminScheduleInfoWidget.php     # Jadwal hari ini (full-width)
│   │   │   ├── AdminAttendanceStats.php        # 6 stat cards
│   │   │   ├── AdminLateListWidget.php         # List terlambat + severity
│   │   │   ├── AdminAbsentListWidget.php       # List belum absen
│   │   │   └── AdminLast7Chart.php             # Chart 7 hari
│   │   │
│   │   └── 📁 Pages/
│   │       └── Auth/                     # Login, register intern
│   │
│   ├── 📁 Http/
│   │   ├── 📁 Controllers/
│   │   │   ├── AbsensiController.php          # ⭐ Core: checkIn + checkOut
│   │   │   ├── DirectAttendanceController.php
│   │   │   ├── AbsenceExportController.php    # Trigger ekspor Excel
│   │   │   └── AuthController.php
│   │   └── 📁 Requests/
│   │
│   ├── 📁 Models/
│   │   ├── User.php              # HasAvatar, FilamentUser, role
│   │   ├── Absence.php           # jam_masuk/pulang, GPS, risk_level, snapshot
│   │   ├── Setting.php           # Dynamic key-value config store
│   │   ├── Jabatan.php
│   │   ├── UnitKerja.php
│   │   ├── RegistrationLink.php
│   │   └── UserDevice.php        # Device fingerprint history
│   │
│   ├── 📁 Services/
│   │   ├── AttendanceService.php       # ⭐ Normal vs Ramadan schedule logic
│   │   └── GeoLocationService.php     # ⭐ Haversine distance + GPS validation
│   │
│   └── 📁 Providers/Filament/
│       ├── AdminPanelProvider.php      # Konfigurasi panel /admin
│       └── UserPanelProvider.php       # Konfigurasi panel /user
│
├── 📁 database/
│   ├── 📁 migrations/                  # 27 migration files
│   └── 📁 seeders/
│
├── 📁 resources/
│   └── 📁 views/
│       ├── 📁 absensi/                 # Halaman absensi (kamera + peta)
│       └── 📁 filament/widgets/        # Blade custom widget admin & user
│
├── 📁 public/
│   ├── 📁 models/                      # Model AI face-api.js (.json + .shard)
│   └── 📁 images/icons/               # Icon PWA (72px – 512px)
│
├── 📁 config/
│   └── laravelpwa.php                  # Manifest PWA
│
├── vercel.json                         # Deployment Vercel (serverless PHP)
└── CICD.md                             # Dokumentasi GitHub Actions CI/CD
```

---

## 🗄 Skema Database

### Tabel Inti

```sql
-- Pengguna sistem
users
  id, name, email, password, role ('admin'|'user'),
  jabatan_id, unit_kerja_id, avatar_url, registered_device_id

-- Data kehadiran harian
absences
  id, user_id, tanggal, jam_masuk, jam_pulang,
  schedule_jam_masuk,   -- Snapshot threshold saat check-in (tidak berubah)
  is_ramadan,           -- Snapshot mode Ramadan saat check-in (tidak berubah)
  lat_masuk, lng_masuk, lat_pulang, lng_pulang,
  distance_masuk, distance_pulang,
  device_info, capture_image,
  risk_level ('safe'|'warning'|'danger')

-- Konfigurasi dinamis (key-value store)
settings
  id, key, value, type ('string'|'number'|'boolean'|'date'|'time'|'json'), description

  -- Keys yang didukung sistem:
  office_latitude, office_longitude, office_radius,
  radius_enabled, face_recognition_enabled, device_validation_enabled,
  default_jam_masuk, default_jam_pulang, default_jam_pulang_jumat,
  ramadan_start_date, ramadan_end_date, ramadan_jam_masuk, ramadan_jam_pulang,
  ramadan_jam_pulang_jumat  -- Jam pulang khusus Jumat selama Ramadan (misal 15:30)

-- Riwayat device fingerprint
user_devices
  id, user_id, device_unique_id, last_used_at

-- Permintaan koreksi absen
attendance_corrections
  id, user_id, absence_id, reason, status ('pending'|'approved'|'rejected'), admin_note

-- Izin / surat keterangan
permissions
  id, user_id, type, start_date, end_date, status, note

-- Link pendaftaran peserta
registration_links
  id, token, expires_at, usage_limit, used_count
```

---

## ⚙️ Prasyarat Instalasi

| Kebutuhan       |      Versi Minimum      | Catatan                                        |
| :-------------- | :---------------------: | :--------------------------------------------- |
| PHP             |        **8.2+**         | Wajib untuk Laravel 12                         |
| MySQL / MariaDB |    **8.0+ / 10.6+**     |                                                |
| Composer        |         **2.x**         | PHP package manager                            |
| Node.js         |         **18+**         | Untuk build asset Vite                         |
| NPM             |         **9+**          | Bundled dengan Node.js                         |
| Web Server      |     Nginx / Apache      | Apache: aktifkan `mod_rewrite`                 |
| SSL / HTTPS     | ⚠️ **WAJIB** production | Browser hanya beri akses Kamera & GPS di HTTPS |

**PHP Extensions yang dibutuhkan:**
`BCMath` · `Ctype` · `Fileinfo` · `GD` · `JSON` · `Mbstring` · `OpenSSL` · `PDO` · `PDO_MySQL` · `Tokenizer` · `XML` · `Zip`

---

## 📥 Panduan Instalasi

### 1 — Clone Repository

```bash
git clone https://github.com/username/absensi-system.git
cd absensi-system
```

### 2 — Install Dependencies

```bash
# PHP packages
composer install

# JS packages
npm install
```

### 3 — Konfigurasi Environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit file `.env`:

```ini
APP_NAME="Sistem Absensi BAPETEN"
APP_URL=https://yourdomain.com    # WAJIB HTTPS untuk GPS & Kamera di production

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=absensi_bapeten
DB_USERNAME=root
DB_PASSWORD=your_password

FILESYSTEM_DISK=public            # Untuk storage foto absensi
```

### 4 — Migrasi & Seeder Database

```bash
php artisan migrate --seed
```

> Seeder membuat akun **Admin default** dan mengisi **setting awal** (jadwal, koordinat kantor).

### 5 — Link Storage & Publish PWA

```bash
php artisan storage:link
php artisan pwa:publish
```

### 6 — Build Frontend Assets

```bash
# Development (watch mode)
npm run dev

# Production build
npm run build
```

### 7 — Jalankan Aplikasi

```bash
php artisan serve
# Akses: http://127.0.0.1:8000
```

> **Catatan Local Development**: Untuk tes fitur Kamera & GPS, gunakan [Ngrok](https://ngrok.com) atau [Laravel Valet](https://laravel.com/docs/valet) agar mendapat HTTPS.

### ⚡ Quick Setup (Satu Command)

```bash
composer run setup
```

Menjalankan secara berurutan: `composer install` → `key:generate` → `migrate` → `npm install` → `npm run build`

---

## 🖥 Modul Admin Panel

Akses di: **`/admin`** — hanya dapat diakses oleh pengguna dengan `role = admin`.

---

### 📊 Dashboard Admin

Widget tersusun dari atas ke bawah dalam urutan berikut:

#### 🗓️ 1. Jadwal Hari Ini (`AdminScheduleInfoWidget`)

Card **full-width** yang menampilkan jadwal aktif saat ini secara visual dan informatif:

| Kondisi               | Tampilan                                   |  Warna   |
| :-------------------- | :----------------------------------------- | :------: |
| **Jadwal Normal**     | Jam masuk, pulang Sen–Kam, pulang hari ini | 🔵 Biru  |
| **Jadwal Ramadan** 🌙 | Jadwal Ramadan + progress bar sisa hari    | 🟡 Amber |

- ⏰ **Jam digital live** — update setiap detik (Alpine.js)
- Badge status dinamis: `Belum Jam Masuk` · `Jam Kerja Aktif` · `Sudah Jam Pulang`
- Info sisa hari Ramadan dengan progress bar

#### 📈 2. Stats Overview (`AdminAttendanceStats`)

Enam stat card dalam layout 3 kolom grid:

| Stat Card             | Data yang Ditampilkan                 |       Warna       |
| :-------------------- | :------------------------------------ | :---------------: |
| Total Pengguna        | Semua user terdaftar                  |    🔵 Primary     |
| Total Mentor          | User jabatan mengandung "mentor"      |   🟣 Secondary    |
| Total Peserta Magang  | User dengan `role = user`             |      ⚫ Gray      |
| Hadir Hari Ini        | Check-in hari ini + mini chart 7 hari |    🟢 Success     |
| Tidak Hadir (Peserta) | Peserta belum absen hari ini          |     🔴 Danger     |
| Tepat Waktu Hari Ini  | Hadir ≤ batas jadwal + mini chart     | 🟢/🟡 Kondisional |

#### ⏰ 3. Pegawai Terlambat (`AdminLateListWidget`)

List scrollable (maks 390px) dengan:

- Diurutkan dari **paling terlambat** (descending)
- **Severity heatmap** 4 level berdasarkan menit keterlambatan:

    | Level      | Keterlambatan |   Warna   |
    | :--------- | :-----------: | :-------: |
    | `low`      |   ≤ 5 menit   | 🟡 Kuning |
    | `medium`   |  6–15 menit   | 🟠 Oranye |
    | `high`     |  16–30 menit  | 🔴 Merah  |
    | `critical` |  > 30 menit   | 🩸 Marun  |

- Avatar **foto profil** (jika tersimpan) atau **inisial berwarna** (fallback)
- Badge `+N mnt` per baris, badge 🌙 Ramadan jika jadwal Ramadan aktif
- **Footer statistik**: rata-rata keterlambatan · jumlah critical · maks keterlambatan

#### 🙅 4. Pegawai Belum Absen (`AdminAbsentListWidget`)

List scrollable peserta magang yang **belum check-in** hari ini:

- Avatar foto profil / inisial berwarna
- Nomor urut per baris
- Email ditampilkan sebagai info tambahan
- Footer: total tidak hadir + indikator tindak lanjut

#### 📉 5. Chart 7 Hari (`AdminLast7Chart`)

Grafik **bar chart** kehadiran 7 hari terakhir (full-width).

---

### 📁 Resource Management Admin

#### 👥 Manajemen Pengguna (`/admin/users`)

- CRUD lengkap data pengguna
- Set role: `admin` / `user`
- Assign Jabatan & Unit Kerja
- Reset password, upload foto profil

#### 📋 Data Absensi (`/admin/absences`)

- Lihat semua record absen dengan filter tanggal, user, status
- Indikator risiko device: Safe 🟢 / Warning 🟡 / Danger 🔴
- Lihat foto selfie absensi (capture_image)
- Koordinat GPS + jarak dari kantor
- Kolom `is_ramadan` & `schedule_jam_masuk` untuk audit trail

#### ✏️ Koreksi Kehadiran (`/admin/attendance-corrections`)

- Review permintaan koreksi dari user
- Approve / Reject + catatan admin

#### 🏢 Jabatan (`/admin/jabatans`) & Unit Kerja (`/admin/unit-kerjas`)

- CRUD jabatan/posisi dan divisi/departemen

#### 📄 Izin/Perizinan (`/admin/permissions`)

- Approve/reject surat izin pegawai
- Data izin diintegrasikan ke laporan ekspor Excel

#### 🔗 Registration Links (`/admin/registration-links`)

- Generate token link pendaftaran unik
- Set batas penggunaan & tanggal kadaluarsa
- Bagikan ke peserta magang baru via QR/link

#### ⚙️ Settings (`/admin/settings`)

**Jadwal Jam Kerja Biasa** (`/admin/settings/jadwal-biasa`):

- Jam Masuk, Jam Pulang Sen–Kam, Jam Pulang Jumat

**Pengaturan Ramadan** (`/admin/settings/ramadan-settings`):

- Tanggal mulai & selesai Ramadan
- Jam Masuk & Jam Pulang khusus Ramadan (Sen–Kam)
- **Jam Pulang Ramadan Khusus Jumat** — default `15:30` (kosongkan = sama dengan Sen–Kam)

**Setting Umum** (via resource Settings):

| Key                         |   Type    | Keterangan                   |
| :-------------------------- | :-------: | :--------------------------- |
| `office_latitude`           | `number`  | Koordinat kantor — lintang   |
| `office_longitude`          | `number`  | Koordinat kantor — bujur     |
| `office_radius`             | `number`  | Radius toleransi GPS (meter) |
| `radius_enabled`            | `boolean` | ON/OFF validasi radius GPS   |
| `face_recognition_enabled`  | `boolean` | ON/OFF verifikasi wajah      |
| `device_validation_enabled` | `boolean` | ON/OFF validasi device ID    |

---

## 📱 Modul User Panel

Akses di: **`/user`** — untuk karyawan dan peserta magang.

### 🏠 Dashboard User

- Widget salam & status absensi hari ini
- Statistik kehadiran bulan berjalan

### ⏱️ Halaman Absensi (`/absensi`)

Alur lengkap dalam satu halaman:

```
1. Face Recognition (jika ON)
   ├── Kamera terbuka (mode cermin)
   ├── Posisikan wajah di tengah frame
   ├── "Tahan... Jangan Bergerak"
   └── Auto-capture saat wajah stabil
         │
         ▼
2. Validasi GPS
   ├── Ambil koordinat browser
   ├── Haversine: hitung jarak ke kantor
   └── Tolak jika jarak > office_radius
         │
         ▼
3. Risk Assessment
   ├── Kirim device token
   ├── Cek riwayat user_devices
   └── Hitung risk_level
         │
         ▼
4. Simpan Record
   ├── jam_masuk / jam_pulang
   ├── lat/lng + distance
   ├── device_info + capture_image
   ├── risk_level
   ├── schedule_jam_masuk (snapshot)
   └── is_ramadan (snapshot)
```

### 📜 Riwayat Absensi (MyAbsences)

- Seluruh riwayat absensi personal
- Kolom **Jadwal**: badge `Normal` / `🌙 Ramadan`
- Warna jam masuk: 🟢 Tepat Waktu / 🔴 Terlambat (menggunakan snapshot threshold)

### 📝 Permintaan Koreksi

- Ajukan koreksi jika data absen salah
- Status: Pending → Approved / Rejected

---

## 🛡️ Logika Keamanan & Anti-Fraud

### Device Fingerprinting & Risk Scoring

Saat `checkIn()`, algoritma berikut dijalankan di `AbsensiController`:

```
Device Token diterima dari browser
         │
         ▼
  Upsert user_devices (user_id, device_unique_id)
         │
         ▼
  Ambil semua user yang pernah pakai device ini [ORDER BY created_at ASC]
         │
      ┌──┴──┐
      │     │
   1 user  > 1 user ──── COLLISION DETECTED
      │         │
   SAFE ✅      ├── Current user = Original Owner ?
                │       │
                │     YES: risk = SAFE ✅
                │          (borrowers hari ini → DANGER 🔴)
                │
                └── Current user ≠ Original Owner ?
                          └──> risk = DANGER 🔴 (JOKI TERDETEKSI)
                               (pemilik asli → WARNING 🟡)
```

### GPS Validation — Haversine Formula

```php
// GeoLocationService::calculateDistance()
$a = sin(Δlat/2)² + cos(lat1) × cos(lat2) × sin(Δlon/2)²
$distance = 2 × arctan2(√a, √(1−a)) × 6371000  // meter

// Valid jika: $distance ≤ Setting::get('office_radius')
```

---

## 🌙 Jadwal Ramadan

`AttendanceService::getTodaySchedule()` menentukan jadwal aktif hari ini secara otomatis:

```
Hari ini?
     │
     ▼
Cek: ramadan_start_date ≤ today ≤ ramadan_end_date ?
     │
  ┌──┴──┐
  │     │
 YA    TIDAK
  │     │
  ▼     ▼
Jadwal  Jadwal Normal
Ramadan (default_jam_masuk, dll.)
  │
  ├── Apakah hari ini Jumat?
  │       │
  │     YA  ──── ramadan_jam_pulang_jumat tersedia?
  │                 │               │
  │               YA ✅           TIDAK
  │                 │               │
  │           jam_pulang =    jam_pulang =
  │           jam_pulang_jumat  jam_pulang
  │           (mis. 15:30)     (mis. 16:30)
  │
  └──> Return: { jam_masuk, jam_pulang, is_ramadan: true, jam_masuk_carbon }
```

**Widget Dashboard** menampilkan:

- Pill **Pulang (Sen–Kam)** → selalu tunjukkan `ramadan_jam_pulang` (jam weekday)
- Pill **Pulang (Jum'at)** → muncul hanya jika `ramadan_jam_pulang_jumat` dikonfigurasi
- Pill **Pulang Hari Ini** → nilai efektif hari ini (otomatis pilih berdasarkan hari)

> **Immutability** — Saat check-in, `schedule_jam_masuk` dan `is_ramadan` di-_snapshot_ ke record absensi. Perubahan setting jadwal di kemudian hari **tidak akan mengubah** data historis yang sudah tersimpan.

---

## 📊 Ekspor Laporan Excel

**Endpoint:** `GET /custom-exports/absences/monthly?month=2&year=2026`

Format file `AttendanceExport.php`:

| Warna Sel  | Arti                 |
| :--------: | :------------------- |
|  🟢 Hijau  | Hadir tepat waktu    |
| 🟡 Kuning  | Hadir terlambat      |
|  🔴 Merah  | Tidak hadir          |
|  🔵 Biru   | Izin (approved)      |
| ⬜ Abu-abu | Hari libur / weekend |

- **Satu baris = satu pegawai**, **satu kolom = satu hari dalam bulan**
- Threshold per hari memperhitungkan Ramadan secara akurat
- Logo instansi di header sheet
- Total kehadiran per pegawai dihitung otomatis

---

## 📱 PWA (Progressive Web App)

Konfigurasi di `config/laravelpwa.php`:

```json
{
    "name": "Sistem Absensi BAPETEN",
    "short_name": "Absensi",
    "theme_color": "#d97706",
    "display": "standalone",
    "orientation": "portrait",
    "shortcuts": [{ "name": "Absen Masuk", "url": "/absensi" }]
}
```

**Cara Install di Perangkat:**

1. Buka aplikasi di **Chrome Android** atau **Safari iOS**
2. Tap banner _"Tambahkan ke Layar Utama"_ atau pilih dari menu browser → _Install App_
3. Aplikasi berjalan **fullscreen** tanpa address bar layaknya aplikasi native

---

## 🚀 CI/CD Pipeline

Sistem dilengkapi **GitHub Actions** untuk deployment otomatis ke VPS/hosting. Detail lengkap tersedia di [`CICD.md`](CICD.md).

```
Push ke branch main
        │
        ▼
  GitHub Actions Triggered
        │
  ┌─────┼─────────────────┐
  │     │                 │
Setup  Build           Verify
PHP    composer          tests
8.2+   + npm run build
  └─────┴─────────────────┘
        │
        ▼
  Rsync files → Server (SSH)
        │
        ▼
  Remote Commands:
  php artisan migrate
  php artisan optimize
  php artisan filament:optimize
        │
        ▼
  ✅ Production Live
```

**GitHub Secrets yang diperlukan:**

| Secret            | Keterangan                         |
| :---------------- | :--------------------------------- |
| `SSH_PRIVATE_KEY` | Private key SSH untuk akses server |
| `SSH_HOST`        | IP / domain server production      |
| `SSH_USERNAME`    | Username SSH server                |
| `DEPLOY_PATH`     | Path direktori project di server   |

---

## 🗺️ Rute Aplikasi Utama

| Method     | URL                                | Keterangan                      | Auth  |
| :--------- | :--------------------------------- | :------------------------------ | :---: |
| `GET`      | `/`                                | Redirect ke login               |   —   |
| `GET/POST` | `/login`                           | Halaman login                   |   —   |
| `POST`     | `/logout`                          | Logout                          |  ✅   |
| `GET`      | `/intern-register/{token}`         | Pendaftaran peserta via link    |   —   |
| `GET`      | `/absensi`                         | Halaman absensi (kamera + peta) |  ✅   |
| `POST`     | `/absensi/check-in`                | Submit absen masuk              |  ✅   |
| `POST`     | `/absensi/check-out`               | Submit absen pulang             |  ✅   |
| `GET`      | `/admin`                           | Dashboard admin                 | Admin |
| `GET`      | `/admin/users`                     | Manajemen pengguna              | Admin |
| `GET`      | `/admin/absences`                  | Data absensi seluruh pegawai    | Admin |
| `GET`      | `/admin/settings`                  | Pengaturan sistem               | Admin |
| `GET`      | `/user`                            | Dashboard user/peserta          |  ✅   |
| `GET`      | `/custom-exports/absences/monthly` | Ekspor Excel bulanan            |  ✅   |
| `GET`      | `/sitemap.xml`                     | Sitemap SEO                     |   —   |
| `GET`      | `/robots.txt`                      | Robots directive                |   —   |

---

<div align="center">

---

**Dibuat dengan ❤️ untuk efisiensi dan integritas data kehadiran.**

_Sistem Absensi BAPETEN — Absen Cerdas, Data Terpercaya._

</div>
