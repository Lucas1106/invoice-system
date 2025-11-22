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

require_once __DIR__ . '/../../auth/db.php';

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) $data = $_POST;

$clientName = trim($data['client_name'] ?? '');

if ($clientName === '') {
    echo json_encode(['ok' => false, 'error' => 'empty_name']);
    exit;
}

try {
    $pdo = db();
    $stmt = $pdo->prepare("INSERT INTO clients_lcr (client_name) VALUES (:name)");
    $ok = $stmt->execute([':name' => $clientName]);

    if (!$ok) throw new RuntimeException('insert_failed');

    echo json_encode(['ok' => true, 'id' => $pdo->lastInsertId()]);

} catch (Throwable $e) {
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
