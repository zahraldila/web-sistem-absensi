<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PrivilegeMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, $privilege)
    {
        $user = Auth::user();
        if (! $user) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect('/login');
        }

        // Authorization depends on role_id mapping if available
        if (!empty($user->role_id) && $user->roleAkses) {
            if (!$user->roleAkses->hasPrivilege($privilege)) {
                abort(403, "Akses ditolak: Anda tidak memiliki privilege [{$privilege}].");
            }
        } else {
            // Fallback for string legacy role without role_id
            $roleStr = strtolower($user->role);
            if ($roleStr === 'super admin') {
                // Legacy Super Admin bypass
            } else {
                abort(403, "Akses ditolak: Akun Anda belum dipetakan ke role yang memiliki privilege [{$privilege}].");
            }
        }

        return $next($request);
    }
}
