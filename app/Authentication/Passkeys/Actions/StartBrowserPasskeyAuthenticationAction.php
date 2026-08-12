<?php

namespace App\Authentication\Passkeys\Actions;

use App\Authentication\Passkeys\Contracts\PasskeyService;
use App\Authentication\Passkeys\DTO\AuthenticationCeremonyOptions;
use App\Authentication\Passkeys\DTO\StartPasskeyAuthenticationData;
use App\Authentication\Passkeys\Support\Base64Url;

final readonly class StartBrowserPasskeyAuthenticationAction
{
    public function __construct(
        private PasskeyService $passkeyService,
        private StartPasskeyAuthenticationAction $startPasskeyAuthenticationAction,
    ) {}

    public function handle(StartPasskeyAuthenticationData $data): AuthenticationCeremonyOptions
    {
        $challenge = $this->startPasskeyAuthenticationAction->handle($data);

        $relyingParty = $this->passkeyService->relyingParty();

        return new AuthenticationCeremonyOptions(
            passkeyId: null,
            userId: null,
            options: [
                'allowCredentials' => [],
                'challenge' => Base64Url::encode($challenge->value),
                'hints' => ['client-device'],
                'rpId' => $relyingParty->id,
                'timeout' => 300000,
                'userVerification' => 'required',
            ],
        );
    }
}
