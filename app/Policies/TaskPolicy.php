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
        // Superadmin bisa update
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Penerima tugas bisa update
        if ($task->assignees->contains($user)) {
            return true;
        }

        // Jika tugas terkait proyek...
        if ($task->project) {
            // Ketua tim proyek bisa update semua tugas di proyeknya
            if ($user->id === $task->project->leader_id) {
                return true;
            }
            // Cek kebijakan level proyek (untuk pemilik dan manajer hierarkis)
            return $user->can('update', $task->project);
        }

        // Untuk tugas ad-hoc, manajer dari penerima tugas bisa update
        if (!$task->project_id && $user->isManager() && $user->unit) {
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
