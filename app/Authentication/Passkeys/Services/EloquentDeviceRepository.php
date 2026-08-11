<?php

namespace App\Authentication\Passkeys\Services;

use App\Authentication\Passkeys\Contracts\DeviceRepository;
use App\Models\Device;
use App\Models\User;

final class EloquentDeviceRepository implements DeviceRepository
{
    public function createForUser(User $user, array $attributes): Device
    {
        /** @var Device $device */
        $device = $user->devices()->create($attributes);

        return $device;
    }

    public function findByLabel(User $user, string $label): ?Device
    {
        return $user->devices()
            ->where('label', $label)
            ->latest('id')
            ->first();
    }

    public function touchLastUsed(Device $device): Device
    {
        $device->forceFill(['last_used_at' => now()])->save();

        return $device->refresh();
    }
}
