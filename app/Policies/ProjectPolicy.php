<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProjectPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Project $project): bool
    {
        // User bisa melihat proyek jika dia adalah leader ATAU anggota proyek tersebut
        return $user->id === $project->leader_id || $project->members->contains($user);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Gunakan helper method yang sudah kita buat di model User
        return $user->canCreateProjects();
    }

    /**
     * Tentukan apakah user bisa mengedit detail utama proyek.
     * Hanya owner atau atasan owner yang bisa.
     */
    public function update(User $user, Project $project): bool
    {
        // Cek apakah ada owner, jika tidak (data lama), leader yang bertanggung jawab
        if (!$project->owner) {
            return $user->id === $project->leader_id;
        }
        return $user->id === $project->owner_id || $project->owner->isSubordinateOf($user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Project $project): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Project $project): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Project $project): bool
    {
        return false;
    }

    public function viewTeamDashboard(User $user, Project $project): bool
    {
        return $user->id === $project->leader_id;
    }

    public function update(User $user, Project $project): bool
    {
        return $user->id === $project->owner_id || $project->owner->isSubordinateOf($user);
    }

    /**
     * Tentukan apakah user bisa menghapus proyek.
     * Hanya owner atau atasan owner yang bisa.
     */
    public function delete(User $user, Project $project): bool
    {
        if (!$project->owner) {
            return $user->id === $project->leader_id;
        }
        return $user->id === $project->owner_id || $project->owner->isSubordinateOf($user);
    }

    /**
     * Tentukan apakah user bisa mengelola anggota proyek.
     * Owner dan Leader proyek bisa.
     */
    public function manageMembers(User $user, Project $project): bool
    {
        return $user->id === $project->owner_id || $user->id === $project->leader_id;
    }

}
