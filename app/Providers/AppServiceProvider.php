<?php

namespace App\Providers;

use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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
        Authenticate::redirectUsing(static fn (): string => route('passkeys.login'));

        RateLimiter::for('passkey-registration', function (Request $request): array {
            return [
                Limit::perMinute(10)->by((string) $request->ip()),
                Limit::perMinute(5)->by('registration:'.$request->string('work_email')->toString()),
            ];
        });

        RateLimiter::for('passkey-login-start', function (Request $request): array {
            return [
                Limit::perMinute(12)->by((string) $request->ip()),
                Limit::perMinute(20)->by('login-start:'.$request->session()->getId()),
            ];
        });

        RateLimiter::for('passkey-login-finish', function (Request $request): array {
            return [
                Limit::perMinute(10)->by((string) $request->ip()),
                Limit::perMinute(8)->by('login-finish:'.$request->session()->getId()),
            ];
        });

        RateLimiter::for('passkey-device-actions', function (Request $request): array {
            return [
                Limit::perMinute(20)->by((string) ($request->user()?->id ?? $request->ip())),
            ];
        });
    }
}
