# Dokumentasi Teknis Aplikasi Tasmen

Dokumen ini menjelaskan aspek teknis dari fitur-fitur baru dan yang telah dimodifikasi untuk meningkatkan fleksibilitas dan modularitas aplikasi.

## 1. Struktur Database Dinamis

Beberapa logika bisnis yang sebelumnya di-hardcode kini telah dipindahkan ke dalam database. Berikut adalah rincian tabel-tabel baru yang mengelola konfigurasi ini.

### `performance_settings`
Tabel ini menyimpan berbagai parameter yang digunakan dalam perhitungan kinerja (IKI/NKF) dan beban kerja.

- **Struktur:**
  - `id`: Primary Key
  - `key` (string, unique): Pengenal unik untuk pengaturan (misal: `manager_weights`, `efficiency_cap`).
  - `value` (json): Nilai dari pengaturan, disimpan dalam format JSON untuk fleksibilitas.
- **Contoh Keys:**
  - `manager_weights`: Menyimpan bobot manajerial untuk setiap peran.
  - `efficiency_cap`: Menyimpan batas minimum dan maksimum untuk faktor efisiensi.
  - `rating_thresholds`: Menyimpan ambang batas untuk predikat kinerja (misal: 'excellent', 'satisfactory').
  - `weekly_workload_thresholds`: Menyimpan ambang batas untuk visualisasi beban kerja mingguan.

### `roles`
Menggantikan sistem peran berbasis string. Tabel ini mendefinisikan peran pengguna dan atributnya.

- **Struktur:**
  - `id`: Primary Key
  - `name` (string, unique): Nama sistem untuk peran (misal: `eselon_i`, `staf`).
  - `label` (string): Nama yang ditampilkan di UI (misal: "Eselon I", "Staf").
  - `managerial_weight` (decimal): Bobot antara 0.00 dan 1.00 yang digunakan dalam perhitungan NKF pimpinan.
- **Relasi:** Terhubung ke tabel `users` melalui `role_id`.

### `task_statuses`
Menyimpan daftar status yang dapat dimiliki oleh sebuah tugas.

- **Struktur:**
  - `id`: Primary Key
  - `key` (string, unique): Pengenal unik untuk status (misal: `in_progress`).
  - `label` (string): Nama yang ditampilkan di UI (misal: "Dalam Proses").
- **Relasi:** Terhubung ke tabel `tasks` melalui `task_status_id`.

### `priority_levels`
Menyimpan daftar level prioritas untuk tugas.

- **Struktur:**
  - `id`: Primary Key
  - `key` (string, unique): Pengenal unik untuk prioritas (misal: `high`).
  - `label` (string): Nama yang ditampilkan di UI (misal: "Tinggi").
  - `weight` (integer): Bobot numerik yang merepresentasikan tingkat prioritas.
- **Relasi:** Terhubung ke tabel `tasks` melalui `priority_level_id`.

### `notification_templates`
Menyimpan template untuk konten notifikasi (email, database, dll).

- **Struktur:**
  - `id`: Primary Key
  - `key` (string, unique): Pengenal unik untuk template (misal: `task_assigned`).
  - `subject` (string): Judul/subjek notifikasi.
  - `body` (text): Isi notifikasi dengan placeholder (misal: `{{user_name}}`).
  - `description` (text): Penjelasan tentang template dan placeholder yang tersedia.

### `job_types` & `workload_components` (Modul ABK)
Tabel-tabel ini membentuk dasar dari Modul Analisis Beban Kerja (ABK).

- **`job_types`:**
  - `id`: Primary Key
  - `name` (string): Nama jabatan atau jenis pekerjaan utama.
- **`workload_components`:**
  - `id`: Primary Key
  - `job_type_id`: Foreign key ke `job_types`.
  - `name` (string): Uraian dari komponen pekerjaan.
  - `volume` (integer): Jumlah output yang dihasilkan per tahun.
  - `output_unit` (string): Satuan dari output (misal: "dokumen", "laporan").
  - `time_norm` (decimal): Waktu standar (dalam jam) yang dibutuhkan untuk menyelesaikan satu unit output.

---

## 2. Antarmuka Pengelolaan Admin

Untuk setiap tabel master di atas, telah dibuat antarmuka pengguna (UI) di dalam area Admin untuk melakukan operasi CRUD (Create, Read, Update, Delete). Ini memungkinkan administrator untuk mengkonfigurasi perilaku aplikasi secara langsung tanpa perlu mengubah kode.

Lokasi UI:
- `/admin/performance-settings`
- `/admin/roles`
- `/admin/task-statuses`
- `/admin/priority-levels`
- `/admin/notification-templates`
- `/admin/abk`

Setiap halaman dilindungi oleh `Gate::authorize('manage_settings')` untuk memastikan hanya pengguna yang berwenang yang dapat mengaksesnya.
