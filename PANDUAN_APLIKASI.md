# Panduan Aplikasi Manajemen Proyek dan Tugas

## 1. Pendahuluan

Selamat datang di Panduan Pengguna Aplikasi Manajemen Proyek dan Tugas. Dokumen ini akan memandu Anda melalui berbagai fitur dan fungsionalitas yang tersedia di dalam aplikasi.

## 2. Beranda (Home)

Menu "Beranda" adalah halaman pertama yang Anda lihat setelah login. Tampilan halaman ini akan berbeda tergantung pada peran (role) Anda di dalam sistem.

### 2.1. Executive Summary (Untuk Pimpinan Eselon I & II)

Bagi pengguna dengan peran Eselon I atau Eselon II, Beranda akan menampilkan **Executive Summary**. Halaman ini memberikan gambaran umum tingkat tinggi (helikopter view) dari semua aktivitas di dalam organisasi Anda.

**Fitur Utama:**

*   **KPI Utama:**
    *   **Proyek Aktif:** Jumlah total proyek yang sedang berjalan.
    *   **Proyek Kritis:** Jumlah proyek yang berisiko terlambat atau sudah melewati tenggat waktu.
    *   **Total Anggaran:** Jumlah total anggaran dari semua proyek.
    *   **Penyerapan Anggaran:** Persentase anggaran yang sudah digunakan.
    *   **Progres Keseluruhan:** Rata-rata progres dari semua proyek.
*   **Daftar & Grafik:**
    *   **Proyek Kritis:** Daftar proyek yang memerlukan perhatian khusus.
    *   **Staf Performa Terbaik:** 5 staf dengan nilai kinerja tertinggi.
    *   **Staf Paling Sibuk:** 5 staf dengan utilisasi beban kerja tertinggi.
    *   **Anggaran per Proyek:** Rincian anggaran dan realisasi untuk 5 proyek dengan anggaran terbesar.
    *   **Tren Kinerja:** Grafik yang menunjukkan tren progres proyek dan penyerapan anggaran selama 6 bulan terakhir.

### 2.2. Global Dashboard (Untuk Selain Eselon I & II)

Untuk peran lainnya (Superadmin, Koordinator, Staf), Beranda akan menampilkan **Global Dashboard**. Halaman ini memberikan ringkasan operasional yang relevan dengan lingkup kerja Anda.

**Fitur Utama:**

*   **Statistik Kunci:**
    *   Jumlah Proyek, Total Pengguna, Pengguna Aktif, Total Tugas, Tugas Selesai, dan Permintaan Peminjaman yang menunggu persetujuan Anda.
    *   *Catatan: Manajer hanya akan melihat statistik dari tim yang mereka pimpin.*
*   **Daftar Proyek:**
    *   Daftar semua proyek dalam lingkup Anda. Anda dapat mencari berdasarkan nama proyek atau memfilter berdasarkan status.
*   **Aktivitas Terbaru:**
    *   Feed yang menampilkan 15 aktivitas terakhir yang terjadi di dalam sistem.

## 3. Menu Kerja

Menu ini berisi semua fitur yang berhubungan dengan pekerjaan dan tugas sehari-hari.

### 3.1. Daftar Kegiatan (Hanya Pimpinan)

*   **Fungsi:** Menampilkan daftar semua kegiatan atau proyek yang ada di dalam lingkup kewenangan Anda.
*   **Cara Akses:** Menu Kerja > Daftar Kegiatan.
*   **Detail:** Halaman ini sama dengan **Global Dashboard** yang dijelaskan di atas.

### 3.2. Tugas Harian

*   **Fungsi:** Mengelola tugas-tugas yang tidak terikat pada proyek tertentu (ad-hoc).
*   **Cara Akses:** Menu Kerja > Tugas Harian.
*   **Fitur:**
    *   **Melihat Tugas:**
        *   Staf biasa hanya dapat melihat tugas yang ditugaskan kepada mereka.
        *   Manajer dapat melihat tugas milik mereka dan semua bawahannya. Manajer juga dapat memfilter tugas berdasarkan personel.
    *   **Membuat Tugas:**
        *   Klik tombol "Buat Tugas Harian Baru".
        *   Isi detail tugas seperti judul, deskripsi, tenggat waktu, dan estimasi jam kerja.
        *   Manajer dapat menugaskan tugas ini kepada satu atau lebih bawahannya. Jika staf biasa yang membuat, tugas akan otomatis ditugaskan kepada dirinya sendiri.

### 3.3. SK Penugasan

*   **Fungsi:** Mengelola Surat Keputusan (SK) Penugasan resmi.
*   **Cara Akses:** Menu Kerja > SK Penugasan.
*   **Fitur:**
    *   **Melihat SK:** Visibilitas SK diatur berdasarkan hierarki. Anda hanya dapat melihat SK yang relevan dengan Anda atau tim Anda.
    *   **Membuat SK:**
        *   Klik "Buat SK Penugasan Baru".
        *   Isi detail seperti judul, nomor SK, tanggal berlaku, status, dan deskripsi.
        *   Unggah file SK resmi dalam format PDF atau gambar.
        *   Manajer dapat menambahkan beberapa anggota tim ke dalam SK tersebut dan menentukan peran masing-masing.

## 4. Laporan & Analisis (Hanya Pimpinan)

Menu ini menyediakan alat untuk menganalisis kinerja dan beban kerja tim. Hanya dapat diakses oleh pimpinan (Eselon I, Eselon II, Koordinator).

