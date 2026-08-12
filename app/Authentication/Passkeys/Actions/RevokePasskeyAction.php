<?php

namespace App\Authentication\Passkeys\Actions;

use App\Authentication\Passkeys\Contracts\AuthenticationAudit;
use App\Models\Passkey;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final readonly class RevokePasskeyAction
{
    public function __construct(private AuthenticationAudit $audit) {}

    public function handle(Passkey $passkey, User $user, string $ipAddress, string $userAgent): Passkey
    {
        return DB::transaction(function () use ($passkey, $user, $ipAddress, $userAgent): Passkey {
            $passkey->forceFill([
                'revoked_at' => now(),
                'status' => 'revoked',
            ])->save();

            $this->audit->record('passkey.revoked', $user->id, [
                'device_id' => $passkey->device_id,
                'ip_address' => $ipAddress,
                'passkey_id' => $passkey->id,
                'risk_level' => 'info',
                'user_agent' => $userAgent,
            ]);

            return $passkey->refresh();
        });
    }
}
