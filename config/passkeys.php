<?php

$defaultOrigin = config('app.url');

return [
    'relying_party' => [
        'id' => env('PASSKEYS_RELYING_PARTY_ID', parse_url($defaultOrigin ?? '', PHP_URL_HOST) ?: 'localhost'),
        'name' => env('PASSKEYS_RELYING_PARTY_NAME', config('app.name', 'Laravel')),
        'origins' => array_values(array_filter(array_map(
            static fn (string $origin): string => trim($origin),
            explode(',', env('PASSKEYS_ALLOWED_ORIGINS', $defaultOrigin ?: ''))
        ))),
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
