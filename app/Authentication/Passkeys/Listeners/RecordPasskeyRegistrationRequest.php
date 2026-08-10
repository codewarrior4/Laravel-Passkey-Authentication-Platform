<?php

namespace App\Authentication\Passkeys\Listeners;

use App\Authentication\Passkeys\Contracts\AuthenticationAudit;
use App\Authentication\Passkeys\Events\PasskeyRegistrationRequested;

final readonly class RecordPasskeyRegistrationRequest
{
    public function __construct(private AuthenticationAudit $audit) {}

    public function handle(PasskeyRegistrationRequested $event): void
    {
        $this->audit->record('passkey.registration.requested', $event->data->userId, [
            'challenge_expires_at' => $event->challenge->expiresAt->toIso8601String(),
            'display_name' => $event->data->displayName,
            'ip_address' => $event->data->ipAddress,
            'user_agent' => $event->data->userAgent,
            'user_handle' => $event->data->userHandle,
        ]);
    }
}
