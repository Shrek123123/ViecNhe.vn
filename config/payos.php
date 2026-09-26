<?php

$payosEnv = static function (string $name, string $default = ''): string {
    $value = getenv($name);
    if ($value === false || trim((string) $value) === '') {
        $value = $_SERVER[$name] ?? $_ENV[$name] ?? $default;
    }

    return trim((string) $value);
};

$publicUrl = 'https://unmade-petite-carefully.ngrok-free.dev/ViecNhe';

return [
    'client_id' => $payosEnv('PAYOS_CLIENT_ID'),
    'api_key' => $payosEnv('PAYOS_API_KEY'),
    'checksum_key' => $payosEnv('PAYOS_CHECKSUM_KEY'),
    'public_url' => $publicUrl,
];
