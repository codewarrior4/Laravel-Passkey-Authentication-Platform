<?php

namespace App\Authentication\Passkeys\ValueObjects;

final readonly class RelyingParty
{
    /**
     * @param  array<int, string>  $origins
     */
    public function __construct(
        public string $id,
        public string $name,
        public array $origins,
    ) {}

    public function allowsOrigin(string $origin): bool
    {
        return in_array($origin, $this->origins, true);
    }
}
