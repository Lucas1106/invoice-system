<?php
declare(strict_types=1);

header('Content-Type: application/json');

$file = __DIR__ . '/clients.json';

if (!file_exists($file)) {
    echo json_encode(["clients" => []]);
    exit;
}

$data = json_decode(file_get_contents($file), true);
echo json_encode($data);
