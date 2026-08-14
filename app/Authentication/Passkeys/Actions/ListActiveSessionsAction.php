<?php

namespace App\Authentication\Passkeys\Actions;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final readonly class ListActiveSessionsAction
{
    /**
     * @return Collection<int, array{id: string, browser: string, ip_address: string, is_current: bool, last_seen: string, platform: string, status: string}>
     */
    public function handle(User $user, string $currentSessionId, string $currentIpAddress, string $currentUserAgent): Collection
    {
        $sessions = DB::table(config('session.table', 'sessions'))
            ->where('user_id', $user->id)
            ->orderByDesc('last_activity')
            ->get()
            ->map(function (object $session) use ($currentSessionId): array {
                $userAgent = (string) ($session->user_agent ?? '');

                return [
                    'browser' => $this->browserLabel($userAgent),
                    'id' => (string) $session->id,
                    'ip_address' => (string) ($session->ip_address ?? 'Unknown IP'),
                    'is_current' => (string) $session->id === $currentSessionId,
                    'last_seen' => CarbonImmutable::createFromTimestamp((int) $session->last_activity)->diffForHumans(),
                    'platform' => $this->platformLabel($userAgent),
                    'status' => (string) $session->id === $currentSessionId ? 'Current session' : 'Active',
                ];
            });

        if (! $sessions->contains(fn (array $session): bool => $session['is_current'])) {
            $sessions->prepend([
                'browser' => $this->browserLabel($currentUserAgent),
                'id' => $currentSessionId,
                'ip_address' => $currentIpAddress,
                'is_current' => true,
                'last_seen' => 'Just now',
                'platform' => $this->platformLabel($currentUserAgent),
                'status' => 'Current session',
            ]);
        }

        return $sessions->unique('id')->values();
    }

    private function browserLabel(string $userAgent): string
    {
        return match (true) {
            str_contains($userAgent, 'Edg/') => 'Microsoft Edge',
            str_contains($userAgent, 'Chrome/') && ! str_contains($userAgent, 'Edg/') => 'Google Chrome',
            str_contains($userAgent, 'Firefox/') => 'Firefox',
            str_contains($userAgent, 'Safari/') && ! str_contains($userAgent, 'Chrome/') => 'Safari',
            default => 'Browser session',
        };
    }

    private function platformLabel(string $userAgent): string
    {
        return match (true) {
            str_contains($userAgent, 'Mac OS X') => 'macOS',
            str_contains($userAgent, 'Windows') => 'Windows',
            str_contains($userAgent, 'Linux') => 'Linux',
            str_contains($userAgent, 'iPhone') || str_contains($userAgent, 'iPad') => 'iOS',
            str_contains($userAgent, 'Android') => 'Android',
            default => 'Unknown platform',
        };
    }
}
