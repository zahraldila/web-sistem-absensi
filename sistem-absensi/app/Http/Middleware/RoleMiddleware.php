<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = Auth::user();
        if (! $user) {
            return redirect('/login');
        }

        $userRole = null;
        if (!empty($user->role_id) && $user->roleAkses) {
            $userRole = $user->roleAkses->nama_role;
        } else {
            $userRole = $user->role;
        }

        if (empty($userRole)) {
            abort(403, 'Akses ditolak: Anda tidak memiliki role yang valid.');
        }

        $hasRole = false;
        foreach ($roles as $r) {
            if (strtolower($userRole) === strtolower($r)) {
                $hasRole = true;
                break;
            }
        }

        if (!$hasRole) {
            abort(403, 'Akses ditolak: Role Anda tidak memiliki izin untuk halaman ini.');
        }

        return $next($request);
    }
}
