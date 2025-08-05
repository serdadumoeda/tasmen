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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('unit_id')->nullable()->constrained('units');
            $table->enum('status', ['active', 'suspended'])->default('active');

            // Hapus kolom yang tidak relevan lagi
            if (Schema::hasColumn('users', 'eselon_2_id')) {
                $table->dropColumn('eselon_2_id');
            }
            if (Schema::hasColumn('users', 'parent_id')) {
                $table->dropColumn('parent_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('unit_id');
            $table->dropColumn('status');
            $table->unsignedBigInteger('eselon_2_id')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
        });
    }
};
