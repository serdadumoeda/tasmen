# Tasmen - Aplikasi Manajemen Tugas & Proyek

Aplikasi web untuk manajemen tugas, proyek, dan sumber daya internal. Dibangun menggunakan Laravel Framework.

## Fitur Utama

- **Manajemen Proyek**: Buat, kelola, dan lacak progres proyek.
- **Manajemen Tugas & Sub-tugas**: Atur tugas dan sub-tugas dengan detail, tenggat waktu, dan penanggung jawab.
- **Pelacakan Waktu**: Catat waktu yang dihabiskan untuk setiap tugas.
- **Manajemen Anggaran**: Alokasikan dan lacak realisasi anggaran untuk setiap proyek.
- **Analisis Beban Kerja**: Pantau dan analisis beban kerja tim untuk distribusi yang lebih baik.
- **Resource Pool**: Kelola dan pinjam sumber daya (pegawai) antar unit kerja.
- **Notifikasi**: Pengingat dan pemberitahuan otomatis untuk tugas, komentar, dan pembaruan penting lainnya.
- **Hierarki Pengguna**: Struktur pengguna berjenjang (Manajer, Staff) dengan hak akses yang berbeda.
- **Laporan**: Hasilkan laporan ringkasan proyek dan kinerja.

## Teknologi yang Digunakan

- **Backend**: PHP 8.2+, Laravel 12
- **Frontend**: Vite, Bootstrap
- **Database**: MySQL / PostgreSQL (direkomendasikan)
- **Server**: Nginx / Apache

## Panduan Instalasi

Berikut adalah langkah-langkah untuk menginstal dan menjalankan proyek ini di lingkungan pengembangan lokal.

### Prasyarat

Pastikan perangkat Anda telah terinstal:
- PHP >= 8.2
- Composer 2.x
- Node.js & NPM
- Database (misalnya MySQL, PostgreSQL)

### Langkah-langkah Instalasi

1.  **Clone Repositori**
    ```bash
    git clone https://github.com/USERNAME/REPO.git
    cd REPO
    ```
    *Catatan: Ganti `USERNAME/REPO` dengan URL repositori yang sesuai.*

2.  **Install Dependensi**
    Install dependensi PHP dengan Composer dan dependensi JavaScript dengan NPM.
    ```bash
    composer install
    npm install
    ```

3.  **Konfigurasi Environment**
    Salin file `.env.example` menjadi `.env`.
    ```bash
    cp .env.example .env
    ```
    Kemudian, generate *application key* untuk Laravel.
    ```bash
    php artisan key:generate
    ```

4.  **Konfigurasi Database**
    Buka file `.env` dan sesuaikan konfigurasi database berikut dengan pengaturan lokal Anda:
    ```
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=tasmen
    DB_USERNAME=root
    DB_PASSWORD=
    ```

5.  **Jalankan Migrasi & Seeder**
    Jalankan migrasi untuk membuat struktur tabel dan *seeder* untuk mengisi data awal (termasuk data pengguna default).
    ```bash
    php artisan migrate:fresh --seed
    ```
    Akun *default* yang akan dibuat (berdasarkan `UserSeeder`):
    - **Superadmin**: `superadmin@example.com` / `password`
    - **Manager**: `manager@example.com` / `password`
    - **User**: `user@example.com` / `password`

6.  **Build Aset Frontend**
    Compile aset frontend seperti CSS dan JavaScript.
    ```bash
    npm run build
    ```

7.  **Jalankan Server Pengembangan**
    Anda bisa menggunakan server bawaan Laravel.
    ```bash
    php artisan serve
    ```
    Aplikasi akan berjalan di `http://127.0.0.1:8000`.

    Atau, untuk pengalaman pengembangan yang lebih baik dengan *hot-reloading* dan *queue worker*, gunakan skrip `dev` yang sudah disediakan di `package.json`.
    ```bash
    npm run dev
    ```

---

Dibuat untuk mempermudah manajemen dan pelaporan di lingkungan kerja.
