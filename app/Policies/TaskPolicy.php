<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function update(User $user, Task $task): bool
    {
        // User bisa update jika dia adalah pimpinan proyek ATAU salah satu dari yang ditugaskan
        return $user->id === $task->project->leader_id || $task->assignees->contains($user);
    }

    public function delete(User $user, Task $task): bool
    {
        // Aturan yang sama dengan update
        return $user->id === $task->project->leader_id || $task->assignees->contains($user);
    }
}