<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\Jabatan;
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

        $user = Auth::user();
        // Get all Eselon I units by finding the children of the root unit.
        $rootUnit = Unit::whereNull('parent_unit_id')->first();
        $eselonIUnits = $rootUnit ? $rootUnit->childUnits()->orderBy('name')->get() : collect();
        $selectedUnitPath = [];
        // The form partial now handles cases where these are not passed.
        return view('profile.complete', compact('user', 'eselonIUnits', 'selectedUnitPath'));
    }

    /**
     * Store the completed profile information.
     */
    public function store(Request $request)
    {
        $request->validate([
            'unit_id' => ['required', 'exists:units,id'],
            'jabatan_id' => ['required', 'exists:jabatans,id'],
        ]);

        $user = Auth::user();

        // Double-check if the user is already set up to prevent race conditions or re-submissions.
        if ($user->unit_id) {
            return redirect()->route('dashboard')->with('info', 'Profil Anda sudah lengkap.');
        }

        DB::transaction(function () use ($request, $user) {
            $jabatan = Jabatan::with('unit')->find($request->jabatan_id);

            // Re-validate that the position is still vacant inside the transaction
            if (!$jabatan || $jabatan->user_id) {
                throw ValidationException::withMessages([
                    'jabatan_id' => __('Jabatan yang dipilih tidak lagi tersedia. Silakan pilih jabatan lain.'),
                ]);
            }

            // Update the user with their new unit and role from the chosen Jabatan
            $user->unit_id = $jabatan->unit_id;
            $user->role = $jabatan->role;
            $user->save();

            // Assign the user to the Jabatan
            $jabatan->user_id = $user->id;
            $jabatan->save();
        });

        return redirect()->route('dashboard')->with('success', 'Profil Anda telah berhasil diperbarui!');
    }
}
