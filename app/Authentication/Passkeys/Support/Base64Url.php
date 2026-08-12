<?php

namespace App\Authentication\Passkeys\Support;

use App\Authentication\Passkeys\Exceptions\PasskeyException;

final class Base64Url
{
    public static function decode(string $value): string
    {
        $remainder = strlen($value) % 4;
        $padded = strtr($value, '-_', '+/');

        if ($remainder > 0) {
            $padded .= str_repeat('=', 4 - $remainder);
        }

        $decoded = base64_decode($padded, true);

        if ($decoded === false) {
            throw new PasskeyException('Invalid base64url value.');
        }

        return $decoded;
    }

    public static function encode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
