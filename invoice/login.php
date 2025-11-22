<?php
declare(strict_types=1);
require_once __DIR__.'/auth/auth.php';
require_once __DIR__.'/auth/config.php';

start_session();
if (current_user()) {
  $allowed = $_SESSION['user']['allowed'] ?? [];
  if (!empty($_GET['next'])) {
    header('Location: ' . $_GET['next']); exit;
  }
  if (count($allowed) === 1) {
    $slug = $allowed[0];
    $target = ($slug === COMPANY_MEZ) ? URL_MEZ_HOME : URL_LCR_HOME;
    header('Location: '.$target); exit;
  }
}

$error = $_GET['error'] ?? '';
$next  = $_GET['next'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $pass  = (string)($_POST['password'] ?? '');
  $res   = do_login($email, $pass);
  if ($res['ok'] ?? false) {
    if ($next) { header('Location: '.$next); exit; }
    $allowed = $_SESSION['user']['allowed'] ?? [];
    if (in_array(COMPANY_MEZ,$allowed,true)) { header('Location: '.URL_MEZ_HOME); exit; }
    if (in_array(COMPANY_LCR,$allowed,true)) { header('Location: '.URL_LCR_HOME); exit; }
    $error = 'no_company_access';
  } else {
    $error = $res['error'] ?? 'login_failed';
  }
}
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover" />
  <title>Entrar | Invoices</title>

  <!-- iPhone PWA friendliness -->
  <meta name="theme-color" content="#0b1020" />
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

  <style>
    :root{
      --bg1:#0b1020; --bg2:#101a3a; --card:#0d1326aa; --stroke:#1c2748;
      --txt:#e6ecff; --muted:#9fb0d1; --accent:#8ef1c7; --accent-2:#6ae2ff;
      --danger:#ff9aa9;
      --radius:18px;
    }
    *{box-sizing:border-box}
    html,body{height:100%}
    body{
      margin:0;
      font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", "Apple Color Emoji","Segoe UI Emoji";
      color:var(--txt);
      background:
        radial-gradient(1200px 700px at 10% -10%, #1b2453 0%, transparent 60%),
        radial-gradient(900px 600px at 110% 10%, #123d4f 0%, transparent 55%),
        linear-gradient(180deg, var(--bg1), var(--bg2));
      display:flex; align-items:center; justify-content:center;
      padding: max(16px, env(safe-area-inset-top)) 16px max(16px, env(safe-area-inset-bottom));
    }
    .wrap{
      width:min(420px, 92vw);
      background:linear-gradient(180deg, #0c142b, #0a1124);
      border:1px solid var(--stroke);
      border-radius: var(--radius);
      padding:20px;
      position:relative;
      box-shadow:
        0 20px 60px rgba(0,0,0,.45),
        inset 0 1px 0 rgba(255,255,255,.04);
      backdrop-filter: blur(8px);
    }
    .brand{
      display:flex; align-items:center; gap:12px; margin-bottom:18px;
    }
    .logo{
      width:42px; height:42px; border-radius:12px;
      background: linear-gradient(135deg, var(--accent), var(--accent-2));
      display:grid; place-items:center; font-weight:800; color:#0b1020;
      box-shadow: 0 6px 16px rgba(106,226,255,.35);
      user-select:none;
    }
    h1{font-size:20px; margin:0; letter-spacing:.2px}
    p.sub{margin:6px 0 0; color:var(--muted); font-size:14px}
    form{margin-top:10px}
    label{display:block; font-size:13px; color:var(--muted); margin:12px 0 6px}
    .input{
      position:relative;
      border:1px solid var(--stroke);
      border-radius:14px;
      background:#0c142b;
      display:flex; align-items:center;
    }
    .input input{
      width:100%; border:0; outline:0; background:transparent;
      color:var(--txt); padding:14px 14px; font-size:16px; /* >=16px evita zoom iOS */
      letter-spacing:.2px;
    }
    .input .toggle{
      position:absolute; right:10px; top:50%; transform:translateY(-50%);
      background:#0f1a36; border:1px solid #1e2a52; color:#cfe0ff;
      padding:8px 10px; font-size:12px; border-radius:10px; cursor:pointer;
    }
    .actions{margin-top:18px; display:flex; flex-direction:column; gap:10px}
    button.primary{
      appearance:none; border:0; cursor:pointer;
      padding:14px 16px; font-weight:800; font-size:16px; letter-spacing:.3px;
      border-radius:14px;
      background: linear-gradient(180deg, #5ff0c8, #46c8f1);
      color:#081225;
      box-shadow: 0 10px 24px rgba(81,216,230,.35);
    }
    button.primary:active{transform:translateY(1px)}
    .muted{font-size:12px; color:var(--muted); text-align:center}
    .err{
      margin-top:10px; padding:10px 12px; border-radius:12px;
      background: #3a0f1a; border:1px solid #5f2633; color:#ffd6dc; font-size:13px;
    }
    .footer{
      margin-top:14px; display:flex; justify-content:center; gap:10px; flex-wrap:wrap;
      color:var(--muted); font-size:12px;
    }
    .footer a{color:#b7d7ff; text-decoration:none}
    .badge{
      position:absolute; right:16px; top:16px; font-size:11px;
      color:#9db2d6; background:#0e1733; border:1px solid #1c2748;
      padding:6px 8px; border-radius:999px;
    }
    /* iOS standalone safe-area fix */
    .ios-pad{padding-top: env(safe-area-inset-top); padding-bottom: env(safe-area-inset-bottom)}
  </style>
</head>
<body class="ios-pad">
  <div class="wrap" role="main" aria-labelledby="title">
    <div class="badge">Secure Access</div>
    <div class="brand">
      <div class="logo">IN</div>
      <div>
        <h1 id="title">Entrar no painel de Invoices</h1>
        <p class="sub">Acesso privado. Funciona como app (PWA) no iPhone.</p>
      </div>
    </div>

    <form method="post" action="/login.php<?= $next ? ('?next='.urlencode($next)) : '' ?>" novalidate>
      <label for="email">E-mail</label>
      <div class="input">
        <input id="email" name="email" type="email" inputmode="email" autocomplete="username"
               autocapitalize="none" spellcheck="false" required placeholder="voce@empresa.com">
      </div>

      <label for="password">Senha</label>
      <div class="input">
        <input id="password" name="password" type="password" autocomplete="current-password"
               minlength="1" required placeholder="••••••••">
        <button type="button" class="toggle" aria-label="Mostrar/ocultar senha" onclick="
          const p = document.getElementById('password');
          p.type = (p.type==='password') ? 'text' : 'password';
          this.textContent = (p.type==='password') ? 'Mostrar' : 'Ocultar';
        ">Mostrar</button>
      </div>

      <?php if ($error): ?>
        <div class="err">
          <?php
            echo [
              'invalid_credentials'=>'E-mail ou senha inválidos.',
              'blocked'=>'Usuário bloqueado.',
              'no_company_access'=>'Sua conta não tem acesso a nenhuma empresa.',
              'login_failed'=>'Não foi possível entrar agora. Tente novamente.',
            ][$error] ?? 'Erro ao entrar.';
          ?>
        </div>
      <?php endif; ?>

      <div class="actions">
        <button class="primary" type="submit">Entrar</button>
        <div class="muted">Dica: adicione à Tela de Início no iPhone para usar como app.</div>
      </div>
    </form>

    <div class="footer">
      <span><a href="/" aria-label="Voltar à página inicial">Início</a></span>
      <span>•</span>
      <span><a href="/logout.php">Sair</a></span>
    </div>
  </div>

  <script>
    // Qualidade de vida no iOS PWA
    (function(){
      // Foco inicial
      const email = document.getElementById('email');
      if (email && !('standalone' in navigator && navigator.standalone)) {
        setTimeout(()=> email.focus(), 150);
      }
      // Previne duplo submit
      const form = document.querySelector('form');
      form?.addEventListener('submit', () => {
        const btn = document.querySelector('button.primary');
        btn && (btn.disabled = true, btn.textContent = 'Entrando…');
      });
    })();
  </script>
</body>
</html>
