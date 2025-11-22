<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, X-Secret');
header('Access-Control-Allow-Methods: POST, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$SECRET = 'hG9!zQ2@pL8$wV6#nX3*eR7^cT5&yB1';

$h = $_SERVER['HTTP_X_SECRET'] ?? '';
if (!hash_equals($SECRET, $h)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'forbidden']);
    exit;
}

require_once __DIR__ . '/../auth/db.php';

$raw = file_get_contents('php://input') ?: '';
$data = json_decode($raw, true);
if (!is_array($data)) $data = $_POST;

$serviceDate    = $data['service_date'] ?? null;
$address        = trim($data['address'] ?? '');
$clientInternal = trim($data['client_internal'] ?? '');
$invoiceCounter = (int)($data['invoice_counter'] ?? 0);
$invoiceNumber  = trim($data['invoice_number'] ?? '');
$total          = (float)($data['total'] ?? 0);
$items          = $data['items'] ?? [];

if (!is_array($items)) $items = [];

if ($invoiceCounter <= 0) {
    echo json_encode(['ok' => false, 'error' => 'bad_counter']);
    exit;
}

try {
    $pdo = db();

    $sql = "INSERT INTO invoices_lcr
            (service_date, address, client_internal, invoice_counter, invoice_number, total, items_json)
            VALUES
            (:service_date, :address, :client_internal, :invoice_counter, :invoice_number, :total, :items_json)";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':service_date'    => $serviceDate,
        ':address'         => $address ?: null,
        ':client_internal' => $clientInternal ?: null,
        ':invoice_counter' => $invoiceCounter,
        ':invoice_number'  => $invoiceNumber,
        ':total'           => $total,
        ':items_json'      => json_encode($items, JSON_UNESCAPED_UNICODE),
    ]);

    echo json_encode([
        'ok' => true,
        'id' => $pdo->lastInsertId()
    ]);

} catch (Throwable $e) {
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
