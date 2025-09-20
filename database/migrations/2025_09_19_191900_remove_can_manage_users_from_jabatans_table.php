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
        // Check if the column exists before trying to drop it
        if (Schema::hasColumn('jabatans', 'can_manage_users')) {
            Schema::table('jabatans', function (Blueprint $table) {
                $table->dropColumn('can_manage_users');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-add the column if the migration is rolled back
        Schema::table('jabatans', function (Blueprint $table) {
            $table->boolean('can_manage_users')->default(false);
        });
    }
};
