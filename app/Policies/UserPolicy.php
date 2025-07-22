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
    public function create(User $user, array $attributes): bool
    {
        if (!$user->canManageUsers()) {
            return false;
        }

        // Pastikan manager tidak membuat user dengan role lebih tinggi dari dirinya
        $roleOrder = [
            User::ROLE_STAF => 0,
            User::ROLE_SUB_KOORDINATOR => 1,
            User::ROLE_KOORDINATOR => 2,
            User::ROLE_ESELON_II => 3,
            User::ROLE_ESELON_I => 4,
            User::ROLE_SUPERADMIN => 5,
        ];

        if ($roleOrder[$attributes['role']] >= $roleOrder[$user->role]) {
            return false;
        }

        // Pastikan manager hanya membuat user di dalam unitnya atau unit bawahannya
        if ($user->unit) {
            return in_array($attributes['unit_id'], $user->unit->getAllSubordinateUnitIds());
        }

        return false;
    }

    /**
     * Tentukan apakah user bisa mengedit data user lain.
     */
    public function update(User $user, User $model, array $attributes): bool
    {
        if (!$model->isSubordinateOf($user)) {
            return false;
        }

        // Logika yang sama dengan create
        $roleOrder = [
            User::ROLE_STAF => 0,
            User::ROLE_SUB_KOORDINATOR => 1,
            User::ROLE_KOORDINATOR => 2,
            User::ROLE_ESELON_II => 3,
            User::ROLE_ESELON_I => 4,
            User::ROLE_SUPERADMIN => 5,
        ];

        if (isset($attributes['role']) && $roleOrder[$attributes['role']] >= $roleOrder[$user->role]) {
            return false;
        }

        if (isset($attributes['unit_id']) && $user->unit) {
            return in_array($attributes['unit_id'], $user->unit->getAllSubordinateUnitIds());
        }

        return true;
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