<?php

namespace App\Authentication\Passkeys\Services;

use App\Authentication\Passkeys\Contracts\PasskeyService;
use App\Authentication\Passkeys\DTO\StartPasskeyAuthenticationData;
use App\Authentication\Passkeys\DTO\StartPasskeyRegistrationData;
use App\Authentication\Passkeys\Exceptions\PasskeyFeatureNotReadyException;
use App\Authentication\Passkeys\ValueObjects\PasskeyChallenge;
use App\Authentication\Passkeys\ValueObjects\RelyingParty;
use Carbon\CarbonImmutable;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

final class PasskeyOrchestrator implements PasskeyService
{
    public function beginAuthentication(StartPasskeyAuthenticationData $data): PasskeyChallenge
    {
        throw PasskeyFeatureNotReadyException::forFlow('authentication');
    }

    public function beginRegistration(StartPasskeyRegistrationData $data): PasskeyChallenge
    {
        throw PasskeyFeatureNotReadyException::forFlow('registration');
    }

    public function relyingParty(): RelyingParty
    {
        /** @var array{id: string, name: string, origins: array<int, string>} $configuration */
        $configuration = config('passkeys.relying_party');

        return new RelyingParty(
            id: Arr::get($configuration, 'id', 'localhost'),
            name: Arr::get($configuration, 'name', config('app.name', 'Laravel')),
            origins: Arr::wrap(Arr::get($configuration, 'origins', [])),
        );
    }

    public function warmupChallenge(int $minutes = 5): PasskeyChallenge
    {
        return new PasskeyChallenge(
            value: Str::random(64),
            expiresAt: CarbonImmutable::now()->addMinutes($minutes),
        );
    }
}
