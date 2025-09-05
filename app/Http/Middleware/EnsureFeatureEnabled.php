<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Support\Feature;

class EnsureFeatureEnabled
{
    /**
     * Handle an incoming request.
     * Usage: ->middleware('feature:dedications')
     */
    public function handle(Request $request, Closure $next, $key)
    {
        if (! Feature::enabled($key)) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Feature disabled',
                ], 403);
            }
            // For web routes, show a 404 to hide endpoint
            abort(404);
        }

        return $next($request);
    }
}
