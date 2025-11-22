<?php
// increment_counter.php
// Increments and persists the invoice counter in counter.json.

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, X-Secret');
header('Access-Control-Allow-Methods: POST, OPTIONS');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

$SECRET = 'hG9!zQ2@pL8$wV6#nX3*eR7^cT5&yB1'; // <<< MESMA senha do invoice.html

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['error' => 'Method not allowed']);
  exit;
}

$clientSecret = $_SERVER['HTTP_X_SECRET'] ?? '';
if (!hash_equals($SECRET, $clientSecret)) {
  http_response_code(401);
  echo json_encode(['error' => 'unauthorized']);
  exit;
}

$file = __DIR__ . '/counter.json';
$tmp  = $file . '.tmp';

$counter = 0;
if (is_readable($file)) {
  $existing = json_decode(file_get_contents($file), true);
  if (isset($existing['counter'])) $counter = (int)$existing['counter'];
}
$counter = max(1, $counter + 1);

$json = json_encode(['counter' => $counter], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
if ($json === false) { http_response_code(500); echo json_encode(['error'=>'encode_failed']); exit; }

$fp = fopen($tmp, 'c+');
if (!$fp) { http_response_code(500); echo json_encode(['error'=>'temp_open_failed']); exit; }
if (!flock($fp, LOCK_EX)) { fclose($fp); http_response_code(500); echo json_encode(['error'=>'lock_failed']); exit; }
ftruncate($fp, 0);
fwrite($fp, $json);
fflush($fp);
flock($fp, LOCK_UN);
fclose($fp);

if (!rename($tmp, $file)) { @unlink($tmp); http_response_code(500); echo json_encode(['error'=>'rename_failed']); exit; }

echo json_encode(['counter' => $counter]);
