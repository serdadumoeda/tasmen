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
        // Jika user tidak login ATAU rolenya bukan superadmin
        if (!auth()->check() || auth()->user()->role !== User::ROLE_SUPERADMIN) {
            abort(403, 'ANDA TIDAK MEMILIKI HAK AKSES.');
        }

        return $next($request);
    }
}