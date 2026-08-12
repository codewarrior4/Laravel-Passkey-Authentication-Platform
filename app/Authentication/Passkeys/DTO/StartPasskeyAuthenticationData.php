<?php

namespace App\Authentication\Passkeys\DTO;

final readonly class StartPasskeyAuthenticationData
{
    public function __construct(
        public ?string $email,
        public string $ipAddress,
        public string $userAgent,
    ) {}
}
