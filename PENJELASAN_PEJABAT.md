Tentu, saya akan jelaskan perubahan yang saya lakukan pada bagian "Daftar Pejabat di Unit Ini" di halaman edit unit.

**Sebelum Perubahan:**
-   Bagian ini sebelumnya bernama "Daftar Jabatan di Unit Ini".
-   Fungsinya adalah untuk mengelola "slot" atau posisi jabatan yang tersedia di unit tersebut. Anda bisa menambah atau menghapus nama-nama jabatan (misalnya, "Analis", "Pranata Komputer") langsung dari halaman ini.

**Mengapa Diubah?**
-   Sistem lama ini tidak lagi sesuai dengan alur kerja baru yang telah kita sepakati, di mana **Jabatan sekarang melekat pada pengguna dan diinput secara manual**, bukan lagi "slot" kosong yang ada di sebuah unit.

**Setelah Perubahan (Yang Sekarang):**
-   **Judul diubah** menjadi "Daftar Pejabat di Unit Ini" agar lebih akurat.
-   **Fungsi manajemen dihapus:** Saya telah menghapus total semua tombol, form, dan logika untuk menambah/menghapus jabatan dari halaman ini.
-   **Menjadi Tampilan Informasi (Read-Only):** Bagian ini sekarang murni untuk menampilkan informasi. Isinya adalah sebuah tabel yang menampilkan **siapa saja orang (pejabat) yang saat ini terdaftar di dalam unit tersebut**.
-   **Isi Tabel:** Tabel tersebut menampilkan:
    -   Nama Pejabat (`$user->name`)
    -   Nama Jabatan yang mereka isi (`$user->jabatan->name`)
    -   Peran (Role) mereka

Singkatnya, bagian tersebut sekarang berfungsi sebagai daftar nama (roster) anggota unit, bukan lagi sebagai alat untuk mengelola posisi jabatan.
