<?php
namespace App\Http\Middleware;

use Closure;

class RejectBanned {
    public function handle($request, Closure $next) {
        $u = $request->user();
        if ($u && $u->is_banned && !$request->is('api/admin/*')) {
            return response()->json(['message'=>'Account banned'], 403);
        }
        return $next($request);
    }
}
