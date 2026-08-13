<?php

namespace App\Http\Middleware;

use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRecentPasskeyAuthentication
{
    public function handle(Request $request, Closure $next): Response
    {
        $authenticatedAt = $request->session()->get('passkey_last_authenticated_at');

        if (! is_string($authenticatedAt) || CarbonImmutable::parse($authenticatedAt)->diffInMinutes(now()) > 10) {
            return to_route('passkeys.login')->with('status', 'Please sign in again before managing devices or passkeys.');
        }

        return $next($request);
    }
}
