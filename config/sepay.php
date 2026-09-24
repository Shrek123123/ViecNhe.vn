<?php

$sepayEnv = static function (string $name, string $default = ''): string {
    $value = getenv($name);
    if ($value === false || trim((string) $value) === '') {
        $value = $_SERVER[$name] ?? $_ENV[$name] ?? $default;
    }

    return trim((string) $value);
};

$config = [
    'merchant_id' => $sepayEnv('SEPAY_MERCHANT_ID', 'SP-LIVE-QDBB2A79'),
    'secret_key' => $sepayEnv('SEPAY_SECRET_KEY', 'spsk_live_2t7BVFFWP3ASWikvgDAkbQ3FT7EXetGi'),
    'webhook_secret' => $sepayEnv('SEPAY_WEBHOOK_SECRET', 'Peepee123'),
    'webhook_api_key' => $sepayEnv('SEPAY_WEBHOOK_API_KEY'),
    'allow_unsigned_bank_webhook' => false,
    'environment' => $sepayEnv('SEPAY_ENVIRONMENT', 'production'),
    'public_url' => rtrim($sepayEnv('SEPAY_PUBLIC_URL', 'http://localhost/JobHive-main'), '/'),
];

$localConfigFile = __DIR__ . '/sepay.local.php';
if (is_file($localConfigFile)) {
    $localConfig = require $localConfigFile;
    if (is_array($localConfig)) {
        $config = array_merge($config, $localConfig);
    }
}

return $config;
