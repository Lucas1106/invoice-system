<?php
declare(strict_types=1);
require_once __DIR__.'/config.php';
require_once __DIR__.'/db.php';

function start_session(): void {
  if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_set_cookie_params([
      'lifetime' => 0, 'path' => '/', 'secure' => COOKIE_SECURE,
      'httponly' => true, 'samesite' => COOKIE_SAMESITE,
    ]);
    session_start();
  }
}

function find_user_by_email(string $email): ?array {
  $st = db()->prepare("SELECT id,name,email,phone,password,role_level,is_blocked FROM users WHERE email = ? LIMIT 1");
  $st->execute([$email]);
  $u = $st->fetch();
  return $u ?: null;
}

function companies_for_user(int $userId): array {
  $st = db()->prepare("SELECT company_slug FROM invoice_user_companies WHERE user_id = ?");
  $st->execute([$userId]);
  return array_column($st->fetchAll(), 'company_slug');
}

function do_login(string $email, string $password): array {
  $u = find_user_by_email($email);
  if (!$u) return ['ok'=>false, 'error'=>'invalid_credentials'];

  $dbPass = (string)($u['password'] ?? '');
  // compatível com o site antigo (senha em texto puro)
  if (!hash_equals($dbPass, $password)) return ['ok'=>false, 'error'=>'invalid_credentials'];
  if ((int)($u['is_blocked'] ?? 0) === 1) return ['ok'=>false, 'error'=>'blocked'];

  $userId = (int)$u['id'];
  $allowed = companies_for_user($userId);
  if (!$allowed) return ['ok'=>false, 'error'=>'no_company_access'];

  start_session();
  $_SESSION['user'] = [
    'id'      => $userId,
    'email'   => (string)$u['email'],
    'name'    => (string)$u['name'],
    'allowed' => $allowed,
  ];
  return ['ok'=>true];
}

function current_user(): ?array {
  start_session();
  return $_SESSION['user'] ?? null;
}

function logout(): void {
  start_session();
  $_SESSION = [];
  if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time()-42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
  }
  session_destroy();
}
