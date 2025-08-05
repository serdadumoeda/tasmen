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
        Schema::create('unit_paths', function (Blueprint $table) {
            $table->foreignId('ancestor_id')->constrained('units')->onDelete('cascade');
            $table->foreignId('descendant_id')->constrained('units')->onDelete('cascade');
            $table->unsignedInteger('depth');

            // Set the primary key
            $table->primary(['ancestor_id', 'descendant_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unit_paths');
    }
};
