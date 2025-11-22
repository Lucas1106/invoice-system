<?php
declare(strict_types=1);
// protege por empresa
require_once __DIR__ . '/../auth/company_gate.php';
require_company(COMPANY_LCR);

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'method_not_allowed']);
    exit;
}

// Lê JSON ou x-www-form-urlencoded
$ctype = strtolower($_SERVER['CONTENT_TYPE'] ?? '');
$data  = [];

if (strpos($ctype, 'application/json') !== false) {
    $raw  = file_get_contents('php://input');
    $data = json_decode($raw, true) ?? [];
} else {
    $data = $_POST;
}

$company = trim((string)($data['company'] ?? ''));
$contact = trim((string)($data['contact'] ?? ''));
$phone   = trim((string)($data['phone'] ?? ''));
$email   = trim((string)($data['email'] ?? ''));
$payment = trim((string)($data['payment'] ?? ''));

if ($company === '' || $contact === '' || $phone === '' || $email === '' || $payment === '') {
    http_response_code(400);
    echo json_encode(['error' => 'missing_fields']);
    exit;
}

try {
    $pdo = db();

    // id fixo = 1 para LCR
    $stmt = $pdo->prepare(
        "REPLACE INTO lcr_settings
         (id, company, contact, phone, email, payment, updated_at)
         VALUES
         (1, :company, :contact, :phone, :email, :payment, NOW())"
    );

    $stmt->execute([
        ':company' => $company,
        ':contact' => $contact,
        ':phone'   => $phone,
        ':email'   => $email,
        ':payment' => $payment,
    ]);

    echo json_encode(['ok' => true]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'error'  => 'db_error',
        'detail' => $e->getMessage(),
    ]);
}
