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
        Schema::dropIfExists('lampiran_surat');
        Schema::dropIfExists('template_surat');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('template_surat', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->longText('konten');
            $table->timestamps();
        });

        Schema::create('lampiran_surat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surat_id')->constrained('surat')->onDelete('cascade');
            $table->string('nama_file');
            $table->string('path_file');
            $table->string('tipe_file')->nullable();
            $table->unsignedInteger('ukuran_file')->nullable();
            $table->timestamps();
        });
    }
};
