<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
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
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(RegisterRequest $request): RedirectResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'nip' => $request->nip,
            'password' => Hash::make($request->password),
            'unit_id' => null, // No unit assigned on registration
            'status' => User::STATUS_ACTIVE,
        ]);

        // Assign the default 'Staf' role
        $stafRole = Role::where('name', 'Staf')->first();
        if ($stafRole) {
            $user->roles()->attach($stafRole);
        }

        event(new Registered($user));

        Auth::login($user);

        // Redirect to the dashboard, the new middleware will handle the rest.
        return redirect(route('dashboard', absolute: false));
    }
}