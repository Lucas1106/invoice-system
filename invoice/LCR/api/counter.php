<?php
declare(strict_types=1);

require_once __DIR__ . '/../../auth/db.php';

header('Content-Type: application/json; charset=utf-8');

$pdo = db();
$action = $_GET['action'] ?? null;

try {

    // 🔹 1) GET COUNTER
    if ($action === 'get') {

        $stmt = $pdo->query("SELECT counter FROM lcr_counters WHERE id = 1 LIMIT 1");
        $row = $stmt->fetch();

        echo json_encode([
            'counter' => $row ? (int)$row['counter'] : 0
        ]);
        exit;
    }

    // 🔹 2) SET COUNTER
    if ($action === 'set') {

        $body = json_decode(file_get_contents('php://input'), true);

        if (!isset($body['counter'])) {
            echo json_encode(['error' => 'missing_counter']);
            exit;
        }

        $stmt = $pdo->prepare("
            REPLACE INTO lcr_counters (id, counter, updated_at)
            VALUES (1, :counter, NOW())
        ");

        $stmt->execute([
            ':counter' => (int)$body['counter']
        ]);

        echo json_encode(['ok' => true]);
        exit;
    }

    // 🔹 3) INCREMENT COUNTER
    if ($action === 'increment') {

        $pdo->beginTransaction();
        $stmt = $pdo->query("SELECT counter FROM lcr_counters WHERE id = 1 FOR UPDATE");
        $row = $stmt->fetch();

        $next = $row ? (int)$row['counter'] + 1 : 1;

        $up = $pdo->prepare("
            REPLACE INTO lcr_counters (id, counter, updated_at)
            VALUES (1, :counter, NOW())
        ");

        $up->execute([':counter' => $next]);
        $pdo->commit();

        echo json_encode(['counter' => $next]);
        exit;
    }

    echo json_encode(['error' => 'invalid_action']);

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    echo json_encode([
        'error'  => 'db_error',
        'detail' => $e->getMessage()
    ]);
}
