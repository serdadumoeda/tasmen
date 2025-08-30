<?php

namespace App\Policies;

use App\Models\LampiranSurat;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class LampiranSuratPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, LampiranSurat $lampiranSurat): bool
    {
        $surat = $lampiranSurat->surat;

        // Allow if the user is the creator of the letter
        if ($user->id === $surat->pembuat_id) {
            return true;
        }

        // Allow if the user is the approver of the letter
        if ($user->id === $surat->penyetuju_id) {
            return true;
        }

        // Allow if the user has received a disposition for this letter
        return $surat->disposisi()->where('penerima_id', $user->id)->exists();
    }
}
