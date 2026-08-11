<?php

namespace App\Authentication\Passkeys\Contracts;

use App\Authentication\Passkeys\Services\EloquentDeviceRepository;
use App\Models\Device;
use App\Models\User;
use Illuminate\Container\Attributes\Bind;
use Illuminate\Container\Attributes\Scoped;

#[Bind(EloquentDeviceRepository::class)]
#[Scoped]
interface DeviceRepository
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function createForUser(User $user, array $attributes): Device;

    public function findByLabel(User $user, string $label): ?Device;

    public function touchLastUsed(Device $device): Device;
}
