<?php

namespace App\Observers;

use App\Models\Setting;
use App\Models\Activity;
use Illuminate\Support\Facades\Auth;

class SettingObserver
{
    /**
     * Handle the Setting "updated" event.
     */
    public function updated(Setting $setting): void
    {
        // We only want to log changes to important, formula-related settings.
        $keysToLog = [
            'iki_formula',
            'nkf_formula_staf',
            'nkf_formula_pimpinan',
            'min_efficiency_factor',
            'max_efficiency_factor',
        ];

        if (in_array($setting->key, $keysToLog)) {
            if ($setting->isDirty('value')) { // Only log if the value actually changed
                Activity::create([
                    'user_id' => Auth::id(),
                    'project_id' => null, // This is a system-wide activity
                    'description' => 'updated_setting',
                    'subject_id' => $setting->id,
                    'subject_type' => Setting::class,
                    'before' => json_encode(['value' => $setting->getOriginal('value')]),
                    'after' => json_encode(['value' => $setting->value]),
                ]);
            }
        }
    }
}
