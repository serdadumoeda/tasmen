<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function update(User $user, Task $task): bool
    {
        // User bisa update jika dia adalah pimpinan proyek ATAU yang ditugaskan
        return $user->id === $task->project->leader_id || $user->id === $task->assigned_to_id;
    }

    public function delete(User $user, Task $task): bool
    {
        // Aturan yang sama dengan update
        return $user->id === $task->project->leader_id || $user->id === $task->assigned_to_id;
    }
}