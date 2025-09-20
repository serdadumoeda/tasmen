<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\Jabatan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class CompleteProfileController extends Controller
{
    /**
     * Show the form for the user to complete their profile.
     */
    public function create()
    {
        // Redirect if profile is already complete
        if (Auth::user()->unit_id) {
            return redirect()->route('dashboard');
        }

        // Fetch units at Eselon I level. Based on the hierarchy logic in the Unit model
        // (getExpectedHeadRole), an Eselon I unit is at depth 2, meaning it has 2 ancestors.
        $eselonIUnits = Unit::withCount('ancestors')->having('ancestors_count', 2)->orderBy('name')->get();
        $selectedUnitPath = []; // For the form partial

        return view('profile.complete', compact('eselonIUnits', 'selectedUnitPath'));
    }

    /**
     * Store the completed profile information.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'unit_id' => ['required', 'exists:units,id'],
            // Validate that the jabatan_id exists and is not already taken by another user.
            'jabatan_id' => ['required', 'exists:jabatans,id,user_id,NULL'],
        ]);

        $user = Auth::user();

        if ($user->unit_id) {
            return redirect()->route('dashboard')->with('info', 'Profil Anda sudah lengkap.');
        }

        DB::transaction(function () use ($validated, $user) {
            // Find the selected Jabatan, which must be vacant.
            $jabatan = Jabatan::where('id', $validated['jabatan_id'])
                              ->whereNull('user_id')
                              ->firstOrFail();

            // Assign the user to the selected Jabatan.
            $jabatan->user_id = $user->id;
            $jabatan->save();

            // Update the user's unit_id to match their new Jabatan's unit.
            // This ensures consistency.
            $user->unit_id = $jabatan->unit_id;
            $user->save();

            // This static method will set the user's main role (Eselon, etc.)
            // based on the unit they joined.
            User::syncRoleFromUnit($user);
        });

        return redirect()->route('dashboard')->with('success', 'Profil Anda telah berhasil diperbarui!');
    }
}
