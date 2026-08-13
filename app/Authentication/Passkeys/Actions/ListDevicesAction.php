<?php

namespace App\Authentication\Passkeys\Actions;

use App\Models\Device;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

final class ListDevicesAction
{
    /**
     * @return Collection<int, Device>
     */
    public function handle(User $user): Collection
    {
        return $user->devices()
            ->with('passkeys')
            ->latest('created_at')
            ->get();
    }
}
