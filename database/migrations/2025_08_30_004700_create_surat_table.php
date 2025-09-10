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
        Schema::create('surat', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_surat')->unique()->nullable();
            $table->string('perihal');
            $table->date('tanggal_surat');
            $table->enum('jenis', ['masuk', 'keluar']);
            $table->string('status')->default('draft');
            $table->foreignId('pembuat_id')->constrained('users');
            $table->foreignId('penyetuju_id')->nullable()->constrained('users');
            $table->longText('konten')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat');
    }
};
