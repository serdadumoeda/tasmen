<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // <-- PENTING: Import DB Facade

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Metode ini sekarang hanya akan mengupdate data, bukan mengubah skema.
     * Ini adalah pendekatan yang aman dan kompatibel dengan PostgreSQL.
     */
    public function up(): void
    {
        // Mengubah semua pengguna yang ada dengan peran 'Ketua Tim'
        // menjadi 'Sub Koordinator' sebagai default yang masuk akal.
        // Ini memastikan tidak ada data yang tidak valid sebelum kita melanjutkan.
        DB::table('users')
            ->where('role', 'Ketua Tim')
            ->update(['role' => 'Sub Koordinator']);

        // CATATAN PENTING:
        // Kita tidak menjalankan Schema::table(...) di sini untuk menghindari error SQL.
        // Dengan menghapus 'Ketua Tim' dari semua Form dan Seeder, kita telah
        // secara efektif menonaktifkan peran tersebut dari aplikasi.
    }

    /**
     * Reverse the migrations.
     *
     * Karena kita hanya mengubah data di metode up(), kita tidak perlu
     * melakukan apa pun di metode down().
     */
    public function down(): void
    {
        // Tidak ada aksi yang diperlukan karena skema tidak diubah.
    }
};