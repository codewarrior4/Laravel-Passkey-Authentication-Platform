<?php

namespace App\Authentication\Passkeys\Actions;

use App\Authentication\Passkeys\Contracts\AuthenticationAudit;
use App\Authentication\Passkeys\Contracts\DeviceRepository;
use App\Authentication\Passkeys\DTO\LoginPasskeyPreviewData;
use App\Authentication\Passkeys\DTO\PasskeyPreviewResult;
use App\Authentication\Passkeys\DTO\StartPasskeyAuthenticationData;
use App\Authentication\Passkeys\Events\PasskeyAuthenticationRequested;
use App\Models\User;

final readonly class PreviewPasskeyAuthenticationAction
{
    public function __construct(
        private AuthenticationAudit $audit,
        private DeviceRepository $deviceRepository,
        private StartPasskeyAuthenticationAction $startPasskeyAuthenticationAction,
    ) {}

    public function handle(LoginPasskeyPreviewData $data): PasskeyPreviewResult
    {
        $user = User::query()->firstOrCreate(
            ['email' => $data->workEmail],
            ['name' => str($data->workEmail)->before('@')->headline(), 'password' => 'password']
        );

        $device = $this->deviceRepository->findByLabel($user, $data->deviceChoice);

        if ($device !== null) {
            $this->deviceRepository->touchLastUsed($device);
        }

        $authenticationData = new StartPasskeyAuthenticationData(
            email: $data->workEmail,
            ipAddress: $data->ipAddress,
            userAgent: $data->userAgent,
        );

        $challenge = $this->startPasskeyAuthenticationAction->handle($authenticationData);

        event(new PasskeyAuthenticationRequested(
            data: $authenticationData,
            challenge: $challenge,
        ));

        $this->audit->record('passkey.preview.login.succeeded', $user->id, [
            'device_id' => $device?->id,
            'device_label' => $device?->label,
            'ip_address' => $data->ipAddress,
            'user_agent' => $data->userAgent,
        ]);

        return new PasskeyPreviewResult(
            userId: $user->id,
            deviceId: $device?->id,
            passkeyId: null,
            message: "Preview sign-in succeeded for {$data->workEmail} using {$data->deviceChoice}.",
            expiresAt: $challenge->expiresAt,
        );
    }
}
