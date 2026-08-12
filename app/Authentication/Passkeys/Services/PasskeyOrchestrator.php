<?php

namespace App\Authentication\Passkeys\Services;

use App\Authentication\Passkeys\Contracts\PasskeyService;
use App\Authentication\Passkeys\DTO\StartPasskeyAuthenticationData;
use App\Authentication\Passkeys\DTO\StartPasskeyRegistrationData;
use App\Authentication\Passkeys\ValueObjects\PasskeyChallenge;
use App\Authentication\Passkeys\ValueObjects\RelyingParty;
use Carbon\CarbonImmutable;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

final class PasskeyOrchestrator implements PasskeyService
{
    public function beginAuthentication(StartPasskeyAuthenticationData $data): PasskeyChallenge
    {
        return $this->warmupChallenge();
    }

    public function beginRegistration(StartPasskeyRegistrationData $data): PasskeyChallenge
    {
        return $this->warmupChallenge();
    }

    public function relyingParty(): RelyingParty
    {
        /** @var array{id: string, ids: array<int, string>, name: string, origins: array<int, string>} $configuration */
        $configuration = config('passkeys.relying_party');
        $requestHost = request()->getHost();
        $allowedIds = Arr::wrap(Arr::get($configuration, 'ids', []));
        $resolvedId = in_array($requestHost, $allowedIds, true)
            ? $requestHost
            : Arr::get($configuration, 'id', 'localhost');

        return new RelyingParty(
            id: $resolvedId,
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
