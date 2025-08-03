<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;

class HomeController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function index(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user->role === User::ROLE_ESELON_I || $user->role === User::ROLE_ESELON_II) {
            return redirect()->route('executive.summary');
        }

        return redirect()->route('global.dashboard');
    }
}
