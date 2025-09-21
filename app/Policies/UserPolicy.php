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
        if ($user->hasRole('Superadmin')) {
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
        // Block Eselon I and Eselon II from editing any user.
        if ($user->hasRole(['Eselon I', 'Eselon II'])) {
            return false;
        }

        // Delegated admin with Eselon II scope
        if ($user->jabatan?->can_manage_users) {
            $managerEselonIIUnit = $user->unit?->getEselonIIAncestor();

            if ($managerEselonIIUnit) {
                // Get all unit IDs under the manager's Eselon II scope.
                $authorizedUnitIds = $managerEselonIIUnit->descendants()->pluck('id');
                $authorizedUnitIds->push($managerEselonIIUnit->id); // Include the Eselon II unit itself.

                // Check if the target user's unit is within that scope.
                if ($model->unit_id && $authorizedUnitIds->contains($model->unit_id)) {
                    return true;
                }
            }
        }

        // Default logic: a user can edit their own direct subordinates.
        return $model->isSubordinateOf($user);
    }

    /**
     * Tentukan apakah user bisa menonaktifkan user lain.
     */
    public function deactivate(User $user, User $model): bool
    {
        // Block Eselon I and Eselon II from deactivating any user.
        if ($user->hasRole(['Eselon I', 'Eselon II'])) {
            return false;
        }

        if ($user->id === $model->id) {
            return false; // Cannot deactivate self
        }

        // Delegated admin with Eselon II scope
        if ($user->jabatan?->can_manage_users) {
            $managerEselonIIUnit = $user->unit?->getEselonIIAncestor();

            if ($managerEselonIIUnit) {
                // Get all unit IDs under the manager's Eselon II scope.
                $authorizedUnitIds = $managerEselonIIUnit->descendants()->pluck('id');
                $authorizedUnitIds->push($managerEselonIIUnit->id);

                // Check if the target user's unit is within that scope.
                if ($model->unit_id && $authorizedUnitIds->contains($model->unit_id)) {
                    return true;
                }
            }
        }

        // A manager can deactivate their own direct subordinates.
        return $model->isSubordinateOf($user);
    }

    /**
     * Tentukan apakah user bisa mengaktifkan kembali user lain.
     */
    public function reactivate(User $user, User $model): bool
    {
        // Logic is identical to deactivation.
        return $this->deactivate($user, $model);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        // Only superadmins can force delete, and only if the user is already suspended.
        return $user->isSuperAdmin() && $model->status === User::STATUS_SUSPENDED;
    }

    /**
     * Tentukan apakah seorang manajer bisa menilai perilaku kerja seorang user.
     */
    public function rateBehavior(User $manager, User $subordinate): bool
    {
        // Aturan 1: Eselon I bisa menilai Eselon II yang unitnya berada langsung di bawahnya.
        if ($manager->hasRole('Eselon I') && $subordinate->hasRole('Eselon II')) {
            // Memastikan unit subordinate tidak null dan memiliki parent_unit_id
            if ($subordinate->unit && $subordinate->unit->parent_unit_id === $manager->unit_id) {
                return true;
            }
        }

        // Aturan 2: Eselon II bisa menilai SEMUA di bawah hierarki unitnya.
        if ($manager->hasRole('Eselon II')) {
            return $subordinate->isSubordinateOf($manager);
        }

        // Default: tolak jika tidak ada aturan yang cocok.
        return false;
    }
}