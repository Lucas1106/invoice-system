<?php
declare(strict_types=1);
require_once __DIR__.'/auth.php';
require_once __DIR__.'/config.php';

function require_company(string $requiredCompany): void {
  $u = current_user();
  if (!$u) {
    header('Location: /login.php?next=' . urlencode($_SERVER['REQUEST_URI'] ?? '/'));
    exit;
  }
  $allowed = $u['allowed'] ?? [];
  if (!in_array($requiredCompany, $allowed, true)) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    echo '403 Forbidden: você não tem acesso a esta empresa.';
    exit;
  }
}
