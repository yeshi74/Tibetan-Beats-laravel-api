<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use App\Models\FeatureFlag;

class Feature
{
    /**
     * Check if a feature is enabled. Cached for $ttlSeconds seconds.
     */
    public static function enabled(string $key, int $ttlSeconds = 60): bool
    {
        return Cache::remember("feature_flag:{$key}", $ttlSeconds, function () use ($key) {
            return (bool) FeatureFlag::where('key', $key)->value('enabled');
        });
    }

    /** Clear cache for a single feature key */
    public static function clearCache(string $key): void
    {
        Cache::forget("feature_flag:{$key}");
    }

    /** Optional: clear multiple or all flags (not implemented) */
    public static function clearAll(): void
    {
        // Implement if you keep a list of keys or want to flush everything (careful)
    }
}
