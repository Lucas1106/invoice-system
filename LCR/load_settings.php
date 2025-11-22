<?php
declare(strict_types=1);
require_once __DIR__ . '/../auth/company_gate.php';
require_company(COMPANY_LCR); // MEA → COMPANY_MEZ

header('Content-Type: application/json; charset=utf-8');

$file = __DIR__ . '/settings.json';
if (!is_file($file)) {
  echo json_encode([
    'company'=>'',
    'contact'=>'',
    'phone'=>'',
    'email'=>'',
    'payment'=>'',
  ], JSON_UNESCAPED_UNICODE);
  exit;
}
echo file_get_contents($file);
