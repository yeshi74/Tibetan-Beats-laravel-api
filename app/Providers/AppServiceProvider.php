<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
        
    //         VerifyEmail::toMailUsing(function ($notifiable, $url) {
    //     // Instead of Laravel's default /login redirect,
    //     // send them to your frontend / Flutter deep link
    //     $frontendUrl = config('app.frontend_url') . '/verify-email?verify_url=' . urlencode($url);

    //     return (new MailMessage)
    //         ->subject('Verify Your Email Address')
    //         ->line('Click the button below to verify your email address.')
    //         ->action('Verify Email', $frontendUrl)
    //         ->line('If you did not create an account, no further action is required.');
    // });

    }
}
