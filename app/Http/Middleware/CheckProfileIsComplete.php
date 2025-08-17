<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckProfileIsComplete
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // If the user is authenticated, their profile is incomplete (no unit_id),
        // AND THEY ARE NOT A SUPERADMIN,
        // and they are not already on the 'complete profile' page or trying to log out,
        // then redirect them.
        if ($user && !$user->isSuperAdmin() && is_null($user->unit_id) && !$request->routeIs('profile.complete.*') && !$request->routeIs('logout')) {
            return redirect()->route('profile.complete.create')->with('warning', 'Harap lengkapi profil Anda untuk melanjutkan.');
        }

        return $next($request);
    }
}
