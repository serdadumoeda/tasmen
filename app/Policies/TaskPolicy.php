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
        // Aturan Universal: Penerima tugas selalu bisa mengupdate tugasnya.
        if ($task->assignees->contains($user)) {
            return true;
        }

        // Aturan Khusus Proyek: Pimpinan proyek bisa mengupdate tugas dalam proyeknya.
        // Pengecekan 'if ($task->project)' SANGAT PENTING untuk menghindari error.
        if ($task->project && $user->id === $task->project->leader_id) {
            return true;
        }

        // Aturan Khusus Ad-Hoc: Atasan dari penerima tugas bisa mengupdate.
        // Ini memastikan atasan punya kontrol atas tugas bawahannya.
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
     * Kita akan menggunakan logika yang sama persis dengan 'update'.
     */
    public function delete(User $user, Task $task): bool
    {
        // Menggunakan logika yang sama dengan hak akses update.
        return $this->update($user, $task);
    }
}