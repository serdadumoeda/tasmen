<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Memberikan hak akses superadmin untuk semua aksi.
     */
    public function before(User $user, string $ability): bool|null
    {
        if ($user->role === User::ROLE_SUPERADMIN) {
            return true;
        }
        return null;
    }

    /**
     * Tentukan apakah user bisa melihat daftar user.
     */
    public function viewAny(User $user): bool
    {
        return $user->canManageUsers();
    }

    /**
     * Tentukan apakah user bisa melihat detail user lain.
     */
    public function view(User $user, User $model): bool
    {
        return $model->isSubordinateOf($user);
    }

    /**
     * Tentukan apakah user bisa membuat user baru.
     */
    public function create(User $user): bool
    {
        return $user->canManageUsers();
    }

    /**
     * Tentukan apakah user bisa mengedit data user lain.
     */
    public function update(User $user, User $model): bool
    {
        // Pengguna bisa mengedit bawahannya.
        return $model->isSubordinateOf($user);
    }

    /**
     * Tentukan apakah user bisa menghapus user lain.
     */
    public function delete(User $user, User $model): bool
    {
        if ($user->id === $model->id) {
            return false;
        }
        return $model->isSubordinateOf($user);
    }

    /**
     * Tentukan apakah seorang manajer bisa menilai perilaku kerja seorang user.
     */
    public function rateBehavior(User $manager, User $subordinate): bool
    {
        // Aturan 1: Eselon I bisa menilai Eselon II yang unitnya berada langsung di bawahnya.
        if ($manager->role === User::ROLE_ESELON_I && $subordinate->role === User::ROLE_ESELON_II) {
            // Memastikan unit subordinate tidak null dan memiliki parent_unit_id
            if ($subordinate->unit && $subordinate->unit->parent_unit_id === $manager->unit_id) {
                return true;
            }
        }

        // Aturan 2: Eselon II bisa menilai SEMUA di bawah hierarki unitnya.
        if ($manager->role === User::ROLE_ESELON_II) {
            return $subordinate->isSubordinateOf($manager);
        }

        // Default: tolak jika tidak ada aturan yang cocok.
        return false;
    }
}