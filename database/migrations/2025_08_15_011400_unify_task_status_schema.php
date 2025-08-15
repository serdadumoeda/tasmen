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
        Schema::table('tasks', function (Blueprint $table) {
            // Drop the redundant boolean column, unifying status logic into the 'status' string column.
            if (Schema::hasColumn('tasks', 'pending_review')) {
                $table->dropColumn('pending_review');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Add the column back on rollback
            if (!Schema::hasColumn('tasks', 'pending_review')) {
                $table->boolean('pending_review')->default(false)->after('status');
            }
        });
    }
};
