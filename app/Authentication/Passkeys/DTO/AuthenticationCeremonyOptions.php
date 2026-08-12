<?php

namespace App\Authentication\Passkeys\DTO;

final readonly class AuthenticationCeremonyOptions
{
    /**
     * @param  array<string, mixed>  $options
     */
    public function __construct(
        public ?int $passkeyId,
        public ?int $userId,
        public array $options,
    ) {}
}
