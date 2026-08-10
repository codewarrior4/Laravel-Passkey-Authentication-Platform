<?php

namespace App\Authentication\Passkeys\Policies;

use App\Models\User;

final class PasskeyManagementPolicy
{
    public function manageOwnPasskeys(User $user, User $owner): bool
    {
        return $user->is($owner);
    }

    public function viewSecurityCenter(User $user): bool
    {
        return $user->exists;
    }
}
