<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
header('Content-Type: application/json; charset=utf-8');

$respond = static function (int $status, array $payload): never {
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
};

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $respond(405, ['success' => false, 'message' => 'Method not allowed']);
}

$rawBody = file_get_contents('php://input');
$payload = json_decode($rawBody, true);
if (!is_array($payload)) {
    $respond(400, ['success' => false, 'message' => 'Invalid JSON payload']);
}

$sepayConfig = require __DIR__ . '/../config/sepay.php';
$conn->query("CREATE TABLE IF NOT EXISTS sepay_transactions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sepay_id VARCHAR(100) NOT NULL UNIQUE,
    gateway VARCHAR(100) NOT NULL,
    transaction_date DATETIME NOT NULL,
    account_number VARCHAR(100) NULL,
    sub_account VARCHAR(250) NULL,
    code VARCHAR(250) NULL,
    amount_in BIGINT NOT NULL DEFAULT 0,
    amount_out BIGINT NOT NULL DEFAULT 0,
    accumulated BIGINT NOT NULL DEFAULT 0,
    content TEXT NULL,
    reference_code VARCHAR(255) NULL,
    body JSON NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_sepay_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$sepayIdColumn = $conn->query("SHOW COLUMNS FROM sepay_transactions LIKE 'sepay_id'");
if ($sepayIdColumn && ($sepayIdDefinition = $sepayIdColumn->fetch_assoc())
    && stripos((string) ($sepayIdDefinition['Type'] ?? ''), 'bigint') === 0) {
    if (!$conn->query("ALTER TABLE sepay_transactions MODIFY sepay_id VARCHAR(100) NOT NULL UNIQUE")) {
        $respond(500, ['success' => false, 'message' => 'Payment schema migration failed']);
    }
}
$providerColumn = $conn->query("SHOW COLUMNS FROM payment_orders LIKE 'provider_transaction_id'");
if ($providerColumn && ($providerDefinition = $providerColumn->fetch_assoc())
    && stripos((string) ($providerDefinition['Type'] ?? ''), 'bigint') === 0) {
    if (!$conn->query("ALTER TABLE payment_orders MODIFY provider_transaction_id VARCHAR(100) NULL UNIQUE")) {
        $respond(500, ['success' => false, 'message' => 'Payment schema migration failed']);
    }
}
foreach (['employer_id' => 'INT UNSIGNED NULL', 'job_payload' => 'JSON NULL', 'job_id' => 'INT UNSIGNED NULL'] as $column => $definition) {
    $columnCheck = $conn->query("SHOW COLUMNS FROM payment_orders LIKE '" . $column . "'");
    if ($columnCheck && $columnCheck->num_rows === 0) {
        if (!$conn->query("ALTER TABLE payment_orders ADD COLUMN {$column} {$definition}")) {
            $respond(500, ['success' => false, 'message' => 'Payment schema migration failed']);
        }
    }
}
$isGatewayIpn = isset($payload['notification_type'], $payload['order']) && is_array($payload['order']);

if (!$isGatewayIpn) {
    $signature = (string) ($_SERVER['HTTP_X_SEPAY_SIGNATURE'] ?? '');
    $timestamp = (int) ($_SERVER['HTTP_X_SEPAY_TIMESTAMP'] ?? 0);
    $webhookSecret = (string) ($sepayConfig['webhook_secret'] ?? '');
    $authorization = (string) ($_SERVER['HTTP_AUTHORIZATION'] ?? '');
    $configuredApiKey = (string) ($sepayConfig['webhook_api_key'] ?? '');
    $apiKeyValid = $configuredApiKey !== ''
        && preg_match('/^Apikey\s+(.+)$/i', $authorization, $apiKeyMatch) === 1
        && hash_equals($configuredApiKey, trim($apiKeyMatch[1]));
    $hmacValid = false;
    if ($webhookSecret !== '' && $signature !== '' && $timestamp > 0 && abs(time() - $timestamp) <= 300) {
        $signatureValue = preg_replace('/^sha256=/i', '', trim($signature));
        $expectedHex = hash_hmac('sha256', $timestamp . '.' . $rawBody, $webhookSecret);
        $expectedBase64 = base64_encode(hash_hmac('sha256', $timestamp . '.' . $rawBody, $webhookSecret, true));
        $hmacValid = hash_equals($expectedHex, $signatureValue)
            || hash_equals($expectedBase64, $signatureValue);
    }
    if (!$apiKeyValid && !$hmacValid) {
        $respond(401, ['success' => false, 'message' => 'Invalid HMAC/API key. Configure HMAC-SHA256 in SePay Dashboard and set SEPAY_WEBHOOK_SECRET.']);
    }
}

if ($isGatewayIpn) {
    $orderData = $payload['order'];
    $transactionData = is_array($payload['transaction'] ?? null) ? $payload['transaction'] : [];
    $isPaid = ($payload['notification_type'] ?? '') === 'ORDER_PAID'
        && ($orderData['order_status'] ?? '') === 'CAPTURED'
        && in_array(($transactionData['transaction_status'] ?? 'APPROVED'), ['APPROVED', 'SUCCESS'], true);
    $transactionCode = trim((string) ($orderData['order_invoice_number'] ?? ''));
    $amount = (int) round((float) ($orderData['order_amount'] ?? 0));
    $providerId = trim((string) ($transactionData['transaction_id'] ?? $transactionData['id'] ?? $orderData['id'] ?? ''));
} else {
    $isPaid = ($payload['transferType'] ?? '') === 'in';
    $transactionCode = trim((string) ($payload['code'] ?? ''));
    if ($transactionCode === '') {
        $transactionCode = trim((string) ($payload['content'] ?? ''));
    }
    $amount = (int) ($payload['transferAmount'] ?? 0);
    $providerId = trim((string) ($payload['id'] ?? ''));
}

if (!$isPaid || $amount <= 0 || $transactionCode === '' || $providerId === '') {
    error_log('SePay payment ignored: ' . $rawBody);
    $respond(200, ['success' => true]);
}

if (!$isGatewayIpn) {
    $transactionStmt = $conn->prepare('INSERT IGNORE INTO sepay_transactions (sepay_id, gateway, transaction_date, account_number, sub_account, code, amount_in, amount_out, accumulated, content, reference_code, body) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    if (!$transactionStmt) {
        $respond(500, ['success' => false, 'message' => 'Cannot prepare transaction log: ' . $conn->error]);
    }
    $sepayId = trim((string) ($payload['id'] ?? ''));
    $gateway = (string) ($payload['gateway'] ?? '');
    $transactionDate = (string) ($payload['transactionDate'] ?? date('Y-m-d H:i:s'));
    $accountNumber = (string) ($payload['accountNumber'] ?? '');
    $subAccount = (string) ($payload['subAccount'] ?? '');
    $code = (string) ($payload['code'] ?? '');
    $amountIn = ($payload['transferType'] ?? '') === 'in' ? $amount : 0;
    $amountOut = ($payload['transferType'] ?? '') === 'out' ? $amount : 0;
    $accumulated = (int) ($payload['accumulated'] ?? 0);
    $content = (string) ($payload['content'] ?? '');
    $referenceCode = (string) ($payload['referenceCode'] ?? '');
    $transactionStmt->bind_param('ssssssiiisss', $sepayId, $gateway, $transactionDate, $accountNumber, $subAccount, $code, $amountIn, $amountOut, $accumulated, $content, $referenceCode, $rawBody);
    if (!$transactionStmt->execute()) {
        $error = $transactionStmt->error;
        $transactionStmt->close();
        $respond(500, ['success' => false, 'message' => 'Cannot save transaction: ' . $error]);
    }
    $isNewTransaction = $transactionStmt->affected_rows === 1;
    $transactionStmt->close();
    if (!$isNewTransaction) {
        $respond(200, ['success' => true]);
    }
}

$conn->begin_transaction();
try {
    $duplicateStmt = $conn->prepare('SELECT id FROM payment_orders WHERE provider_transaction_id = ? LIMIT 1 FOR UPDATE');
    if (!$duplicateStmt) {
        throw new RuntimeException('Cannot prepare duplicate payment query: ' . $conn->error);
    }
    $duplicateStmt->bind_param('s', $providerId);
    if (!$duplicateStmt->execute()) {
        throw new RuntimeException('Cannot check duplicate payment: ' . $duplicateStmt->error);
    }
    $duplicate = $duplicateStmt->get_result()->fetch_assoc();
    $duplicateStmt->close();
    if ($duplicate) {
        $conn->commit();
        $respond(200, ['success' => true]);
    }

    $orderStmt = $conn->prepare('SELECT id, amount, status, employer_id, job_payload, job_id FROM payment_orders WHERE transaction_code = ? OR invoice_number = ? LIMIT 1 FOR UPDATE');
    if (!$orderStmt) {
        throw new RuntimeException('Cannot prepare payment order query: ' . $conn->error);
    }
    $orderStmt->bind_param('ss', $transactionCode, $transactionCode);
    if (!$orderStmt->execute()) {
        throw new RuntimeException('Cannot find payment order: ' . $orderStmt->error);
    }
    $order = $orderStmt->get_result()->fetch_assoc();
    $orderStmt->close();
    if (!$order || (int) $order['amount'] !== $amount) {
        error_log('SePay payment mismatch: code=' . $transactionCode . ', amount=' . $amount . ', body=' . $rawBody);
        throw new RuntimeException('Payment order not found or amount mismatch');
    }

    if ($order['status'] !== 'paid' || empty($order['job_id'])) {
        $job = json_decode((string) ($order['job_payload'] ?? ''), true);
        if (!is_array($job) || empty($job['company_id']) || empty($order['employer_id'])) {
            throw new RuntimeException('Payment order does not contain a job draft');
        }

        $companyStmt = $conn->prepare('SELECT id FROM company WHERE id = ? AND employer_id = ? LIMIT 1');
        $companyId = (int) $job['company_id'];
        $employerId = (int) $order['employer_id'];
        $companyStmt->bind_param('ii', $companyId, $employerId);
        if (!$companyStmt->execute() || !$companyStmt->get_result()->fetch_assoc()) {
            $companyStmt->close();
            throw new RuntimeException('Company does not belong to employer');
        }
        $companyStmt->close();

        $numberStmt = $conn->prepare('SELECT COALESCE(MAX(job_post_number_by_employer), 0) + 1 AS next_number FROM job WHERE posted_by_employer_id = ? FOR UPDATE');
        if (!$numberStmt) {
            throw new RuntimeException('Cannot prepare job number query: ' . $conn->error);
        }
        $numberStmt->bind_param('i', $employerId);
        if (!$numberStmt->execute()) {
            throw new RuntimeException('Cannot calculate job number: ' . $numberStmt->error);
        }
        $nextNumber = (int) ($numberStmt->get_result()->fetch_assoc()['next_number'] ?? 1);
        $numberStmt->close();

        $jobStmt = $conn->prepare('INSERT INTO job (posted_by_employer_id, company_id, job_post_number_by_employer, job_title, job_description, job_location, salary, post_duration, contact_email, contact_phone, required_certification, job_experience, job_requirement, job_benefit, no_employee_needed, job_position, job_detailed_location, job_location_district, job_type, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        if (!$jobStmt) {
            throw new RuntimeException('Cannot prepare job insert: ' . $conn->error);
        }
        $jobStatus = 'pending_review';
        $jobStmt->bind_param('iiissssissssssisssss', $employerId, $companyId, $nextNumber, $job['job_title'], $job['job_description'], $job['job_location'], $job['salary'], $job['post_duration'], $job['contact_email'], $job['contact_phone'], $job['required_certification'], $job['job_experience'], $job['job_requirement'], $job['job_benefit'], $job['no_employee_needed'], $job['job_position'], $job['job_detailed_location'], $job['job_location_district'], $job['job_type'], $jobStatus);
        if (!$jobStmt->execute()) {
            throw new RuntimeException('Cannot create job after payment: ' . $jobStmt->error);
        }
        $jobId = $conn->insert_id;
        $jobStmt->close();

        $reference = (string) ($payload['referenceCode'] ?? $transactionData['transaction_id'] ?? '');
        $updateStmt = $conn->prepare("UPDATE payment_orders SET status = 'paid', job_id = ?, provider_transaction_id = ?, provider_reference = ?, raw_webhook = ?, paid_at = NOW() WHERE id = ? AND job_id IS NULL");
        if (!$updateStmt) {
            throw new RuntimeException('Cannot prepare payment update: ' . $conn->error);
        }
        $updateStmt->bind_param('isssi', $jobId, $providerId, $reference, $rawBody, $order['id']);
        if (!$updateStmt->execute()) {
            throw new RuntimeException('Cannot update payment status: ' . $updateStmt->error);
        }
        if ($updateStmt->affected_rows !== 1) {
            throw new RuntimeException('Payment status was not updated');
        }
        $updateStmt->close();
    }

    $conn->commit();
    $respond(200, ['success' => true]);
} catch (Throwable $exception) {
    $conn->rollback();
    error_log('SePay webhook error: ' . $exception->getMessage());
    $respond(500, ['success' => false, 'message' => 'Webhook processing failed: ' . $exception->getMessage()]);
}
