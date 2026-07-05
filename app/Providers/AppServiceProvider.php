<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use App\Models\SupportMessage;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ✅ Override the default password reset URL for API usage
        ResetPassword::createUrlUsing(function ($user, string $token) {
            return config('app.frontend_url') . '/reset-password?token=' . $token . '&email=' . urlencode($user->email);
        });

        Paginator::useBootstrapFive();

        View::composer('layouts.admin', function ($view) {
            $openSupportCount = SupportMessage::where('status', 'open')->count();

            $view->with('openSupportCount', $openSupportCount);
        });

        
    }
}
