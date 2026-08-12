<?php

namespace App\Authentication\Passkeys\Actions;

use App\Authentication\Passkeys\DTO\RegisterPasskeyPreviewData;
use App\Authentication\Passkeys\DTO\RegistrationCeremonyOptions;
use App\Authentication\Passkeys\Support\Base64Url;
use App\Models\User;

final readonly class StartBrowserPasskeyRegistrationAction
{
    public function __construct(private PreviewPasskeyRegistrationAction $previewPasskeyRegistrationAction) {}

    public function handle(RegisterPasskeyPreviewData $data): RegistrationCeremonyOptions
    {
        $result = $this->previewPasskeyRegistrationAction->handle($data);

        /** @var User $user */
        $user = User::query()->with('passkeys')->findOrFail($result->userId);
        $passkey = $user->passkeys()->findOrFail($result->passkeyId);

        return new RegistrationCeremonyOptions(
            passkeyId: $passkey->id,
            options: [
                'rp' => [
                    'id' => config('passkeys.relying_party.id'),
                    'name' => config('passkeys.relying_party.name'),
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
                'authenticatorSelection' => [
                    'residentKey' => 'preferred',
                    'userVerification' => 'preferred',
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
