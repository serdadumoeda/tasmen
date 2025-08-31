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
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Surat $surat): bool
    {
        // Only the user who created the letter can delete it.
        // Admins are handled by the Gate::before callback in AuthServiceProvider.
        return $user->id === $surat->pembuat_id;
    }
}
