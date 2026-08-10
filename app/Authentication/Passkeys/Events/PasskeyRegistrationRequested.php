<?php

namespace App\Authentication\Passkeys\Events;

use App\Authentication\Passkeys\DTO\StartPasskeyRegistrationData;
use App\Authentication\Passkeys\ValueObjects\PasskeyChallenge;

final readonly class PasskeyRegistrationRequested
{
    public function __construct(
        public StartPasskeyRegistrationData $data,
        public PasskeyChallenge $challenge,
    ) {}
}
