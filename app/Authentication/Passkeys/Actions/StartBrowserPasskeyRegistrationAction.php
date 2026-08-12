<?php

namespace App\Authentication\Passkeys\Actions;

use App\Authentication\Passkeys\Contracts\PasskeyService;
use App\Authentication\Passkeys\DTO\RegisterPasskeyPreviewData;
use App\Authentication\Passkeys\DTO\RegistrationCeremonyOptions;
use App\Authentication\Passkeys\Support\Base64Url;
use App\Models\User;

final readonly class StartBrowserPasskeyRegistrationAction
{
    public function __construct(
        private PasskeyService $passkeyService,
        private PreviewPasskeyRegistrationAction $previewPasskeyRegistrationAction,
    ) {}

    public function handle(RegisterPasskeyPreviewData $data): RegistrationCeremonyOptions
    {
        $result = $this->previewPasskeyRegistrationAction->handle($data);

        /** @var User $user */
        $user = User::query()->with('passkeys')->findOrFail($result->userId);
        $passkey = $user->passkeys()->findOrFail($result->passkeyId);
        $relyingParty = $this->passkeyService->relyingParty();

        return new RegistrationCeremonyOptions(
            passkeyId: $passkey->id,
            options: [
                'rp' => [
                    'id' => $relyingParty->id,
                    'name' => $relyingParty->name,
                ],
                'user' => [
                    'displayName' => $user->name,
                    'id' => Base64Url::encode($passkey->user_handle),
                    'name' => $user->email,
                ],
                'challenge' => Base64Url::encode($passkey->current_challenge ?? ''),
                'pubKeyCredParams' => [
                    ['alg' => -7, 'type' => 'public-key'],
                    ['alg' => -257, 'type' => 'public-key'],
                ],
                'timeout' => 300000,
                'attestation' => 'none',
                'hints' => ['client-device'],
                'authenticatorSelection' => [
                    'authenticatorAttachment' => 'platform',
                    'requireResidentKey' => true,
                    'residentKey' => 'required',
                    'userVerification' => 'required',
                ],
                'excludeCredentials' => $user->passkeys
                    ->filter(fn ($existingPasskey) => $existingPasskey->credential_id !== null)
                    ->map(fn ($existingPasskey) => [
                        'id' => Base64Url::encode($existingPasskey->credential_id),
                        'transports' => $existingPasskey->transports ?? [],
                        'type' => 'public-key',
                    ])
                    ->values()
                    ->all(),
            ],
        );
    }
}
