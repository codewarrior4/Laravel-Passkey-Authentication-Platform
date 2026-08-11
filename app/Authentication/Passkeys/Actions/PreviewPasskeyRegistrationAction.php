<?php

namespace App\Authentication\Passkeys\Actions;

use App\Authentication\Passkeys\Contracts\DeviceRepository;
use App\Authentication\Passkeys\Contracts\PasskeyRepository;
use App\Authentication\Passkeys\DTO\PasskeyPreviewResult;
use App\Authentication\Passkeys\DTO\RegisterPasskeyPreviewData;
use App\Authentication\Passkeys\DTO\StartPasskeyRegistrationData;
use App\Authentication\Passkeys\Events\PasskeyRegistrationRequested;
use App\Models\User;
use Illuminate\Support\Str;

final readonly class PreviewPasskeyRegistrationAction
{
    public function __construct(
        private DeviceRepository $deviceRepository,
        private PasskeyRepository $passkeyRepository,
        private StartPasskeyRegistrationAction $startPasskeyRegistrationAction,
    ) {}

    public function handle(RegisterPasskeyPreviewData $data): PasskeyPreviewResult
    {
        $user = User::query()->updateOrCreate(
            ['email' => $data->workEmail],
            ['name' => $data->fullName, 'password' => 'password']
        );

        $device = $this->deviceRepository->createForUser($user, [
            'browser' => $this->detectBrowser($data->userAgent),
            'ip_address' => $data->ipAddress,
            'label' => $data->deviceName,
            'platform' => $this->detectPlatform($data->userAgent),
            'registered_at' => now(),
            'type' => 'platform',
            'user_agent' => $data->userAgent,
        ]);

        $registrationData = new StartPasskeyRegistrationData(
            userId: $user->id,
            userHandle: (string) $user->getKey(),
            displayName: $data->fullName,
            ipAddress: $data->ipAddress,
            userAgent: $data->userAgent,
        );

        $challenge = $this->startPasskeyRegistrationAction->handle($registrationData);

        $passkey = $this->passkeyRepository->createDraft(
            user: $user,
            device: $device,
            label: $data->deviceName,
            challenge: $challenge->value,
            challengeExpiresAt: $challenge->expiresAt,
        );

        event(new PasskeyRegistrationRequested(
            data: $registrationData,
            challenge: $challenge,
        ));

        return new PasskeyPreviewResult(
            userId: $user->id,
            deviceId: $device->id,
            passkeyId: $passkey->id,
            message: "Registration draft created for {$data->fullName}. The passkey now has a stored device, draft record, challenge window, and audit trail.",
            expiresAt: $challenge->expiresAt,
        );
    }

    private function detectBrowser(string $userAgent): string
    {
        if (Str::contains($userAgent, 'Firefox')) {
            return 'Firefox';
        }

        if (Str::contains($userAgent, 'Chrome')) {
            return 'Chrome';
        }

        if (Str::contains($userAgent, 'Safari')) {
            return 'Safari';
        }

        return 'Browser';
    }

    private function detectPlatform(string $userAgent): string
    {
        if (Str::contains($userAgent, ['iPhone', 'iPad', 'iOS'])) {
            return 'iOS';
        }

        if (Str::contains($userAgent, 'Android')) {
            return 'Android';
        }

        if (Str::contains($userAgent, 'Windows')) {
            return 'Windows';
        }

        if (Str::contains($userAgent, ['Macintosh', 'Mac OS'])) {
            return 'macOS';
        }

        return 'Unknown';
    }
}
