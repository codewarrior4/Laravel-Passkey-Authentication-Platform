<?php

namespace App\Authentication\Passkeys\DTO;

final readonly class RegistrationCeremonyOptions
{
    /**
     * @param  array<string, mixed>  $options
     */
    public function __construct(
        public int $passkeyId,
        public array $options,
    ) {}
}
