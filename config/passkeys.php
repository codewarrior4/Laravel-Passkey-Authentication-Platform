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
        'enabled' => [
            'active' => env('PASSKEYS_ENABLED', true),
            'key' => 'passkeys.enabled',
            'label' => 'Passkeys platform',
        ],
        'registration' => [
            'active' => env('PASSKEYS_REGISTRATION_ENABLED', true),
            'key' => 'passkeys.registration',
            'label' => 'Registration',
        ],
        'login' => [
            'active' => env('PASSKEYS_LOGIN_ENABLED', true),
            'key' => 'passkeys.login',
            'label' => 'Login',
        ],
        'device_management' => [
            'active' => env('PASSKEYS_DEVICE_MANAGEMENT_ENABLED', true),
            'key' => 'passkeys.device-management',
            'label' => 'Device management',
        ],
        'risk_engine' => [
            'active' => env('PASSKEYS_RISK_ENGINE_ENABLED', true),
            'key' => 'passkeys.risk-engine',
            'label' => 'Risk engine',
        ],
    ],
    'lab' => [
        'route' => 'passkeys.lab',
    ],
];
