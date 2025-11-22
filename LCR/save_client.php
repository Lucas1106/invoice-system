<?php
declare(strict_types=1);

header('Content-Type: application/json');

$secret = 'hG9!zQ2@pL8$wV6#nX3*eR7^cT5&yB1';
if (($_SERVER['HTTP_X_SECRET'] ?? '') !== $secret) {
    http_response_code(403);
    echo json_encode(["ok"=>false,"error"=>"invalid_secret"]);
    exit;
}

$input = json_decode(file_get_contents("php://input"), true);
if (!$input || !$input["name"]) {
    echo json_encode(["ok"=>false,"error"=>"invalid_data"]);
    exit;
}

$file = __DIR__ . '/clients.json';
$data = file_exists($file)
    ? json_decode(file_get_contents($file), true)
    : ["clients"=>[]];

if (!in_array($input["name"], $data["clients"])) {
    $data["clients"][] = $input["name"];
}

file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));

echo json_encode(["ok"=>true]);
