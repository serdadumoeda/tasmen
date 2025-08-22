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
        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Cuti Tahunan, Cuti Sakit, dll.
            $table->integer('default_days')->nullable(); // Jatah hari default per tahun
            $table->boolean('requires_attachment')->default(false); // Apakah butuh lampiran (misal: Cuti Sakit)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_types');
    }
};
