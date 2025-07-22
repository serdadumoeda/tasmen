<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Unit;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use App\Http\Requests\Auth\RegisterRequest;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        $eselon1Units = Unit::where('level', 1)->get();
        return view('auth.register', ['eselon1Units' => $eselon1Units]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(RegisterRequest $request): RedirectResponse
    {
        // Validasi tambahan untuk unit_id
        if (empty($request->unit_id)) {
            return back()->withErrors(['unit_id' => 'Anda harus memilih unit kerja minimal sampai Eselon II.'])->withInput();
        }

        $unit = Unit::find($request->unit_id);

        // Memastikan unit yang dipilih bukan Eselon I (yang parent_id nya null)
        if ($unit && is_null($unit->parent_id)) {
            return back()->withErrors(['unit_id' => 'Pemilihan unit kerja tidak boleh hanya sampai Eselon I.'])->withInput();
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'unit_id' => $request->unit_id,
            'role' => User::ROLE_STAF,
            'status' => User::STATUS_ACTIVE,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}