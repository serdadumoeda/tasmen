<?php

namespace App\Policies;

use App\Models\SpecialAssignment;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SpecialAssignmentPolicy
{
    /**
     * Tentukan apakah user bisa melihat SK penugasan.
     */
    public function view(User $user, SpecialAssignment $specialAssignment): bool
    {
        // Superadmin bisa melihat semua
        if ($user->hasRole('Superadmin')) {
            return true;
        }

        // Pembuat atau anggota tim bisa melihat
        if ($specialAssignment->creator_id === $user->id || $specialAssignment->users()->where('user_id', $user->id)->exists()) {
            return true;
        }

        // Manajer dari pembuat bisa melihat
        if ($user->isManager() && $user->unit && $specialAssignment->creator && $specialAssignment->creator->unit) {
            return in_array($specialAssignment->creator->unit->id, $user->unit->getAllSubordinateUnitIds());
        }

        return false;
    }

    /**
     * Tentukan apakah user bisa membuat SK penugasan baru.
     */
    public function create(User $user): bool
    {
        return $user->canManageUsers();
    }

    /**
     * Tentukan apakah user bisa mengupdate SK.
     */
    public function update(User $user, SpecialAssignment $specialAssignment): bool
    {
        // Superadmin bisa update
        if ($user->hasRole('Superadmin')) {
            return true;
        }

        // Pembuat bisa update
        if ($user->id === $specialAssignment->creator_id) {
            return true;
        }

        // Manajer dari pembuat bisa update
        if ($user->isManager() && $user->unit && $specialAssignment->creator && $specialAssignment->creator->unit) {
            return in_array($specialAssignment->creator->unit->id, $user->unit->getAllSubordinateUnitIds());
        }

        return false;
    }

    /**
     * Tentukan apakah user bisa menghapus SK.
     */
    public function delete(User $user, SpecialAssignment $specialAssignment): bool
    {
        return $this->update($user, $specialAssignment);
    }
}
