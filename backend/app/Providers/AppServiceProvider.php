<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

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
        // 1. Enforce HTTPS when in production or when FORCE_HTTPS is true
        if (app()->environment('production') || env('FORCE_HTTPS', false)) {
            URL::forceScheme('https');
            if (request()) {
                request()->server->set('HTTPS', 'on');
            }
        }

        // 2. Set Strong Global Password Policy (min 8 chars, letters, numbers)
        Password::defaults(function () {
            return Password::min(8)
                ->letters()
                ->numbers();
        });

        // 3. Implicitly grant "Super Admin" role all permissions
        Gate::before(function ($user, $ability) {
            return $user->hasRole('Super Admin') ? true : null;
        });

        // 4. Password Reset URL Configuration
        ResetPassword::createUrlUsing(function (User $user, string $token) {
            $frontendUrl = rtrim(env('FRONTEND_URL', 'http://localhost:5173'), '/');
            return "{$frontendUrl}/reset-password?token={$token}&email=" . urlencode($user->email);
        });
    }
}
