<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Unit;
use App\Models\Jabatan;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use App\Http\Requests\Auth\RegisterRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        // Correctly fetch Eselon I units using the constant from the Unit model.
        $eselonIUnits = Unit::where('level', Unit::LEVEL_ESELON_I)->orderBy('name')->get();

        // Pass a variable for the selected path for consistency with the form, even though it's empty on register.
        $selectedUnitPath = [];

        return view('auth.register', compact('eselonIUnits', 'selectedUnitPath'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(RegisterRequest $request): RedirectResponse
    {
        $user = DB::transaction(function () use ($request) {
            $jabatan = Jabatan::with('unit')->find($request->jabatan_id);

            // Re-validate that the position is still vacant inside the transaction
            if (!$jabatan || $jabatan->user_id) {
                throw ValidationException::withMessages([
                    'jabatan_id' => __('Jabatan yang dipilih tidak lagi tersedia. Silakan pilih jabatan lain.'),
                ]);
            }

            // Create the user, deriving unit and role from the chosen Jabatan
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'unit_id' => $jabatan->unit_id,
                'role' => $jabatan->unit->level,
                'status' => User::STATUS_ACTIVE,
            ]);

            // Assign the new user to the Jabatan
            $jabatan->user_id = $user->id;
            $jabatan->save();

            return $user;
        });

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}