### 4.1. Analisis Beban Kerja

*   **Fungsi:** Memberikan analisis mendalam tentang beban kerja dan kinerja setiap anggota tim.
*   **Cara Akses:** Laporan & Analisis > Analisis Beban Kerja.
*   **Fitur:**
    *   Menampilkan daftar bawahan Anda beserta metrik kinerjanya.
    *   Anda dapat memberikan **penilaian perilaku kerja** (Diatas/Sesuai/Dibawah Ekspektasi) untuk setiap anggota tim. Penilaian ini akan secara otomatis menghitung ulang skor kinerja mereka.

### 4.2. Beban Kerja Mingguan

*   **Fungsi:** Memberikan gambaran cepat mengenai beban kerja setiap anggota tim untuk minggu berjalan.
*   **Cara Akses:** Laporan & Analisis > Beban Kerja Mingguan.
*   **Fitur:**
    *   Menampilkan daftar anggota tim dan persentase beban kerja mereka.
    *   Persentase dihitung berdasarkan total jam dari tugas-tugas yang belum selesai dibagi dengan standar jam kerja mingguan (37.5 jam).
    *   Ini membantu Anda mengidentifikasi siapa yang memiliki beban kerja berlebih (over-utilized) atau kurang (under-utilized).

## 5. Manajemen Tim (Hanya Manajer & Superadmin)

Menu ini berisi alat untuk mengelola struktur organisasi, pengguna, dan sumber daya tim.

### 5.1. Manajemen Unit (Hanya Superadmin)

*   **Fungsi:** Mengelola unit-unit organisasi di dalam sistem.
*   **Cara Akses:** Manajemen Tim > Manajemen Unit.
*   **Fitur:**
    *   Membuat, mengedit, dan menghapus unit organisasi.
    *   Menentukan level unit (Eselon I, Eselon II, dll.) dan unit induknya untuk membangun hierarki.
    *   Mengelola **Jabatan** di dalam setiap unit. Sebuah unit tidak dapat dihapus jika masih memiliki anggota atau sub-unit.

### 5.2. Manajemen Pengguna

*   **Fungsi:** Mengelola akun pengguna di dalam sistem.
*   **Cara Akses:** Manajemen Tim > Manajemen Pengguna.
*   **Fitur:**
    *   **Melihat Pengguna:** Menampilkan daftar pengguna yang berada dalam lingkup Anda.
    *   **Membuat Pengguna:** Membuat akun pengguna baru. Anda akan menentukan nama, email, password, dan menempatkan mereka pada **Unit** dan **Jabatan** yang spesifik. Peran (role) pengguna akan otomatis terisi sesuai level unitnya.
    *   **Mengedit Pengguna:** Mengubah detail pengguna, termasuk memindahkan mereka ke unit/jabatan lain.
    *   **Melihat Hirarki:** Terdapat tampilan hierarki untuk melihat struktur organisasi secara visual.

### 5.3. Peminjaman Anggota

*   **Fungsi:** Mengelola proses peminjaman anggota dari satu tim ke tim lain untuk sebuah proyek.
*   **Cara Akses:** Manajemen Tim > Peminjaman Anggota.
*   **Fitur:**
    *   **Dashboard Peminjaman:** Halaman ini memiliki tiga bagian:
        1.  **Menunggu Persetujuan Saya:** Daftar permintaan peminjaman dari manajer lain yang perlu Anda setujui.
        2.  **Permintaan Terkirim Saya:** Riwayat permintaan peminjaman yang telah Anda ajukan.
        3.  **Riwayat Persetujuan:** Riwayat permintaan yang telah Anda setujui atau tolak.
    *   **Proses:**
        *   Permintaan peminjaman biasanya diajukan dari halaman manajemen tim sebuah proyek.
        *   Sistem akan otomatis mengirim permintaan ke atasan dari anggota yang ingin dipinjam.
        *   Jika disetujui, anggota tersebut akan otomatis ditambahkan ke tim proyek.

### 5.4. Resource Pool

*   **Fungsi:** Mengelola daftar anggota tim yang tersedia untuk ditugaskan ke proyek baru.
*   **Cara Akses:** Manajemen Tim > Resource Pool.
*   **Fitur:**
    *   Menampilkan daftar bawahan Anda dan status mereka di dalam "Resource Pool".
    *   Anda dapat mengubah status seorang anggota menjadi "tersedia" di Resource Pool dan memberikan catatan mengenai ketersediaan mereka.
    *   Anggota yang berada di Resource Pool dapat dengan mudah ditemukan dan ditambahkan saat membuat tim proyek baru.

## 6. Profil & Notifikasi

### 6.1. Notifikasi

*   Klik ikon lonceng di pojok kanan atas untuk melihat notifikasi terbaru Anda (misalnya, saat Anda ditugaskan ke tugas baru atau saat permintaan peminjaman Anda disetujui).
*   Mengklik sebuah notifikasi akan menandainya sebagai sudah dibaca dan membawa Anda ke halaman yang relevan.

### 6.2. Profil Pengguna

*   Klik inisial nama Anda di pojok kanan atas untuk membuka menu profil.
*   **Profil:** Di halaman ini, Anda dapat:
    *   Mengubah nama dan alamat email Anda.
    *   Memperbarui password Anda.
    *   Menghapus akun Anda secara permanen (tindakan ini tidak dapat diurungkan).
*   **Log Out:** Keluar dari aplikasi.
