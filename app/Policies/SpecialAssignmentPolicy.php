<?php

namespace App\Policies;

use App\Models\SpecialAssignment;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SpecialAssignmentPolicy
{
    /**
     * Tentukan apakah user bisa membuat SK penugasan baru.
     */
    public function create(User $user): bool
    {
        // Pimpinan (Koordinator ke atas) bisa membuat SK baru untuk timnya.
        return $user->canManageUsers(); 
    }

    /**
     * Tentukan apakah user bisa mengupdate SK.
     */
    public function update(User $user, SpecialAssignment $specialAssignment): bool
    {
        // Hanya pembuat SK atau atasan dari pembuat yang bisa mengedit.
        return $user->id === $specialAssignment->creator_id || ($specialAssignment->creator && $specialAssignment->creator->isSubordinateOf($user));
    }

    /**
     * Tentukan apakah user bisa menghapus SK.
     */
    public function delete(User $user, SpecialAssignment $specialAssignment): bool
    {
        // Aturannya sama dengan update.
        return $this->update($user, $specialAssignment);
    }
}