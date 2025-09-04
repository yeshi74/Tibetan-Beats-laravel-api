<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $u = $request->user();

        if (!$u || !$u->role || !in_array($u->role->name, $roles, true) || ($u->is_banned ?? false)) {
            abort(403, 'Unauthorized');
        }

        return $next($request);
    }
}
