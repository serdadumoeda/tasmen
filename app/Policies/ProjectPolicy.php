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
        if ($user->role === User::ROLE_SUPERADMIN) {
            return true;
        }

        // Pemilik, leader, atau anggota tim bisa melihat
        if ($project->owner_id === $user->id || $project->leader_id === $user->id || $project->members()->where('user_id', $user->id)->exists()) {
            return true;
        }

        // Manajer bisa melihat proyek milik bawahannya
        if ($user->isManager() && $user->unit && $project->owner && $project->owner->unit) {
            return in_array($project->owner->unit->id, $user->unit->getAllSubordinateUnitIds());
        }

        return false;
    }

    /**
     * Tentukan apakah user bisa membuat proyek baru.
     */
    public function create(User $user): bool
    {
        return $user->canCreateProjects();
    }

    /**
     * Tentukan apakah user bisa mengupdate proyek.
     */
    public function update(User $user, Project $project): bool
    {
        // Superadmin bisa update
        if ($user->role === User::ROLE_SUPERADMIN) {
            return true;
        }

        // Pemilik dan leader bisa update
        if ($project->owner_id === $user->id || $project->leader_id === $user->id) {
            return true;
        }

        // Manajer dari pemilik bisa update
        if ($user->isManager() && $user->unit && $project->owner && $project->owner->unit) {
            return in_array($project->owner->unit->id, $user->unit->getAllSubordinateUnitIds());
        }

        return false;
    }

    /**
     * Tentukan apakah user bisa menghapus proyek.
     */
    public function delete(User $user, Project $project): bool
    {
        // Hanya superadmin atau pemilik yang bisa hapus
        return $user->role === User::ROLE_SUPERADMIN || $project->owner_id === $user->id;
    }

    public function viewTeamDashboard(User $user, Project $project): bool
    {
        // Izinkan jika pengguna adalah pemilik proyek ATAU ketua tim proyek.
        return $user->id === $project->owner_id || $user->id === $project->leader_id;
    }
}