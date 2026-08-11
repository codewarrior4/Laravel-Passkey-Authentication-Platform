<?php

namespace App\Authentication\Passkeys\DTO;

final readonly class RegisterPasskeyPreviewData
{
    public function __construct(
        public string $fullName,
        public string $workEmail,
        public string $deviceName,
        public string $ipAddress,
        public string $userAgent,
    ) {}
}
