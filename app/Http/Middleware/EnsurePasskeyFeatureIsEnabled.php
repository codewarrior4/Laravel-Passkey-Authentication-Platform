<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasskeyFeatureIsEnabled
{
    public function handle(Request $request, Closure $next, string $feature = 'enabled'): Response
    {
        if ($this->isDisabled('enabled') || $this->isDisabled($feature)) {
            return $this->disabledResponse($request, $this->messageFor($feature));
        }

        return $next($request);
    }

    private function disabledResponse(Request $request, string $message): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson() || $request->isJson()) {
            return response()->json([
                'message' => $message,
            ], 403);
        }

        return to_route('passkeys.overview')->with('status', $message);
    }

    private function isDisabled(string $feature): bool
    {
        return ! (bool) data_get(config('passkeys.feature_flags'), $feature.'.active', false);
    }

    private function messageFor(string $feature): string
    {
        return match ($feature) {
            'registration' => 'Passkey registration is temporarily unavailable.',
            'login' => 'Passkey sign-in is temporarily unavailable.',
            'device_management' => 'Device management is temporarily unavailable.',
            default => 'Passkeys are temporarily unavailable.',
        };
    }
}
