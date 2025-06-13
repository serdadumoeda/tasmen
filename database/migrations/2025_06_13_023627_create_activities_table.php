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
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->nullableMorphs('subject'); // Ini akan menyimpan ID & Tipe model (misal: Task)
            $table->string('description'); // Deskripsi aktivitas, misal: "created_task"
            $table->text('before')->nullable(); // State sebelum perubahan (JSON)
            $table->text('after')->nullable(); // State setelah perubahan (JSON)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
