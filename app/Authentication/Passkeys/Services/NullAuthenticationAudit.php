<?php

namespace App\Authentication\Passkeys\Services;

use App\Authentication\Passkeys\Contracts\AuthenticationAudit;

final class NullAuthenticationAudit implements AuthenticationAudit
{
    public function record(string $event, ?int $userId = null, array $context = []): void {}
}
