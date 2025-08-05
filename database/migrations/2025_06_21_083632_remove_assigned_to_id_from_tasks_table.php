<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Hapus foreign key constraint terlebih dahulu
            $table->dropForeign(['assigned_to_id']);
            // Hapus kolomnya
            $table->dropColumn('assigned_to_id');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Jika di-rollback, buat kembali kolomnya
            $table->foreignId('assigned_to_id')->nullable()->after('project_id')->constrained('users');
        });
    }
};