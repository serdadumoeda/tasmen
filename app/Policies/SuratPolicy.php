<?php

namespace App\Policies;

use App\Models\Surat;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SuratPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Surat $surat): bool
    {
        // Allow if the user is the creator, approver, or a disposition recipient
        if ($user->id === $surat->pembuat_id || $user->id === $surat->penyetuju_id) {
            return true;
        }

        return $surat->disposisi()->where('penerima_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can download the final PDF.
     */
    public function download(User $user, Surat $surat): bool
    {
        // The logic for viewing and downloading is the same.
        return $this->view($user, $surat);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Surat $surat): bool
    {
        // Allow updating only if the letter is a draft and the user is the creator or a collaborator.
        return $surat->status === 'draft' && ($user->id === $surat->pembuat_id || $surat->isCollaborator($user));
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Surat $surat): bool
    {
        // Only the creator or a collaborator can delete a draft letter.
        return $surat->status === 'draft' && ($user->id === $surat->pembuat_id || $surat->isCollaborator($user));
    }

    /**
     * Determine whether the user can create a project from the letter.
     */
    public function makeProject(User $user, Surat $surat): bool
    {
        // Staff members and regular users cannot create projects from a letter.
        if ($user->isStaff() || $user->hasRole('User')) {
            return false;
        }

        return true;
    }
}
