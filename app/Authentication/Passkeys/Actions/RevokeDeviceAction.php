<?php

namespace App\Authentication\Passkeys\Actions;

use App\Authentication\Passkeys\Contracts\AuthenticationAudit;
use App\Models\Device;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final readonly class RevokeDeviceAction
{
    public function __construct(private AuthenticationAudit $audit) {}

    public function handle(Device $device, User $user, string $ipAddress, string $userAgent): Device
    {
        return DB::transaction(function () use ($device, $user, $ipAddress, $userAgent): Device {
            $device->forceFill([
                'revoked_at' => now(),
            ])->save();

            foreach ($device->passkeys()->whereNull('revoked_at')->get() as $passkey) {
                $passkey->forceFill([
                    'revoked_at' => now(),
                    'status' => 'revoked',
                ])->save();

                $this->audit->record('passkey.revoked', $user->id, [
                    'device_id' => $device->id,
                    'ip_address' => $ipAddress,
                    'passkey_id' => $passkey->id,
                    'risk_level' => 'info',
                    'user_agent' => $userAgent,
                ]);
            }

            $this->audit->record('device.revoked', $user->id, [
                'device_id' => $device->id,
                'device_label' => $device->label,
                'ip_address' => $ipAddress,
                'risk_level' => 'info',
                'user_agent' => $userAgent,
            ]);

            return $device->refresh();
        });
    }
}
