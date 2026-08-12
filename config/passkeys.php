<?php

$defaultOrigin = config('app.url');
$configuredOrigins = array_values(array_filter(array_map(
    static fn (string $origin): string => trim($origin),
    explode(',', env('PASSKEYS_ALLOWED_ORIGINS', $defaultOrigin ?: ''))
)));
$configuredIds = array_values(array_unique(array_filter([
    env('PASSKEYS_RELYING_PARTY_ID'),
    ...array_map(
        static fn (string $origin): string => (string) (parse_url($origin, PHP_URL_HOST) ?: ''),
        $configuredOrigins
    ),
], static fn ($host): bool => is_string($host) && $host !== '' && filter_var($host, FILTER_VALIDATE_IP) === false)));

return [
    'relying_party' => [
        'id' => env('PASSKEYS_RELYING_PARTY_ID', parse_url($defaultOrigin ?? '', PHP_URL_HOST) ?: 'localhost'),
        'ids' => $configuredIds,
        'name' => env('PASSKEYS_RELYING_PARTY_NAME', config('app.name', 'Laravel')),
        'origins' => $configuredOrigins,
    ],
    'feature_flags' => [
        'enabled' => 'passkeys.enabled',
        'registration' => 'passkeys.registration',
        'login' => 'passkeys.login',
        'device_management' => 'passkeys.device-management',
        'risk_engine' => 'passkeys.risk-engine',
    ],
    'lab' => [
        'route' => 'passkeys.lab',
    ],
];
