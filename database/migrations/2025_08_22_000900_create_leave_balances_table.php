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
        Schema::create('leave_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->year('year'); // Tahun jatah cuti
            $table->integer('total_days'); // Total jatah di tahun itu
            $table->integer('days_taken')->default(0); // Berapa hari yang sudah diambil
            $table->unique(['user_id', 'year']); // Setiap user hanya punya 1 record per tahun
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_balances');
    }
};
