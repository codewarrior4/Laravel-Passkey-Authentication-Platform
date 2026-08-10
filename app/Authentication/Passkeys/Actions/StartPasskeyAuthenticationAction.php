<?php

namespace App\Authentication\Passkeys\Actions;

use App\Authentication\Passkeys\Contracts\PasskeyService;
use App\Authentication\Passkeys\DTO\StartPasskeyAuthenticationData;
use App\Authentication\Passkeys\ValueObjects\PasskeyChallenge;

final readonly class StartPasskeyAuthenticationAction
{
    public function __construct(private PasskeyService $passkeyService) {}

    public function handle(StartPasskeyAuthenticationData $data): PasskeyChallenge
    {
        return $this->passkeyService->beginAuthentication($data);
    }
}
