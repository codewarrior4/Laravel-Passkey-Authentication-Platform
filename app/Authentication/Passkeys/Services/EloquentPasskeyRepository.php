<?php

namespace App\Authentication\Passkeys\Services;

use App\Authentication\Passkeys\Contracts\PasskeyRepository;
use App\Models\Device;
use App\Models\Passkey;
use App\Models\User;
use DateTimeInterface;

final class EloquentPasskeyRepository implements PasskeyRepository
{
    public function createDraft(User $user, Device $device, string $label, string $challenge, DateTimeInterface $challengeExpiresAt): Passkey
    {
        /** @var Passkey $passkey */
        $passkey = $user->passkeys()->create([
            'challenge_expires_at' => $challengeExpiresAt,
            'current_challenge' => $challenge,
            'device_id' => $device->id,
            'label' => $label,
            'status' => 'pending',
            'user_handle' => (string) $user->getKey(),
        ]);

        return $passkey;
    }

    public function latestForUser(User $user): ?Passkey
    {
        return $user->passkeys()->latest('id')->first();
    }
}
