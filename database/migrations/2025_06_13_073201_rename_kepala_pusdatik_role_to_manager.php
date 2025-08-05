<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Mengubah semua user dengan role 'kepala_pusdatik' menjadi 'manager'
        DB::table('users')
            ->where('role', 'kepala_pusdatik')
            ->update(['role' => 'manager']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Jika migrasi di-rollback, kembalikan 'manager' menjadi 'kepala_pusdatik'
         DB::table('users')
            ->where('role', 'manager')
            ->update(['role' => 'kepala_pusdatik']);
    }
};