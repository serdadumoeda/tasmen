<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSuperadmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // If an admin is impersonating another user, they should be able to leave.
        // The original user's ID is in the session, proving they are an admin.
        if (session()->has('impersonator_id')) {
            return $next($request);
        }

        // If user is not logged in OR their role is not superadmin
        if (!auth()->check() || auth()->user()->role !== User::ROLE_SUPERADMIN) {
            abort(403, 'ANDA TIDAK MEMILIKI HAK AKSES.');
        }

        return $next($request);
    }
}