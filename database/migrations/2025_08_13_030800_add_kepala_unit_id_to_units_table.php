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
        Schema::table('units', function (Blueprint $table) {
            $table->foreignId('kepala_unit_id')->nullable()->after('parent_unit_id')->constrained('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            // The dropForeign method will drop the foreign key constraint by convention.
            $table->dropForeign(['kepala_unit_id']);
            $table->dropColumn('kepala_unit_id');
        });
    }
};
