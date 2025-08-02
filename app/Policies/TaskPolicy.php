<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    /**
     * Tentukan apakah pengguna bisa melihat tugas.
     */
    public function view(User $user, Task $task): bool
    {
        // Superadmin bisa melihat semua
        if ($user->role === User::ROLE_SUPERADMIN) {
            return true;
        }

        // Anggota tim bisa melihat
        if ($task->assignees->contains($user)) {
            return true;
        }
        
        // Jika tugas terkait proyek, gunakan ProjectPolicy
        if ($task->project) {
            return $user->can('view', $task->project);
        }

        // Untuk tugas ad-hoc, manajer dari penerima tugas bisa melihat
        if (!$task->project_id && $user->isManager() && $user->unit) {
            foreach ($task->assignees as $assignee) {
                if ($assignee->unit && in_array($assignee->unit->id, $user->unit->getAllSubordinateUnitIds())) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Tentukan apakah pengguna bisa mengupdate tugas.
     */
    public function update(User $user, Task $task): bool
    {
        // Superadmin can update anything.
        if ($user->isSuperAdmin()) {
            return true;
        }

        // The person assigned to the task can update it.
        if ($task->assignees->contains($user)) {
            return true;
        }

        // If the task is part of a project, check project-level permissions.
        if ($project = $task->project) {
            // The project owner can update all tasks in their project.
            if ($user->id === $project->owner_id) {
                return true;
            }
            // The project leader can update all tasks in their project.
            if ($user->id === $project->leader_id) {
                return true;
            }
            // A manager can update tasks in a project owned by their subordinate.
            if ($user->isTopLevelManager() && $project->owner && $project->owner->isSubordinateOf($user)) {
                return true;
            }
        }

        // For ad-hoc tasks (no project), a manager can update tasks assigned to their subordinates.
        if (!$task->project_id && $user->isManager()) {
            foreach ($task->assignees as $assignee) {
                if ($assignee->isSubordinateOf($user)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Tentukan apakah pengguna bisa menyetujui/menolak tugas.
     */
    public function approve(User $user, Task $task): bool
    {
        return $this->update($user, $task);
    }

    /**
     * Tentukan apakah pengguna bisa menghapus tugas.
     */
    public function delete(User $user, Task $task): bool
    {
        return $this->update($user, $task);
    }
}
