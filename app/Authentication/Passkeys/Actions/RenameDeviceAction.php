<?php

namespace App\Authentication\Passkeys\Actions;

use App\Authentication\Passkeys\Contracts\AuthenticationAudit;
use App\Models\Device;
use App\Models\User;

final readonly class RenameDeviceAction
{
    public function __construct(private AuthenticationAudit $audit) {}

    public function handle(Device $device, string $label, User $user, string $ipAddress, string $userAgent): Device
    {
        $device->forceFill([
            'label' => $label,
        ])->save();

        $this->audit->record('device.renamed', $user->id, [
            'device_id' => $device->id,
            'device_label' => $label,
            'ip_address' => $ipAddress,
            'risk_level' => 'info',
            'user_agent' => $userAgent,
        ]);

        return $device->refresh();
    }
}
