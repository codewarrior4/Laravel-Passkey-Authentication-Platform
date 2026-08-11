<?php

namespace App\Authentication\Passkeys\DTO;

final readonly class LoginPasskeyPreviewData
{
    public function __construct(
        public string $workEmail,
        public string $deviceChoice,
        public string $ipAddress,
        public string $userAgent,
    ) {}
}
