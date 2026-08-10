<?php

namespace App\Authentication\Passkeys\Events;

use App\Authentication\Passkeys\DTO\StartPasskeyAuthenticationData;
use App\Authentication\Passkeys\ValueObjects\PasskeyChallenge;

final readonly class PasskeyAuthenticationRequested
{
    public function __construct(
        public StartPasskeyAuthenticationData $data,
        public PasskeyChallenge $challenge,
    ) {}
}
