<?php

$payosEnv = static function (string $name, string $default = ''): string {
    $value = getenv($name);
    if ($value === false || trim((string) $value) === '') {
        $value = $_SERVER[$name] ?? $_ENV[$name] ?? $default;
    }

    return trim((string) $value);
};

$publicUrl = rtrim($payosEnv('PAYOS_PUBLIC_URL'), '/');
if ($publicUrl === '') {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $appPath = trim(str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME'] ?? '/'))), '/.');
    $publicUrl = $scheme . '://' . $host . ($appPath === '' ? '' : '/' . $appPath);
}

return [
    'client_id' => $payosEnv('PAYOS_CLIENT_ID'),
    'api_key' => $payosEnv('PAYOS_API_KEY'),
    'checksum_key' => $payosEnv('PAYOS_CHECKSUM_KEY'),
    'public_url' => $publicUrl,
];
