<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProjectPolicy
{
    /**
     * Memberikan hak akses superadmin untuk semua aksi sebelum pengecekan lain.
     */
    public function before(User $user, string $ability): bool|null
    {
        if ($user->role === 'superadmin') {
            return true;
        }
        return null; // Lanjutkan ke pengecekan method policy lainnya
    }

    /**
     * Tentukan apakah user bisa melihat detail proyek.
     * Aturan: User adalah pimpinan proyek ATAU anggota tim.
     */
    public function view(User $user, Project $project): bool
    {
        return $user->id === $project->leader_id || $project->members->contains($user);
    }

    /**
     * Tentukan apakah user bisa membuat proyek baru.
     * Aturan: User memiliki peran manajerial (Koordinator ke atas).
     */
    public function create(User $user): bool
    {
        return $user->canCreateProjects();
    }

    /**
     * Tentukan apakah user bisa mengedit detail utama proyek (nama, deskripsi).
     * Aturan: User adalah pemilik proyek ATAU atasan dari pemilik.
     */
    public function update(User $user, Project $project): bool
    {
        // Fallback untuk data lama yang mungkin belum punya owner
        if (!$project->owner) {
            return $user->id === $project->leader_id;
        }
        return $user->id === $project->owner_id || $project->owner->isSubordinateOf($user);
    }

    /**
     * Tentukan apakah user bisa mengelola anggota tim (tambah/hapus).
     * Aturan: User adalah pemilik proyek ATAU pimpinan proyek.
     */
    public function manageMembers(User $user, Project $project): bool
    {
        return $user->id === $project->owner_id || $user->id === $project->leader_id;
    }

    /**
     * Tentukan apakah user bisa menghapus proyek.
     * Aturan: User adalah pemilik proyek ATAU atasan dari pemilik (izin paling ketat).
     */
    public function delete(User $user, Project $project): bool
    {
        // Fallback untuk data lama yang mungkin belum punya owner
        if (!$project->owner) {
            return $user->id === $project->leader_id;
        }
        return $user->id === $project->owner_id || $project->owner->isSubordinateOf($user);
    }

    /**
     * Tentukan apakah user bisa melihat dasbor tim.
     * Aturan: Hanya pimpinan proyek.
     */
    public function viewTeamDashboard(User $user, Project $project): bool
    {
        return $user->id === $project->leader_id;
    }

    /**
     * Metode-metode di bawah ini sengaja dibuat false karena tidak digunakan.
     * Namun, sebaiknya tetap ada untuk kelengkapan.
     */
    public function viewAny(User $user): bool
    {
        return true; // Dibiarkan true, controller yang akan memfilter
    }

    public function restore(User $user, Project $project): bool
    {
        return false;
    }

    public function forceDelete(User $user, Project $project): bool
    {
        return false;
    }
}