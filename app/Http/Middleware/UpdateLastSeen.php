<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class UpdateLastSeen
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user()) {

            if (
                !$request->user()->last_seen_at ||
                $request->user()->last_seen_at->lt(now()->subMinute())
            ) {
                $request->user()->forceFill([
                    'last_seen_at' => now(),
                ])->saveQuietly();
            }
        }

        return $next($request);
    }
}