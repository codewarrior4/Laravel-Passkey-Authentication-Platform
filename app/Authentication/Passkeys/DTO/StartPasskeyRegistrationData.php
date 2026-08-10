<?php

namespace App\Authentication\Passkeys\DTO;

final readonly class StartPasskeyRegistrationData
{
    public function __construct(
        public int $userId,
        public string $userHandle,
        public string $displayName,
        public string $ipAddress,
        public string $userAgent,
    ) {}
}
