<?php

namespace App\Observers;

use App\Models\Task;

class TaskObserver
{
    /**
     * Handle the Task "saving" event.
     *
     * @param  \App\Models\Task  $task
     * @return void
     */
    public function saving(Task $task)
    {
        // Check if the task has sub-tasks to avoid unnecessary calculations.
        // We check the count on the relationship, not the loaded collection.
        if ($task->subTasks()->count() > 0) {
            $task->recalculateProgress();
        }
    }
}
