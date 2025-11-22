<?php
declare(strict_types=1);
require_once __DIR__ . '/../auth/company_gate.php';
require_company(COMPANY_LCR);
readfile(__DIR__ . '/index.html');
