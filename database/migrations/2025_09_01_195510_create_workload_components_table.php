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
        Schema::create('workload_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_type_id')->constrained('job_types')->onDelete('cascade');
            $table->string('name'); // Uraian pekerjaan
            $table->unsignedInteger('volume'); // Volume kerja per tahun
            $table->string('output_unit'); // Satuan output (e.g., "dokumen", "laporan")
            $table->decimal('time_norm', 8, 2); // Norma waktu in hours
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workload_components');
    }
};
