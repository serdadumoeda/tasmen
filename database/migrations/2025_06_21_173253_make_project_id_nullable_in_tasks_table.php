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
            // Kita ubah kolom project_id agar bisa menerima nilai NULL.
            // Ini adalah kunci agar sebuah tugas bisa dianggap sebagai "Ad-Hoc".
            $table->foreignId('project_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Mengembalikan ke kondisi semula jika migrasi di-rollback.
            // Perhatian: Ini akan gagal jika sudah ada data tugas dengan project_id=NULL.
            $table->foreignId('project_id')->nullable(false)->change();
        });
    }
};