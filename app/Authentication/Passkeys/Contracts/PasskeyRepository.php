<?php

namespace App\Authentication\Passkeys\Contracts;

use App\Authentication\Passkeys\Services\EloquentPasskeyRepository;
use App\Models\Device;
use App\Models\Passkey;
use App\Models\User;
use DateTimeInterface;
use Illuminate\Container\Attributes\Bind;
use Illuminate\Container\Attributes\Scoped;

#[Bind(EloquentPasskeyRepository::class)]
#[Scoped]
interface PasskeyRepository
{
    public function createDraft(User $user, Device $device, string $label, string $challenge, DateTimeInterface $challengeExpiresAt): Passkey;

    public function latestForUser(User $user): ?Passkey;
}
