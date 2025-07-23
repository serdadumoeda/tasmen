<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Perintah SQL mentah untuk menghapus check constraint di PostgreSQL
        DB::statement('ALTER TABLE units DROP CONSTRAINT IF EXISTS units_level_check');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tidak ada cara mudah untuk membuat ulang constraint yang tidak kita ketahui definisinya.
        // Biarkan kosong atau tambahkan komentar.
    }
};
