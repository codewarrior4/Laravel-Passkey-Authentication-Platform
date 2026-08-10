<?php

namespace App\Authentication\Passkeys\ValueObjects;

use Carbon\CarbonImmutable;

final readonly class PasskeyChallenge
{
    public function __construct(
        public string $value,
        public CarbonImmutable $expiresAt,
    ) {}

    public function isExpired(): bool
    {
        return $this->expiresAt->isPast();
    }
}
