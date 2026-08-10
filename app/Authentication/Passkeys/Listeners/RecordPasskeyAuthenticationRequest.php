<?php

namespace App\Authentication\Passkeys\Listeners;

use App\Authentication\Passkeys\Contracts\AuthenticationAudit;
use App\Authentication\Passkeys\Events\PasskeyAuthenticationRequested;

final readonly class RecordPasskeyAuthenticationRequest
{
    public function __construct(private AuthenticationAudit $audit) {}

    public function handle(PasskeyAuthenticationRequested $event): void
    {
        $this->audit->record('passkey.authentication.requested', null, [
            'challenge_expires_at' => $event->challenge->expiresAt->toIso8601String(),
            'email' => $event->data->email,
            'ip_address' => $event->data->ipAddress,
            'user_agent' => $event->data->userAgent,
        ]);
    }
}
