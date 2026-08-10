<?php

namespace App\Authentication\Passkeys\Actions;

use App\Authentication\Passkeys\Contracts\PasskeyService;
use App\Authentication\Passkeys\DTO\StartPasskeyRegistrationData;
use App\Authentication\Passkeys\ValueObjects\PasskeyChallenge;

final readonly class StartPasskeyRegistrationAction
{
    public function __construct(private PasskeyService $passkeyService) {}

    public function handle(StartPasskeyRegistrationData $data): PasskeyChallenge
    {
        return $this->passkeyService->beginRegistration($data);
    }
}
