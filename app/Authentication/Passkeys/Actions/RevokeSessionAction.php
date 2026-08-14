<?php

namespace App\Authentication\Passkeys\Actions;

use App\Authentication\Passkeys\Contracts\AuthenticationAudit;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final readonly class RevokeSessionAction
{
    public function __construct(private AuthenticationAudit $audit) {}

    public function handle(string $sessionId, string $currentSessionId, User $user, string $ipAddress, string $userAgent): bool
    {
        if ($sessionId === $currentSessionId) {
            return false;
        }

        $deleted = DB::table(config('session.table', 'sessions'))
            ->where('id', $sessionId)
            ->where('user_id', $user->id)
            ->delete();

        if ($deleted === 1) {
            $this->audit->record('session.revoked', $user->id, [
                'ip_address' => $ipAddress,
                'risk_level' => 'info',
                'session_id' => $sessionId,
                'user_agent' => $userAgent,
            ]);
        }

        return $deleted === 1;
    }
}
