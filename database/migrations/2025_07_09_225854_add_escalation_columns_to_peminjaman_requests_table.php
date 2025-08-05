<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('peminjaman_requests', function (Blueprint $table) {
            // Kolom untuk melacak sudah berapa kali permintaan ini dieskalasi
            $table->integer('escalation_level')->default(0)->after('status');
            
            // Kolom untuk menandai kapan permintaan ini dianggap terlambat (kadaluwarsa)
            $table->timestamp('due_date')->nullable()->after('escalation_level');
        });
    }

    public function down(): void
    {
        Schema::table('peminjaman_requests', function (Blueprint $table) {
            $table->dropColumn(['escalation_level', 'due_date']);
        });
    }
};