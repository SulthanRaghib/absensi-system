# 🚀 CI/CD Documentation: Laravel with GitHub Actions

![GitHub Actions](https://img.shields.io/badge/GitHub_Actions-2088FF?style=for-the-badge&logo=github-actions&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![SSH](https://img.shields.io/badge/SSH_Deployment-24292e?style=for-the-badge&logo=gnu-bash&logoColor=white)

Dokumentasi ini menjelaskan implementasi **Continuous Integration & Continuous Deployment (CI/CD)** pada proyek ini. Sistem ini mengotomatiskan proses pengujian, _building_ aset, dan _deployment_ ke server produksi (VPS/Hosting) menggunakan GitHub Actions via protokol SSH/Rsync.

---

## 📋 Daftar Isi

1. [Arsitektur Workflow](#-arsitektur-workflow)
2. [Fitur Utama](#-fitur-utama)
3. [Prasyarat Sistem](#-prasyarat-sistem)
4. [Konfigurasi GitHub Secrets](#-konfigurasi-github-secrets)
5. [Struktur Pipeline (.yml)](#-struktur-pipeline-yml)
6. [Troubleshooting & Maintenance](#-troubleshooting--maintenance)

---

## 🏗 Arsitektur Workflow

Proses deployment berjalan secara otomatis setiap kali ada kode baru yang di-_push_ ke _branch_ utama (`master` atau `main`).

```mermaid
graph LR
    A[👨‍💻 Dev Push Code] -->|Git Push| B(GitHub Repository)
    B -->|Trigger| C{GitHub Actions}
    subgraph CI Process
    C -->|1. Setup| D[Setup PHP & Node.js]
    D -->|2. Build| E[Install Composer & NPM Build]
    end
    subgraph CD Process
    E -->|3. Transfer| F[Rsync Files to Server]
    F -->|4. Command| G[SSH Remote Commands]
    end
    G -->|Result| H[✅ Production Live]
```

---

## ✨ Fitur Utama

| Fitur                           | Deskripsi                                                                                                       |
| :------------------------------ | :-------------------------------------------------------------------------------------------------------------- |
| **🚀 Automated Deployment**     | Deploy otomatis tanpa perlu menyentuh FTP/FileZilla.                                                            |
| **⚡ Server-Side Optimization** | Menjalankan `optimize`, `config:cache`, dan `view:cache` otomatis di server.                                    |
| **🎨 Assets Build Offloading**  | Proses berat `npm run build` (Vite) dilakukan di GitHub Runner, bukan di server hosting (menghemat RAM server). |
| **🛡 Secure Transfer**           | Menggunakan SSH Key dan Rsync untuk transfer file yang aman dan terenkripsi.                                    |
| **🔄 Database Migration**       | Otomatis menjalankan `php artisan migrate` dengan aman.                                                         |
| **🖌 Filament Support**          | Termasuk optimasi aset panel admin Filament (`filament:optimize`).                                              |

---

## ⚙️ Prasyarat Sistem

Sebelum menjalankan pipeline, pastikan hal-hal berikut sudah siap:

1.  **Hosting/Server:**

    -   Mendukung akses **SSH**.
    -   Terinstall **PHP 8.2+**, **Composer**, dan **Git**.
    -   Struktur folder standar Laravel (`public_html` atau sejenisnya).

2.  **SSH Key Pair:**

    -   Private Key disimpan di GitHub Secrets.
    -   Public Key terdaftar di `~/.ssh/authorized_keys` di server.
    -   Format kunci yang disarankan: **RSA (PEM format)** untuk kompatibilitas maksimal.

---

## 🔐 Konfigurasi GitHub Secrets

Agar GitHub Actions dapat mengakses server, variabel sensitif harus disimpan di **Settings \> Secrets and variables \> Actions**.

| Nama Secret       | Wajib? | Deskripsi                             | Contoh Nilai                    |
| :---------------- | :----: | :------------------------------------ | :------------------------------ |
| `SSH_HOST`        |   ✅   | IP Address atau Domain Server.        | `xx.xx.xxx.xxx`                 |
| `SSH_USER`        |   ✅   | Username SSH/cPanel.                  | `absenmag`                      |
| `SSH_PORT`        |   ✅   | Port SSH (Default 22).                | `22`                            |
| `SSH_PRIVATE_KEY` |   ✅   | Isi Private Key (OpenSSH).            | `-----BEGIN RSA PRIVATE KEY...` |
| `TARGET_DIR`      |   ✅   | Path absolut folder proyek di server. | `/home/user/public_html`        |

---

## 📜 Struktur Pipeline (.yml)

File konfigurasi utama terletak di `.github/workflows/deploy-ssh.yml`. Berikut adalah tahapan detailnya:

### 1\. Build Environment (Local Runner)

Langkah ini menyiapkan kode sebelum dikirim agar bersih dan siap pakai.

-   **Checkout:** Mengambil kode terbaru.
-   **Composer Install:** Mengunduh dependensi PHP (tanpa dev-dependencies).
-   **NPM Build:** Mengompilasi aset CSS/JS (Tailwind/Vite).

### 2\. Deployment (Rsync)

Mengirim file ke server menggunakan `rsync`.

-   **Mode:** Incremental (hanya file yang berubah yang dikirim).
-   **Exclude:** File `.env`, folder `.git`, dan `node_modules` **TIDAK** dikirim untuk keamanan dan efisiensi.

### 3\. Remote Commands (SSH)

Mengeksekusi perintah Laravel langsung di server hosting:

```bash
# Contoh perintah yang dijalankan otomatis:
php artisan down                      # Mode maintenance
php artisan migrate --force           # Update database
php artisan optimize:clear            # Hapus cache lama
php artisan config:cache              # Cache config baru
php artisan filament:optimize         # Cache icon Filament
php artisan up                        # Website Online kembali
```

---

## 🔧 Troubleshooting & Maintenance

Berikut adalah solusi untuk masalah umum yang sering terjadi saat deployment.

### 🔴 Error: `Permission denied (publickey)`

**Penyebab:** Server menolak kunci SSH yang dikirim GitHub.
**Solusi:**

1.  Pastikan format Public Key di server (`~/.ssh/authorized_keys`) dalam **SATU BARIS** panjang.
2.  Cek permission folder di server (Wajib\!):
    ```bash
    chmod 700 ~/.ssh
    chmod 600 ~/.ssh/authorized_keys
    chmod 711 ~
    ```
3.  Pastikan Private Key di GitHub Secret dicopy lengkap dari header `BEGIN` sampai `END`.

### 🔴 Error: `rsync error: syntax or usage error`

**Penyebab:** Salah satu GitHub Secret kosong atau salah nama.
**Solusi:**

-   Cek ulang ejaan nama secret (`SSH_HOST` vs `HOST`).
-   Pastikan secret `TARGET_DIR` tidak diakhiri spasi.

### 🔴 Update Environment Variable (.env)

Jika Anda mengubah konfigurasi `.env` (misal: password DB baru), Anda harus:

1.  Login ke server via SSH/File Manager manual.
2.  Edit file `.env` di server secara langsung.
3.  Jalankan `php artisan config:clear` via terminal server.
    _(Pipeline ini sengaja tidak menimpa file .env demi keamanan)._

---

<p align="center">
Dibuat dengan ❤️ untuk efisiensi Absensi System.
</p>
````
tes
