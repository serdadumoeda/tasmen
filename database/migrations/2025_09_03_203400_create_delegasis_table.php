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
        Schema::create('delegasis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jabatan_id')->comment('Jabatan yang didelegasikan')->constrained();
            $table->foreignId('user_id')->comment('User yang menerima delegasi')->constrained();
            $table->enum('jenis', ['Plt', 'Plh']);
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->text('keterangan')->nullable();
            $table->foreignId('created_by_user_id')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delegasis');
    }
};
