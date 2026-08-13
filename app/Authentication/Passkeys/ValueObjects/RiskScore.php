<?php

namespace App\Authentication\Passkeys\ValueObjects;

final readonly class RiskScore
{
    /**
     * @param  array<int, string>  $signals
     */
    public function __construct(
        public int $value,
        public string $level,
        public array $signals,
    ) {}

    public function isElevated(): bool
    {
        return $this->level !== 'low';
    }

    public function shouldBlock(): bool
    {
        return $this->level === 'high';
    }
}
