<?php

namespace App\Authentication\Passkeys\Actions;

use App\Authentication\Passkeys\Contracts\AuthenticationAudit;
use App\Authentication\Passkeys\Contracts\PasskeyService;
use App\Authentication\Passkeys\DTO\FinishPasskeyAuthenticationData;
use App\Authentication\Passkeys\Exceptions\PasskeyException;
use App\Authentication\Passkeys\Services\SecurityRiskEvaluator;
use App\Authentication\Passkeys\Support\Base64Url;
use App\Models\Passkey;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Auth\AuthManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final readonly class FinishPasskeyAuthenticationAction
{
    public function __construct(
        private AuthenticationAudit $audit,
        private AuthManager $auth,
        private PasskeyService $passkeyService,
        private SecurityRiskEvaluator $securityRiskEvaluator,
    ) {}

    public function handle(FinishPasskeyAuthenticationData $data, Request $request): User
    {
        return DB::transaction(function () use ($data, $request): User {
            $sessionChallenge = $request->session()->get('passkey_authentication_challenge');
            $challengeExpiresAt = $request->session()->get('passkey_authentication_expires_at');

            if (! is_string($sessionChallenge) || ! is_string($challengeExpiresAt)) {
                throw new PasskeyException('The authentication challenge is missing. Start the sign-in flow again.');
            }

            if (CarbonImmutable::parse($challengeExpiresAt)->isPast()) {
                throw new PasskeyException('The authentication challenge has expired.');
            }

            /** @var Passkey $passkey */
            $passkey = Passkey::query()
                ->with(['device', 'user'])
                ->where('credential_id_hash', hash('sha256', $data->credentialId))
                ->where('status', 'active')
                ->whereNull('revoked_at')
                ->first();

            if ($passkey === null) {
                throw new PasskeyException('This passkey is no longer recognized. Remove it from your device and register a new one.');
            }

            if ($passkey->device?->revoked_at !== null) {
                throw new PasskeyException('This device has been revoked.');
            }

            $clientDataJson = Base64Url::decode($data->clientDataJson);
            $clientData = json_decode($clientDataJson, true, flags: JSON_THROW_ON_ERROR);

            if (($clientData['type'] ?? null) !== 'webauthn.get') {
                throw new PasskeyException('Invalid WebAuthn authentication type.');
            }

            if (($clientData['challenge'] ?? null) !== $sessionChallenge) {
                throw new PasskeyException('Challenge mismatch during passkey authentication.');
            }

            $relyingParty = $this->passkeyService->relyingParty();

            if (($clientData['origin'] ?? null) !== $data->origin || ! $relyingParty->allowsOrigin($data->origin)) {
                throw new PasskeyException('Origin mismatch during passkey authentication.');
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

            if (($signCount !== 0 || $passkey->counter !== 0) && $signCount <= $passkey->counter) {
                throw new PasskeyException('Passkey counter replay detected.');
            }

            $signaturePayload = $authenticatorData.hash('sha256', $clientDataJson, true);
            $publicKey = $this->publicKeyPem(Base64Url::decode($passkey->public_key ?? ''));
            $signature = Base64Url::decode($data->signature);
            $verified = openssl_verify($signaturePayload, $signature, $publicKey, OPENSSL_ALGO_SHA256);

            if ($verified !== 1) {
                throw new PasskeyException('Passkey signature verification failed.');
            }

            $riskScore = $this->securityRiskEvaluator->evaluate($passkey, $request);

            if ($riskScore->shouldBlock()) {
                $this->audit->record('suspicious.activity.detected', $passkey->user_id, [
                    'device_id' => $passkey->device_id,
                    'ip_address' => $request->ip(),
                    'passkey_id' => $passkey->id,
                    'risk_level' => $riskScore->level,
                    'signals' => $riskScore->signals,
                    'user_agent' => (string) $request->userAgent(),
                ]);

                throw new PasskeyException('This sign-in was blocked for review.');
            }

            $passkey->forceFill([
                'counter' => $signCount,
                'last_used_at' => now(),
            ])->save();

            $passkey->device?->forceFill([
                'ip_address' => $request->ip(),
                'last_used_at' => now(),
                'user_agent' => (string) $request->userAgent(),
            ])->save();

            $this->auth->guard()->login($passkey->user);
            $request->session()->regenerate();

            $this->audit->record('passkey.authentication.completed', $passkey->user_id, [
                'device_id' => $passkey->device_id,
                'ip_address' => $request->ip(),
                'passkey_id' => $passkey->id,
                'risk_level' => $riskScore->level,
                'user_agent' => (string) $request->userAgent(),
            ]);

            if ($riskScore->isElevated()) {
                $this->audit->record('suspicious.activity.detected', $passkey->user_id, [
                    'device_id' => $passkey->device_id,
                    'ip_address' => $request->ip(),
                    'passkey_id' => $passkey->id,
                    'risk_level' => $riskScore->level,
                    'signals' => $riskScore->signals,
                    'user_agent' => (string) $request->userAgent(),
                ]);
            }

            $request->session()->forget([
                'passkey_authentication_challenge',
                'passkey_authentication_expires_at',
            ]);

            return $passkey->user->refresh();
        });
    }

    private function publicKeyPem(string $der): string
    {
        return "-----BEGIN PUBLIC KEY-----\n"
            .chunk_split(base64_encode($der), 64, "\n")
            ."-----END PUBLIC KEY-----\n";
    }
}
