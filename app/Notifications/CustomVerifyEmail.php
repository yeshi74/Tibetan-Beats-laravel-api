<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as VerifyEmailNotification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Carbon;

class CustomVerifyEmail extends VerifyEmailNotification
{
    /**
     * Build the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        // Generate verification URL (same logic as Laravel’s default)
        $verificationUrl = $this->verificationUrl($notifiable);

        // Pass user and url to your Blade template
        return (new MailMessage)
            ->subject('Verify Your Account on Tibetan Music 🎵')
            ->markdown(
                'vendor.notifications.email', // <-- points to your customized email.blade.php
                [
                    'url' => $verificationUrl,
                    'user' => $notifiable, // 👈 so we can use $user->name
                ]
            );
    }

    /**
     * Get the verification URL for the given notifiable.
     */
    protected function verificationUrl($notifiable)
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(config('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }
}
