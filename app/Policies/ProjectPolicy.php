<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProjectPolicy
{
    /**
     * MODIFIKASI: Method 'before' tidak lagi diperlukan di sini karena sudah ditangani
     * oleh AppServiceProvider yang hanya meloloskan superadmin.
     */

    /**
     * Helper function untuk memeriksa apakah proyek berada dalam hirarki pengguna.
     * Ini adalah satu-satunya sumber kebenaran untuk kebijakan ini.
     */
    private function isWithinHierarchy(User $user, Project $project): bool
    {
        // Jika pimpinan, periksa apakah pemilik proyek adalah bawahan atau dirinya sendiri.
        if ($user->canManageUsers()) {
             // Fallback untuk proyek lama yang mungkin tidak punya owner
            if (!$project->owner) {
                return $user->id === $project->leader_id;
            }
            return $user->id === $project->owner_id || $project->owner->isSubordinateOf($user);
        }

        // Jika bukan pimpinan (misal: Staf), mereka hanya bisa melihat jika mereka adalah anggota.
        return $project->members->contains($user);
    }

    /**
     * Tentukan apakah user bisa melihat daftar proyek.
     * Selalu true, karena filtering utama dilakukan oleh HierarchicalScope.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Tentukan apakah user bisa melihat detail proyek.
     * Aturan: Proyek berada dalam hirarki pimpinan, atau user adalah anggota tim.
     */
    public function view(User $user, Project $project): bool
    {
        return $this->isWithinHierarchy($user, $project);
    }

    /**
     * Tentukan apakah user bisa membuat proyek baru.
     * Aturan tidak berubah: Hanya pimpinan yang bisa.
     */
    public function create(User $user): bool
    {
        return $user->canCreateProjects();
    }

    /**
     * Tentukan apakah user bisa mengupdate proyek.
     * Aturan baru: Proyek berada dalam hirarki pimpinan.
     */
    public function update(User $user, Project $project): bool
    {
        return $this->isWithinHierarchy($user, $project);
    }

    /**
     * Tentukan apakah user bisa mengelola anggota tim.
     * Aturan baru: Proyek berada dalam hirarki pimpinan.
     */
    public function manageMembers(User $user, Project $project): bool
    {
        return $this->isWithinHierarchy($user, $project);
    }

    /**
     * Tentukan apakah user bisa menghapus proyek.
     * Aturan baru: Proyek berada dalam hirarki pimpinan.
     */
    public function delete(User $user, Project $project): bool
    {
        return $this->isWithinHierarchy($user, $project);
    }

    /**
     * Tentukan apakah user bisa melihat dasbor tim.
     * Aturan baru: Proyek berada dalam hirarki pimpinan.
     */
    public function viewTeamDashboard(User $user, Project $project): bool
    {
        return $this->isWithinHierarchy($user, $project);
    }
}