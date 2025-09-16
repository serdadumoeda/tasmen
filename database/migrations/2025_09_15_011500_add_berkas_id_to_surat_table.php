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
        Schema::table('surat', function (Blueprint $table) {
            $table->foreignId('berkas_id')->nullable()->constrained('berkas')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat', function (Blueprint $table) {
            $table->dropForeign(['berkas_id']);
            $table->dropColumn('berkas_id');
        });
    }
};
