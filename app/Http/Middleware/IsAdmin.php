<?php


namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        // Example check: user must be admin (e.g. is_admin = true)
        if (!auth()->check() || !auth()->user()->is_admin) {
            abort(403, 'Access denied.');
        }

        return $next($request);
    }
}

