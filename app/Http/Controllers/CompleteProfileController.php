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

        // PERBAIKAN: Ambil semua unit dengan level 'Eselon I'
        $eselonIUnits = Unit::where('level', 'Eselon I')->orderBy('name')->get();
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
            $jabatan = Jabatan::create([
                'name' => $validated['jabatan_name'],
                'unit_id' => $validated['unit_id'],
                'user_id' => $user->id,
                'role' => 'Staf', // Default role for self-completion
            ]);

            $user->unit_id = $validated['unit_id'];
            $user->save();

            User::recalculateAndSaveRole($user);
        });

        return redirect()->route('dashboard')->with('success', 'Profil Anda telah berhasil diperbarui!');
    }
}
