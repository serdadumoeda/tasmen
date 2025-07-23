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
}