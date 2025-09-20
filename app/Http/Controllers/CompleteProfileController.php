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

        // Find the root unit (Kementerian) and get its direct children (Eselon I)
        $rootUnit = Unit::whereNull('parent_unit_id')->first();
        $eselonIUnits = $rootUnit ? $rootUnit->childUnits()->orderBy('name')->get() : collect();
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
            'jabatan_name' => ['required', 'string', 'max:255'],
        ]);

        $user = Auth::user();

        if ($user->unit_id) {
            return redirect()->route('dashboard')->with('info', 'Profil Anda sudah lengkap.');
        }

        DB::transaction(function () use ($validated, $user) {
            // Create a new Jabatan for the user based on their input
            $jabatan = Jabatan::create([
                'name' => $validated['jabatan_name'],
                'unit_id' => $validated['unit_id'],
                'user_id' => $user->id,
            ]);

            // Update the user's unit_id and recalculate their role based on hierarchy
            $user->unit_id = $validated['unit_id'];
            $user->save();

            // This static method will set the user's main role (Eselon, etc.)
            // based on the unit they joined.
            User::syncRoleFromUnit($user);
        });

        return redirect()->route('dashboard')->with('success', 'Profil Anda telah berhasil diperbarui!');
    }
}
