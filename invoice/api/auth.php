<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

$JWT_SECRET = "CHAVE_SECRETA_AQUI"; // troque isso por algo forte

function generate_token(array $user): string {
    global $JWT_SECRET;

    $header = ['alg' => 'HS256', 'typ' => 'JWT'];
    $payload = [
        'id' => $user['id'],
        'email' => $user['email'],
        'name' => $user['name'],
        'iat' => time(),
        'exp' => time() + (60 * 60 * 24 * 7) // 7 dias
    ];

    $base64Header = rtrim(strtr(base64_encode(json_encode($header)), '+/', '-_'), '=');
    $base64Payload = rtrim(strtr(base64_encode(json_encode($payload)), '+/', '-_'), '=');

    $signature = hash_hmac('sha256', "$base64Header.$base64Payload", $JWT_SECRET, true);
    $base64Signature = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');

    return "$base64Header.$base64Payload.$base64Signature";
}

function get_user_from_token(): ?array {
    global $JWT_SECRET;

    if (!isset($_SERVER['HTTP_AUTHORIZATION'])) return null;

    $auth = trim(str_replace('Bearer', '', $_SERVER['HTTP_AUTHORIZATION']));
    $parts = explode('.', $auth);

    if (count($parts) !== 3) return null;

    [$h, $p, $s] = $parts;

    $signatureCheck = rtrim(strtr(base64_encode(hash_hmac('sha256', "$h.$p", $JWT_SECRET, true)), '+/', '-_'), '=');

    if (!hash_equals($signatureCheck, $s)) return null;

    $payload = json_decode(base64_decode(strtr($p, '-_', '+/')), true);

    if (!$payload || $payload['exp'] < time()) {
        return null;
    }

    return $payload;
}

function require_auth(): array {
    $user = get_user_from_token();

    if (!$user) {
        echo json_encode(['ok' => false, 'error' => 'unauthorized']);
        exit;
    }

    return $user;
}
