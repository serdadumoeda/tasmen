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
            // Add the new foreign key column.
            // It's nullable at first to avoid issues with existing data.
            // We'll populate it and then make it non-nullable in a separate data migration if needed.
            $table->foreignId('task_status_id')->nullable()->constrained('task_statuses')->after('id');

            // Drop the old status column if it exists.
            if (Schema::hasColumn('tasks', 'status')) {
                $table->dropColumn('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Add the old column back.
            if (!Schema::hasColumn('tasks', 'status')) {
                $table->string('status')->default('pending')->after('progress');
            }

            // Drop the foreign key constraint and the column.
            $table->dropForeign(['task_status_id']);
            $table->dropColumn('task_status_id');
        });
    }
};
