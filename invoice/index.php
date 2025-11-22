<?php
declare(strict_types=1);
require_once __DIR__.'/auth/auth.php';
require_once __DIR__.'/auth/config.php';

start_session();
$u = current_user();

if (!$u) {
  header('Location: /login.php');
  exit;
}

$allowed = $u['allowed'] ?? [];

// 1 empresa → manda direto
if (count($allowed) === 1) {
  $slug = $allowed[0];
  $target = ($slug === COMPANY_MEZ) ? URL_MEZ_HOME : URL_LCR_HOME;
  header('Location: '.$target);
  exit;
}

// 0 empresa → erro
if (count($allowed) === 0) {
  http_response_code(403);
  header('Content-Type: text/plain; charset=utf-8');
  echo "403 Forbidden: sua conta não possui acesso a nenhuma empresa.";
  exit;
}

// 2+ empresas → mostra seletor
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Escolha a empresa | Invoices</title>
  <style>
    body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu;display:flex;align-items:center;justify-content:center;min-height:100svh;margin:0;background:#0f172a;color:#e2e8f0}
    .wrap{width:420px;background:#0b1220;border:1px solid #1f2937;border-radius:16px;padding:24px;box-shadow:0 10px 40px rgba(0,0,0,.4)}
    h1{font-size:20px;margin:0 0 16px}
    .grid{display:grid;gap:12px}
    a{display:block;text-decoration:none;padding:14px 16px;border-radius:10px;border:1px solid #334155;background:#111827;color:#e5e7eb;font-weight:600;text-align:center}
    a:hover{background:#1f2937}
    .muted{margin-top:16px;font-size:12px;color:#9ca3af;text-align:center}
  </style>
</head>
<body>
  <div class="wrap">
    <h1>Escolha a empresa</h1>
    <div class="grid">
      <?php if (in_array(COMPANY_MEZ, $allowed, true)): ?>
        <a href="<?= htmlspecialchars(URL_MEZ_HOME) ?>">MEA Cleaning</a>
      <?php endif; ?>
      <?php if (in_array(COMPANY_LCR, $allowed, true)): ?>
        <a href="<?= htmlspecialchars(URL_LCR_HOME) ?>">LC Rodrigues</a>
      <?php endif; ?>
    </div>
    <div class="muted"><a href="/logout.php" style="color:#93c5fd">Sair</a></div>
  </div>
</body>
</html>
