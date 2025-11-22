<?php
declare(strict_types=1);
ob_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';

$input = json_decode(file_get_contents("php://input"), true);

$email = $input["email"] ?? "";
$password = $input["password"] ?? "";

if (!$email || !$password) {
    echo json_encode(['ok' => false, 'error' => 'missing_fields']);
    exit;
}

try {
    global $pdo;

    $stmt = $pdo->prepare("SELECT id, name, email, password FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);

    $user = $stmt->fetch();

    if (!$user) {
        echo json_encode(['ok' => false, 'error' => 'invalid_credentials']);
        exit;
    }

    // Comparação simples, pois o sistema não usa password_hash
    if ($user['password'] !== $password) {
        echo json_encode(['ok' => false, 'error' => 'invalid_credentials']);
        exit;
    }

    $token = generate_token($user);

    echo json_encode([
        'ok' => true,
        'token' => $token,
        'user' => [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email']
        ]
    ]);
    exit;

} catch (Exception $e) {
    echo json_encode(['ok' => false, 'error' => 'server_error', 'detail' => $e->getMessage()]);
    exit;
}
