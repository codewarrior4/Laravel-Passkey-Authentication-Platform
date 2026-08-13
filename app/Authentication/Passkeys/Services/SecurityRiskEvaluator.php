<?php

namespace App\Authentication\Passkeys\Services;

use App\Authentication\Passkeys\ValueObjects\RiskScore;
use App\Models\AuthenticationEvent;
use App\Models\Passkey;
use Illuminate\Http\Request;

final class SecurityRiskEvaluator
{
    public function evaluate(?Passkey $passkey, Request $request, bool $failedAttempt = false): RiskScore
    {
        $score = 0;
        $signals = [];

        if ($passkey?->device !== null && $passkey->device->ip_address !== null && $passkey->device->ip_address !== $request->ip()) {
            $score += 30;
            $signals[] = 'new_ip_address';
        }

        if ($passkey?->device !== null && $passkey->device->user_agent !== null && $passkey->device->user_agent !== (string) $request->userAgent()) {
            $score += 20;
            $signals[] = 'new_user_agent';
        }

        $recentFailures = AuthenticationEvent::query()
            ->when($passkey !== null, fn ($query) => $query->where('user_id', $passkey->user_id))
            ->where('event', 'passkey.authentication.failed')
            ->where('ip_address', $request->ip())
            ->where('occurred_at', '>=', now()->subMinutes(15))
            ->count();

        if ($recentFailures >= 3) {
            $score += 40;
            $signals[] = 'multiple_recent_failures';
        }

        $recentSuccesses = AuthenticationEvent::query()
            ->when($passkey !== null, fn ($query) => $query->where('user_id', $passkey->user_id))
            ->where('event', 'passkey.authentication.completed')
            ->where('occurred_at', '>=', now()->subMinutes(10))
            ->count();

        if ($recentSuccesses >= 5) {
            $score += 20;
            $signals[] = 'abnormal_login_frequency';
        }

        if ($failedAttempt) {
            $score += 10;
            $signals[] = 'failed_attempt';
        }

        $level = match (true) {
            $score >= 60 => 'high',
            $score >= 30 => 'medium',
            default => 'low',
        };

        return new RiskScore(
            value: $score,
            level: $level,
            signals: $signals,
        );
    }
}
