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
        // Superadmin bisa mengupdate apapun.
        if ($user->isSuperAdmin()) {
            return true;
        }

        // PERBAIKAN UTAMA: Pastikan relasi diload sebelum digunakan.
        // Sebaiknya ini sudah dilakukan di Controller, tapi kita lakukan di sini juga
        // untuk memastikan keamanan.
        $task->loadMissing('project', 'assignees');

        // Pengguna yang ditugaskan pada tugas dapat mengupdate-nya.
        if (optional($task->assignees)->contains($user)) {
            return true;
        }

        // Jika tugas adalah bagian dari proyek, periksa izin level proyek.
        if ($project = $task->project) {
            // Pemilik proyek (owner) bisa mengupdate semua tugas di proyeknya.
            if ($user->id === $project->owner_id) {
                return true;
            }
            // Pimpinan proyek (leader) bisa mengupdate semua tugas di proyeknya.
            if ($user->id === $project->leader_id) {
                return true;
            }
            // Manajer dapat mengupdate tugas di proyek yang dimiliki oleh bawahannya.
            // Memastikan relasi owner dan isSubordinateOf ada sebelum digunakan.
            if ($user->isTopLevelManager() && optional($project->owner)->isSubordinateOf($user)) {
                return true;
            }
        }

        // Untuk tugas ad-hoc (tanpa proyek), manajer dapat mengupdate tugas yang ditugaskan kepada bawahannya.
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
