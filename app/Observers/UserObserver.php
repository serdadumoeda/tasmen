<?php

namespace App\Observers;

use App\Models\User;
use App\Models\JabatanHistory;

class UserObserver
{
    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        if ($user->isDirty('jabatan_id') || $user->isDirty('unit_id')) {
            // End the previous history record
            JabatanHistory::where('user_id', $user->id)
                ->whereNull('end_date')
                ->latest('start_date')
                ->first()
                ?->update(['end_date' => now()]);

            // Create a new history record
            if ($user->jabatan_id && $user->unit_id) {
                JabatanHistory::create([
                    'user_id' => $user->id,
                    'jabatan_id' => $user->jabatan_id,
                    'unit_id' => $user->unit_id,
                    'start_date' => now(),
                    'end_date' => null,
                ]);
            }
        }
    }

    /**
     * Handle the User "created" event to set the initial position.
     */
    public function created(User $user): void
    {
        if ($user->jabatan_id && $user->unit_id) {
            JabatanHistory::create([
                'user_id' => $user->id,
                'jabatan_id' => $user->jabatan_id,
                'unit_id' => $user->unit_id,
                'start_date' => now(),
                'end_date' => null,
            ]);
        }
    }
}
