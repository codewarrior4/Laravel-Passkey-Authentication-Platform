<?php

namespace App\Authentication\Passkeys\Exceptions;

final class PasskeyFeatureNotReadyException extends PasskeyException
{
    public static function forFlow(string $flow): self
    {
        return new self("The {$flow} passkey flow has not been implemented yet.");
    }
}
