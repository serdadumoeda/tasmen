<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    /**
     * Tentukan apakah pengguna bisa mengupdate tugas.
     */
    public function update(User $user, Task $task): bool
    {
        // Aturan 1: Penerima tugas (assignee) selalu bisa mengedit tugasnya.
        if ($task->assignees->contains($user)) {
            return true;
        }

        // Aturan 2: Jika ini tugas proyek, periksa apakah pengguna punya hak akses 'update' pada PROYEK-nya.
        // Ini akan secara otomatis menggunakan ProjectPolicy yang sudah benar dan hierarkis.
        if ($task->project && $user->can('update', $task->project)) {
            return true;
        }
        
        // Aturan 3: Jika ini tugas ad-hoc (tanpa proyek), periksa apakah pengguna adalah atasan dari penerima tugas.
        if (!$task->project_id) {
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
        // Aturan 1: Pimpinan yang berhak mengelola proyek bisa menyetujui.
        if ($task->project && $user->can('update', $task->project)) {
            return true;
        }

        // Aturan 2: Atasan dari penerima tugas ad-hoc bisa menyetujui.
        if (!$task->project_id) {
            foreach ($task->assignees as $assignee) {
                if ($assignee->isSubordinateOf($user)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Tentukan apakah pengguna bisa menghapus tugas.
     * Logikanya sama dengan 'update'.
     */
    public function delete(User $user, Task $task): bool
    {
        return $this->update($user, $task);
    }
}