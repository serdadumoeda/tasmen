<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProjectPolicy
{
    /**
     * Tentukan apakah user bisa melihat daftar proyek.
     * Superadmin bisa melihat semua.
     * Manajer bisa melihat proyek milik bawahannya.
     */
    public function viewAny(User $user): bool
    {
        return true; // Dikelola oleh scope
    }

    /**
     * Tentukan apakah user bisa melihat detail proyek.
     */
    public function view(User $user, Project $project): bool
    {
        // Superadmin bisa melihat semua
        if ($user->hasRole('Superadmin')) {
            return true;
        }

        // Pemilik, leader, atau anggota tim bisa melihat
        if ($project->owner_id === $user->id || $project->leader_id === $user->id || $project->members()->where('user_id', $user->id)->exists()) {
            return true;
        }

        // Manajer bisa melihat proyek milik bawahannya
        if ($user->isManager() && $user->unit && $project->owner && $project->owner->unit) {
            // Use the cached method to prevent N+1 performance issues
            return $user->getSubordinateUnitIdsWithCache()->contains($project->owner->unit->id);
        }

        return false;
    }

    /**
     * Tentukan apakah user bisa membuat proyek baru.
     */
    public function create(User $user): bool
    {
        // Explicitly deny 'Staf' role from creating projects, as per user requirements.
        if ($user->hasRole('Staf')) {
            return false;
        }

        // For other roles, use the existing centralized logic.
        return $user->canCreateProjects();
    }

    /**
     * Tentukan apakah user bisa mengupdate proyek.
     */
    public function update(User $user, Project $project): bool
    {
        // Superadmin bisa update
        if ($user->hasRole('Superadmin')) {
            return true;
        }

        // Pemilik dan leader bisa update
        if ($project->owner_id === $user->id || $project->leader_id === $user->id) {
            return true;
        }

        // Manajer dari pemilik bisa update
        if ($user->isManager() && $user->unit && $project->owner && $project->owner->unit) {
            // Use the cached method to prevent N+1 performance issues
            return $user->getSubordinateUnitIdsWithCache()->contains($project->owner->unit->id);
        }

        return false;
    }

    /**
     * Tentukan apakah user bisa menghapus proyek.
     */
    public function delete(User $user, Project $project): bool
    {
        // Hanya superadmin atau pemilik yang bisa hapus
        return $user->hasRole('Superadmin') || $project->owner_id === $user->id;
    }

    public function viewTeamDashboard(User $user, Project $project): bool
    {
        // Superadmin dapat melihat semua dashboard tim.
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Pemilik dan ketua tim dari proyek dapat melihat dashboard tim mereka.
        if ($user->id === $project->owner_id || $user->id === $project->leader_id) {
            return true;
        }

        // Manajer (Eselon I, Eselon II) dapat melihat dashboard tim dari proyek
        // yang dimiliki oleh bawahan dalam hierarki unit mereka.
        if ($user->isTopLevelManager() && $project->owner) {
            return $project->owner->isSubordinateOf($user);
        }

        return false;
    }
}