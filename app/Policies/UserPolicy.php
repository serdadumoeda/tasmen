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
        if ($user->role === 'Superadmin') {
            return true;
        }
        return null;
    }

    /**
     * Tentukan apakah user bisa melihat daftar user.
     * Dibuat true agar semua pimpinan bisa masuk ke halaman,
     * tapi controller akan memfilter daftar yang ditampilkan.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['Eselon I', 'Eselon II', 'Koordinator']);
    }

    /**
     * Tentukan apakah user bisa melihat detail user lain.
     * bisa melihat detail bawahan.
     */
    public function view(User $user, User $model): bool
    {
        return $model->isSubordinateOf($user);
    }

    /**
     * Tentukan apakah user bisa membuat user baru.
     * Semua pimpinan bisa membuat user baru (untuk menjadi bawahannya).
     */
    public function create(User $user): bool
    {
        return in_array($user->role, ['Eselon I', 'Eselon II', 'Koordinator']);
    }

    /**
     * Tentukan apakah user bisa mengedit data user lain.
     * bisa mengedit data bawahan .
     */
    public function update(User $user, User $model): bool
    {
        return $model->isSubordinateOf($user);
    }

    /**
     * Tentukan apakah user bisa menghapus user lain.
     * bisa menghapus bawahan .
     * User tidak bisa menghapus dirinya sendiri.
     */
    public function delete(User $user, User $model): bool
    {
        if ($user->id === $model->id) {
            return false;
        }
        return $model->isSubordinateOf($user);
    }
}