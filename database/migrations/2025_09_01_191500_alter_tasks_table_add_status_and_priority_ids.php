<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Task;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->unsignedBigInteger('task_status_id')->nullable()->after('status');
            $table->unsignedBigInteger('priority_level_id')->nullable()->after('priority');

            $table->foreign('task_status_id')->references('id')->on('task_statuses');
            $table->foreign('priority_level_id')->references('id')->on('priority_levels');
        });

        // Data migration
        $statuses = DB::table('task_statuses')->pluck('id', 'key');
        $priorities = DB::table('priority_levels')->pluck('id', 'key');

        // It's safer to loop through tasks to update them to avoid memory issues on large tables.
        Task::all()->each(function ($task) use ($statuses, $priorities) {
            $statusKey = strtolower($task->status);
            $priorityKey = strtolower($task->priority);

            $statusId = $statuses[$statusKey] ?? null;
            $priorityId = $priorities[$priorityKey] ?? null;

            // Fallback to a default if the key doesn't exist.
            if (!$statusId) {
                $statusId = $statuses['pending'] ?? 1;
            }
            if (!$priorityId) {
                $priorityId = $priorities['medium'] ?? 2;
            }

            DB::table('tasks')->where('id', $task->id)->update([
                'task_status_id' => $statusId,
                'priority_level_id' => $priorityId,
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['task_status_id']);
            $table->dropForeign(['priority_level_id']);
            $table->dropColumn(['task_status_id', 'priority_level_id']);
        });
    }
};
