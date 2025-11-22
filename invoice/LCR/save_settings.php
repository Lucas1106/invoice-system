<?php
declare(strict_types=1);
// protege por empresa
require_once __DIR__ . '/../auth/company_gate.php';
require_company(COMPANY_LCR);

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['error'=>'method_not_allowed']); exit;
}

// Lê JSON ou x-www-form-urlencoded
$ctype = strtolower($_SERVER['CONTENT_TYPE'] ?? '');
$data = [];
if (strpos($ctype, 'application/json') !== false) {
  $raw = file_get_contents('php://input');
  $data = json_decode($raw, true);
  if (!is_array($data)) { http_response_code(400); echo json_encode(['error'=>'invalid_json']); exit; }
} else {
  $data = $_POST ?: [];
}

$company = trim((string)($data['company'] ?? ''));
$contact = trim((string)($data['contact'] ?? ''));
$phone   = trim((string)($data['phone'] ?? ''));
$email   = trim((string)($data['email'] ?? ''));
$payment = trim((string)($data['payment'] ?? ''));

$file = __DIR__ . '/settings.json';
$tmp  = $file . '.tmp';
$out  = json_encode([
  'company'=>$company, 'contact'=>$contact, 'phone'=>$phone,
  'email'=>$email, 'payment'=>$payment, 'updated_at'=>date('c')
], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);

$fp = fopen($tmp, 'c+'); if(!$fp){ http_response_code(500); echo json_encode(['error'=>'temp_open_failed']); exit; }
if(!flock($fp, LOCK_EX)){ fclose($fp); http_response_code(500); echo json_encode(['error'=>'lock_failed']); exit; }
ftruncate($fp,0); fwrite($fp,$out); fflush($fp); flock($fp,LOCK_UN); fclose($fp);

if(!@rename($tmp,$file)){ @unlink($tmp); http_response_code(500); echo json_encode(['error'=>'rename_failed']); exit; }
echo json_encode(['ok'=>true]);
