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
        Schema::create('berkas_surat', function (Blueprint $table) {
            $table->primary(['berkas_id', 'surat_id']);
            $table->foreignId('berkas_id')->constrained('berkas')->onDelete('cascade');
            $table->foreignId('surat_id')->constrained('surat')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('berkas_surat');
    }
};
