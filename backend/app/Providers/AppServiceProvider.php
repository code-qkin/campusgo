<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Force HTTPS in production
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        // Email verification link points to our backend web route (signed),
        // which then redirects to the frontend after verifying.
        VerifyEmail::createUrlUsing(function ($notifiable) {
            return URL::temporarySignedRoute(
                'verification.verify',
                now()->addMinutes(60),
                [
                    'id'   => $notifiable->getKey(),
                    'hash' => sha1($notifiable->getEmailForVerification()),
                ]
            );
        });

        // Password reset link goes directly to the frontend reset-password page.
        ResetPassword::createUrlUsing(function ($notifiable, string $token) {
            $frontend = rtrim(config('app.frontend_url'), '/');
            return $frontend . '/reset-password?token=' . $token . '&email=' . urlencode($notifiable->email);
        });
    }
}
