<?php
// save_counter.php
// Sets the invoice counter in the database (table lcr_counters).

declare(strict_types=1);

require_once __DIR__ . '/../auth/db.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, X-Secret');
header('Access-Control-Allow-Methods: POST, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$SECRET = 'hG9!zQ2@pL8$wV6#nX3*eR7^cT5&yB1'; // mesma senha do invoice.html

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'method_not_allowed']);
    exit;
}

// Valida X-Secret
$headers = function_exists('getallheaders') ? getallheaders() : [];
$provided = $headers['X-Secret'] ?? $headers['x-secret'] ?? null;

if ($provided !== $SECRET) {
    http_response_code(403);
    echo json_encode(['error' => 'forbidden']);
    exit;
}

// Lê JSON ou form-data
$ctype = strtolower($_SERVER['CONTENT_TYPE'] ?? '');
$data  = [];

if (strpos($ctype, 'application/json') !== false) {
    $raw  = file_get_contents('php://input');
    $data = json_decode($raw, true) ?? [];
} else {
    $data = $_POST;
}

if (!isset($data['counter'])) {
    http_response_code(400);
    echo json_encode(['error' => 'missing_counter']);
    exit;
}

$n = (int)$data['counter'];
if ($n < 0) {
    http_response_code(400);
    echo json_encode(['error' => 'invalid_counter']);
    exit;
}

try {
    $pdo = db();

    // id fixo = 1
    $stmt = $pdo->prepare(
        "REPLACE INTO lcr_counters (id, counter, updated_at)
         VALUES (1, :counter, NOW())"
    );
    $stmt->execute([':counter' => $n]);

    echo json_encode(['counter' => $n]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'error'  => 'db_error',
        'detail' => $e->getMessage(),
    ]);
}
