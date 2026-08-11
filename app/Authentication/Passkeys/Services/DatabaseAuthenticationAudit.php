<?php

namespace App\Authentication\Passkeys\Services;

use App\Authentication\Passkeys\Contracts\AuthenticationAudit;
use App\Models\AuthenticationEvent;
use Illuminate\Support\Arr;

final class DatabaseAuthenticationAudit implements AuthenticationAudit
{
    public function record(string $event, ?int $userId = null, array $context = []): void
    {
        AuthenticationEvent::query()->create([
            'device_id' => Arr::get($context, 'device_id'),
            'event' => $event,
            'ip_address' => Arr::get($context, 'ip_address'),
            'metadata' => Arr::except($context, ['device_id', 'ip_address', 'passkey_id', 'risk_level', 'user_agent']),
            'occurred_at' => now(),
            'passkey_id' => Arr::get($context, 'passkey_id'),
            'risk_level' => Arr::get($context, 'risk_level', 'info'),
            'user_agent' => Arr::get($context, 'user_agent'),
            'user_id' => $userId,
        ]);
    }
}
