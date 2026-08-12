<?php

namespace App\Authentication\Passkeys\DTO;

final readonly class FinishPasskeyAuthenticationData
{
    public function __construct(
        public string $credentialId,
        public string $clientDataJson,
        public string $authenticatorData,
        public string $signature,
        public string $origin,
    ) {}
}
