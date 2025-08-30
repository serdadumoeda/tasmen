<?php

namespace App\Policies;

use App\Models\Surat;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SuratPolicy
{
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
