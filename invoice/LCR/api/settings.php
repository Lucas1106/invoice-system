<?php
declare(strict_types=1);

require_once __DIR__ . '/../../auth/company_gate.php';
require_company(COMPANY_LCR);
require_once __DIR__ . '/../../auth/db.php';

header('Content-Type: application/json; charset=utf-8');

$pdo = db();
$action = $_GET['action'] ?? null;

try {

    // 🔹 1) GET SETTINGS
    if ($action === 'get') {
        $stmt = $pdo->query("
            SELECT company, contact, phone, email, payment, updated_at
            FROM lcr_settings WHERE id = 1 LIMIT 1
        ");
        $row = $stmt->fetch();

        if (!$row) {
            echo json_encode(['error' => 'settings_not_found']);
            exit;
        }

        echo json_encode($row, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 🔹 2) SAVE SETTINGS
    if ($action === 'save') {

        $body = json_decode(file_get_contents('php://input'), true);
        if (!$body) {
            echo json_encode(['error' => 'invalid_json']);
            exit;
        }

        $stmt = $pdo->prepare("
            REPLACE INTO lcr_settings (id, company, contact, phone, email, payment, updated_at)
            VALUES (1, :company, :contact, :phone, :email, :payment, NOW())
        ");

        $stmt->execute([
            ':company' => $body['company'] ?? '',
            ':contact' => $body['contact'] ?? '',
            ':phone'   => $body['phone'] ?? '',
            ':email'   => $body['email'] ?? '',
            ':payment' => $body['payment'] ?? '',
        ]);

        echo json_encode(['ok' => true]);
        exit;
    }

    // 🔹 default
    echo json_encode(['error' => 'invalid_action']);

} catch (Throwable $e) {
    echo json_encode([
        'error'  => 'db_error',
        'detail' => $e->getMessage()
    ]);
}
