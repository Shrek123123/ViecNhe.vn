<?php

require_once  '../vendor/autoload.php';

use PayOS\PayOS;

// Keep your PayOS key protected by including it by an env variable
$payOSClientId = '1a59b580-13ce-4970-8be0-5729597216fb';
$payOSApiKey = '1941fae4-cbab-4223-96bd-5488aa4dad19';
$payOSChecksumKey = '1bb680614be51f4d9b17697bb5e845e22dcd5e1912f3208a779fe74833d95dfc';

$payOS = new PayOS(
    clientId: $payOSClientId,
    apiKey: $payOSApiKey,
    checksumKey: $payOSChecksumKey
);

$YOUR_DOMAIN = 'http://localhost/JobHive-main';

$data = [
    "orderCode" => intval(substr(strval(microtime(true) * 10000), -6)),
    "amount" => 2000,
    "description" => "Thanh toán đơn hàng",
    "returnUrl" => $YOUR_DOMAIN . "/success.html",
    "cancelUrl" => $YOUR_DOMAIN . "/cancel.html"
];

$response = $payOS->paymentRequests->create($data);

header("HTTP/1.1 303 See Other");
header("Location: " . $response['checkoutUrl']);