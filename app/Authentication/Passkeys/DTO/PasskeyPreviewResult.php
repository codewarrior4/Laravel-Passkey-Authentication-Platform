<?php

namespace App\Authentication\Passkeys\DTO;

use Carbon\CarbonImmutable;

final readonly class PasskeyPreviewResult
{
    public function __construct(
        public int $userId,
        public ?int $deviceId,
        public ?int $passkeyId,
        public string $message,
        public CarbonImmutable $expiresAt,
    ) {}
}
