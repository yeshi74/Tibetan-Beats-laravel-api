<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\Dedication;
use App\Policies\DedicationPolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Dedication::class => DedicationPolicy::class,
        \App\Models\Dedication::class => \App\Policies\DedicationPolicy::class,
    ];

    public function boot(): void
    {
        // Policies are auto-registered from $policies.
    }
}
