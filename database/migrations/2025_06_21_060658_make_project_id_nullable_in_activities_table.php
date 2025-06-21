<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            // Ubah project_id agar bisa null, karena aktivitas user tidak terikat proyek
            $table->foreignId('project_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            // Kembalikan seperti semula jika di-rollback
            $table->foreignId('project_id')->nullable(false)->change();
        });
    }
};