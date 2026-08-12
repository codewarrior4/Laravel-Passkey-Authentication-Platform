<?php

namespace App\Authentication\Passkeys\DTO;

final readonly class FinishPasskeyRegistrationData
{
    /**
     * @param  array<int, string>  $transports
     */
    public function __construct(
        public int $passkeyId,
        public string $credentialId,
        public string $clientDataJson,
        public string $authenticatorData,
        public string $publicKey,
        public int $publicKeyAlgorithm,
        public array $transports,
        public string $origin,
    ) {}
}
