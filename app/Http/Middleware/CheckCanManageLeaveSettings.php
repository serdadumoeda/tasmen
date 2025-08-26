<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckCanManageLeaveSettings
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect('login');
        }

        $user = Auth::user();

        // Allow if user is Super Admin
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Allow if user's position (jabatan) can manage users
        if ($user->jabatan && $user->jabatan->can_manage_users) {
            return $next($request);
        }

        abort(403, 'ANDA TIDAK MEMILIKI HAK AKSES.');
    }
}
