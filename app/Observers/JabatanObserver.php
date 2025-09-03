<?php

namespace App\Observers;

use App\Models\Jabatan;
use App\Models\JabatanHistory;
use Carbon\Carbon;

class JabatanObserver
{
    /**
     * Handle the Jabatan "updating" event.
     *
     * @param  \App\Models\Jabatan  $jabatan
     * @return void
     */
    public function updating(Jabatan $jabatan)
    {
        // Check if the user_id is being changed.
        if ($jabatan->isDirty('user_id')) {
            $oldUserId = $jabatan->getOriginal('user_id');
            $newUserId = $jabatan->user_id;

            // End the previous user's history for this position
            if ($oldUserId) {
                JabatanHistory::where('jabatan_id', $jabatan->id)
                    ->where('user_id', $oldUserId)
                    ->whereNull('end_date')
                    ->update(['end_date' => Carbon::today()]);
            }

            // Create a new history record for the new user
            if ($newUserId) {
                JabatanHistory::create([
                    'user_id' => $newUserId,
                    'jabatan_id' => $jabatan->id,
                    'unit_id' => $jabatan->unit_id,
                    'start_date' => Carbon::today(),
                    'end_date' => null,
                ]);
            }
        }
    }

    /**
     * Handle the Jabatan "created" event.
     *
     * @param  \App\Models\Jabatan  $jabatan
     * @return void
     */
    public function created(Jabatan $jabatan)
    {
        // If a new position is created with a user already assigned
        if ($jabatan->user_id) {
            JabatanHistory::create([
                'user_id' => $jabatan->user_id,
                'jabatan_id' => $jabatan->id,
                'unit_id' => $jabatan->unit_id,
                'start_date' => Carbon::today(),
                'end_date' => null,
            ]);
        }
    }
}
