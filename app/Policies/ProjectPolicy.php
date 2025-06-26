<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProjectPolicy
{
    /**
     * Helper function terpusat untuk memeriksa hak akses ke sebuah proyek.
     */
    private function hasAccess(User $user, Project $project): bool
    {
        // Aturan 1: User adalah pemilik proyek.
        if ($project->owner_id === $user->id) {
            return true;
        }

        // Aturan 2: User adalah ketua tim proyek.
        if ($project->leader_id === $user->id) {
            return true;
        }

        // Aturan 3: User adalah atasan langsung dari pemilik proyek.
        if ($project->owner && $project->owner->isSubordinateOf($user)) {
            return true;
        }
        
        // Aturan 4: User adalah anggota tim proyek (pengecekan terakhir ke database).
        return $project->members()->where('user_id', $user->id)->exists();
    }

    /**
     * Tentukan apakah user bisa melihat detail proyek.
     */
    public function view(User $user, Project $project): bool
    {
        return $this->hasAccess($user, $project);
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
     * Hanya pemilik dan ketua tim yang bisa edit.
     */
    public function update(User $user, Project $project): bool
    {
        return $project->owner_id === $user->id || $project->leader_id === $user->id;
    }

    /**
     * Tentukan apakah user bisa menghapus proyek.
     * Hanya pemilik asli yang bisa menghapus.
     */
    public function delete(User $user, Project $project): bool
    {
        return $project->owner_id === $user->id;
    }

    public function viewTeamDashboard(User $user, Project $project): bool
    {
        // Izinkan jika pengguna adalah pemilik proyek ATAU ketua tim proyek.
        return $user->id === $project->owner_id || $user->id === $project->leader_id;
    }
}