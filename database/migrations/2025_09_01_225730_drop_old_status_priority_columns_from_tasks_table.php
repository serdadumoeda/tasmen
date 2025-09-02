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
        // This migration assumes the data has already been migrated to the new columns
        // in the 2025_09_01_191500_alter_tasks_table_add_status_and_priority_ids.php migration.
        Schema::table('tasks', function (Blueprint $table) {
            if (Schema::hasColumn('tasks', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('tasks', 'priority')) {
                $table->dropColumn('priority');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->string('status')->nullable();
            $table->string('priority')->nullable();
        });
    }
};
