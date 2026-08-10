<?php

namespace App\Authentication\Passkeys\Contracts;

use App\Authentication\Passkeys\DTO\StartPasskeyAuthenticationData;
use App\Authentication\Passkeys\DTO\StartPasskeyRegistrationData;
use App\Authentication\Passkeys\Services\PasskeyOrchestrator;
use App\Authentication\Passkeys\ValueObjects\PasskeyChallenge;
use App\Authentication\Passkeys\ValueObjects\RelyingParty;
use Illuminate\Container\Attributes\Bind;
use Illuminate\Container\Attributes\Scoped;

#[Bind(PasskeyOrchestrator::class)]
#[Scoped]
interface PasskeyService
{
    public function beginAuthentication(StartPasskeyAuthenticationData $data): PasskeyChallenge;

    public function beginRegistration(StartPasskeyRegistrationData $data): PasskeyChallenge;

    public function relyingParty(): RelyingParty;
}
