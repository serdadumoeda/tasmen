<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Ubah kolom 'status' menjadi tipe string.
            // Ini adalah cara yang aman dan kompatibel untuk semua database.
            $table->string('status', 50)->default('pending')->change();
        });
    }

    /**
     * Reverse the migrations.
     * Kita tidak mendefinisikan rollback spesifik untuk menjaga keamanan data,
     * karena sulit untuk mengetahui state sebelumnya secara pasti.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Jika diperlukan, Anda bisa menambahkan logika untuk mengembalikan
            // ke state enum, tapi ini lebih aman.
        });
    }
};