<?php

namespace App\Authentication\Passkeys\Contracts;

use App\Authentication\Passkeys\Services\NullAuthenticationAudit;
use Illuminate\Container\Attributes\Bind;
use Illuminate\Container\Attributes\Scoped;

#[Bind(NullAuthenticationAudit::class)]
#[Scoped]
interface AuthenticationAudit
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function record(string $event, ?int $userId = null, array $context = []): void;
}
