<?php

namespace App\Authentication\Passkeys\Actions;

use App\Authentication\Passkeys\Contracts\AuthenticationAudit;
use App\Authentication\Passkeys\Contracts\PasskeyService;
use App\Authentication\Passkeys\DTO\FinishPasskeyRegistrationData;
use App\Authentication\Passkeys\Exceptions\PasskeyException;
use App\Authentication\Passkeys\Support\Base64Url;
use App\Models\Passkey;
use Illuminate\Support\Facades\DB;

final readonly class FinishPasskeyRegistrationAction
{
    public function __construct(
        private AuthenticationAudit $audit,
        private PasskeyService $passkeyService,
    ) {}

    public function handle(FinishPasskeyRegistrationData $data): Passkey
    {
        return DB::transaction(function () use ($data): Passkey {
            /** @var Passkey $passkey */
            $passkey = Passkey::query()
                ->with(['device', 'user'])
                ->findOrFail($data->passkeyId);

            if ($passkey->challenge_expires_at === null || $passkey->challenge_expires_at->isPast()) {
                throw new PasskeyException('The registration challenge has expired.');
            }

            $clientData = json_decode(Base64Url::decode($data->clientDataJson), true, flags: JSON_THROW_ON_ERROR);

            if (($clientData['type'] ?? null) !== 'webauthn.create') {
                throw new PasskeyException('Invalid WebAuthn ceremony type.');
            }

            if (($clientData['challenge'] ?? null) !== Base64Url::encode($passkey->current_challenge ?? '')) {
                throw new PasskeyException('Challenge mismatch during passkey registration.');
            }

            $relyingParty = $this->passkeyService->relyingParty();

            if (($clientData['origin'] ?? null) !== $data->origin || ! $relyingParty->allowsOrigin($data->origin)) {
                throw new PasskeyException('Origin mismatch during passkey registration.');
            }

            if (! $passkey->user->passkeys()->where('credential_id_hash', hash('sha256', $data->credentialId))->doesntExist()) {
                throw new PasskeyException('This credential is already registered.');
            }

            $authenticatorData = Base64Url::decode($data->authenticatorData);

            if (strlen($authenticatorData) < 37) {
                throw new PasskeyException('Authenticator data is incomplete.');
            }

            $expectedRpIdHash = hash('sha256', $relyingParty->id, true);
            $rpIdHash = substr($authenticatorData, 0, 32);
            $flags = ord(substr($authenticatorData, 32, 1));
            $signCount = unpack('Ncount', substr($authenticatorData, 33, 4))['count'];

            if ($rpIdHash !== $expectedRpIdHash) {
                throw new PasskeyException('Relying party hash mismatch.');
            }

            if (($flags & 0x01) !== 0x01) {
                throw new PasskeyException('User presence flag was not set.');
            }

            $passkey->forceFill([
                'challenge_expires_at' => null,
                'counter' => $signCount,
                'credential_id' => $data->credentialId,
                'credential_id_hash' => hash('sha256', $data->credentialId),
                'current_challenge' => null,
                'last_used_at' => now(),
                'public_key' => $data->publicKey,
                'registered_at' => now(),
                'status' => 'active',
                'transports' => $data->transports,
            ])->save();

            $passkey->device?->forceFill([
                'last_used_at' => now(),
            ])->save();

            $this->audit->record('passkey.registration.completed', $passkey->user_id, [
                'device_id' => $passkey->device_id,
                'ip_address' => $passkey->device?->ip_address,
                'passkey_id' => $passkey->id,
                'public_key_algorithm' => $data->publicKeyAlgorithm,
                'risk_level' => 'low',
                'transports' => $data->transports,
                'user_agent' => $passkey->device?->user_agent,
            ]);

            return $passkey->refresh();
        });
    }
}
