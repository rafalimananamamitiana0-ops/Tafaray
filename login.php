<?php
require __DIR__ . '/includes/auth.php';
if (currentAdmin($pdo)) { header('Location: index.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['username'] ?? '');
    $p = (string)($_POST['password'] ?? '');

    if (!isset($_SESSION['login_attempts']))    $_SESSION['login_attempts']    = 0;
    if (!isset($_SESSION['login_locked_until'])) $_SESSION['login_locked_until'] = 0;

    if (time() < $_SESSION['login_locked_until']) {
        $secs  = $_SESSION['login_locked_until'] - time();
        $error = "Trop de tentatives. Réessayez dans {$secs}s.";
    } elseif ($u === '' || $p === '') {
        $error = "Veuillez remplir tous les champs.";
    } elseif (attemptLogin($pdo, $u, $p)) {
        $_SESSION['login_attempts'] = 0;
        header('Location: index.php'); exit;
    } else {
        $_SESSION['login_attempts']++;
        if ($_SESSION['login_attempts'] >= 5) {
            $_SESSION['login_locked_until'] = time() + 60;
            $_SESSION['login_attempts']     = 0;
            $error = "Trop de tentatives. Compte bloqué 60 secondes.";
        } else {
            $remaining = 5 - $_SESSION['login_attempts'];
            $error     = "Identifiants invalides. ({$remaining} tentative(s) restante(s))";
        }
    }
}
?><!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Connexion — TAFARAY-SENI</title>
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <style>
    /* ── Reset ── */
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --accent:      #d4a84c;
      --accent-2:    #f0d78c;
      --accent-soft: rgba(212,168,76,.14);
      --text:        #e6edf7;
      --muted:       #8a97ad;
      --line:        rgba(255,255,255,.07);
      --emerald:     #22c55e;
    }

    /* ── Fond global ── */
    html, body {
      height: 100%;
      font-family: 'Inter', system-ui, sans-serif;
      font-size: 14.5px;
      line-height: 1.55;
      color: var(--text);
    }

    body {
      min-height: 100vh;
      background: #070b13;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 24px 16px;
      position: relative;
      overflow-x: hidden;
    }

    /* Glow décoratif gauche */
    body::before {
      content: "";
      position: fixed;
      width: 520px; height: 520px;
      border-radius: 50%;
      background: radial-gradient(circle, #d4a84c, transparent 60%);
      top: -160px; left: -140px;
      filter: blur(110px);
      opacity: .42;
      pointer-events: none;
      z-index: 0;
    }

    /* Glow décoratif droit */
    body::after {
      content: "";
      position: fixed;
      width: 640px; height: 640px;
      border-radius: 50%;
      background: radial-gradient(circle, #6366f1, transparent 60%);
      bottom: -240px; right: -200px;
      filter: blur(110px);
      opacity: .26;
      pointer-events: none;
      z-index: 0;
    }

    /* Grille de fond */
    .bg-grid {
      position: fixed; inset: 0;
      background-image:
        linear-gradient(rgba(255,255,255,.04) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255,255,255,.04) 1px, transparent 1px);
      background-size: 42px 42px;
      pointer-events: none;
      z-index: 0;
    }

    /* ── Carte ── */
    .card {
      position: relative;
      z-index: 1;
      width: 100%;
      max-width: 430px;
      background: linear-gradient(180deg, rgba(20,26,40,.93), rgba(12,16,26,.96));
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      border: 1px solid rgba(212,168,76,.25);
      border-radius: 24px;
      padding: 38px 34px 32px;
      box-shadow: 0 30px 80px -30px rgba(0,0,0,.75),
                  inset 0 0 0 1px rgba(255,255,255,.04);
    }

    .card::before {
      content: "";
      position: absolute; inset: 0;
      border-radius: 24px;
      border: 1px solid rgba(255,255,255,.05);
      pointer-events: none;
    }

    /* ── En-tête ── */
    .card-head {
      text-align: center;
      margin-bottom: 26px;
    }

    .logo {
      display: inline-grid;
      place-items: center;
      width: 52px; height: 52px;
      border-radius: 14px;
      background: linear-gradient(135deg, var(--accent), var(--accent-2));
      color: #1a1208;
      font-family: 'Sora', sans-serif;
      font-size: 19px;
      font-weight: 800;
      box-shadow: 0 8px 22px -8px rgba(212,168,76,.6);
      margin-bottom: 16px;
    }

    .eyebrow {
      display: block;
      font-size: 10.5px;
      letter-spacing: .22em;
      text-transform: uppercase;
      font-weight: 600;
      color: var(--accent);
      margin-bottom: 6px;
    }

    .card-head h2 {
      font-family: 'Sora', sans-serif;
      font-size: 25px;
      font-weight: 700;
      color: var(--text);
      margin-bottom: 5px;
      letter-spacing: -.02em;
    }

    .card-head p {
      font-size: 13px;
      color: var(--muted);
    }

    /* ── Flash ── */
    .flash {
      padding: 11px 15px;
      border-radius: 10px;
      font-size: 13.5px;
      font-weight: 500;
      margin-bottom: 18px;
      transition: opacity .5s ease;
    }

    .flash-err {
      background: rgba(239,68,68,.1);
      color: #fca5a5;
      border: 1px solid rgba(239,68,68,.25);
    }

    .flash-ok {
      background: rgba(34,197,94,.1);
      color: #86efac;
      border: 1px solid rgba(34,197,94,.25);
    }

    /* ── Formulaire ── */
    .form { display: flex; flex-direction: column; gap: 15px; }

    .field { display: flex; flex-direction: column; }

    .field label {
      font-size: 11px;
      text-transform: uppercase;
      letter-spacing: .14em;
      font-weight: 600;
      color: var(--muted);
      margin-bottom: 7px;
    }

    /* Wrapper champ + icône */
    .field-wrap {
      position: relative;
      display: flex;
      align-items: center;
      background: #0a1120;
      border: 1px solid var(--line);
      border-radius: 12px;
      transition: border-color .18s, box-shadow .18s;
    }

    .field-wrap:focus-within {
      border-color: var(--accent);
      box-shadow: 0 0 0 4px var(--accent-soft);
    }

    .field-icon {
      position: absolute;
      left: 14px;
      top: 50%;
      transform: translateY(-50%);
      font-size: 15px;
      color: var(--accent);
      pointer-events: none;
      line-height: 1;
    }

    .field-wrap input {
      flex: 1;
      background: transparent;
      border: none;
      outline: none;
      color: var(--text);
      font-family: inherit;
      font-size: 14.5px;
      padding: 14px 14px 14px 44px;
    }

    .field-wrap input::placeholder { color: var(--muted); opacity: .5; }

    /* Bouton afficher/masquer généré par JS */
    .btn-toggle {
      position: absolute;
      right: 10px;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      color: var(--muted);
      cursor: pointer;
      padding: 6px;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      line-height: 1;
      transition: color .15s, background .15s;
    }

    .btn-toggle:hover {
      color: var(--accent-2);
      background: var(--accent-soft);
    }

    /* ── Bouton connexion ── */
    .btn-submit {
      width: 100%;
      margin-top: 4px;
      padding: 14px;
      border: none;
      border-radius: 12px;
      background: linear-gradient(135deg, var(--accent), var(--accent-2));
      color: #1a1208;
      font-family: 'Sora', sans-serif;
      font-size: 15px;
      font-weight: 700;
      letter-spacing: .02em;
      cursor: pointer;
      box-shadow: 0 10px 28px -10px rgba(212,168,76,.55);
      transition: filter .18s, transform .15s;
    }

    .btn-submit:hover   { filter: brightness(1.06); transform: translateY(-1px); }
    .btn-submit:active  { transform: translateY(0); filter: brightness(.98); }
    .btn-submit:disabled {
      opacity: .5;
      cursor: not-allowed;
      transform: none;
      filter: none;
    }

    /* ── Pied ── */
    .card-foot {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      margin-top: 22px;
      font-size: 12px;
      color: var(--muted);
    }

    .dot {
      width: 7px; height: 7px;
      border-radius: 50%;
      background: var(--emerald);
      box-shadow: 0 0 6px var(--emerald);
      flex-shrink: 0;
    }

    /* ── Responsive ── */
    @media (max-width: 480px) {
      .card { padding: 28px 20px 24px; border-radius: 20px; }
      .card-head h2 { font-size: 22px; }
    }
  </style>
</head>
<body>

  <div class="bg-grid" aria-hidden="true"></div>

  <main class="card" role="main">

    <!-- En-tête -->
    <div class="card-head">
      <div class="logo">TS</div>
      <span class="eyebrow">Espace administrateur</span>
      <h2>Connexion</h2>
      <p>Entrez vos identifiants pour accéder au tableau de bord</p>
    </div>

    <!-- Message d'erreur -->
    <?php if ($error !== ''): ?>
      <div id="flash-msg" class="flash flash-err" role="alert">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <!-- Formulaire -->
    <form method="post" class="form" autocomplete="off" novalidate>

      <div class="field">
        <label for="username">Identifiant</label>
        <div class="field-wrap">
       
          <input
          style="padding-left:14px;"
            id="username"
            type="text"
            name="username"
            placeholder="admin"
            required
            autofocus
            value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
          >
        </div>
      </div>

      <div class="field">
        <label for="password">Mot de passe</label>
        <div class="field-wrap" id="pwd-wrap">
         
          <input
          style="padding-left:14px;"
            id="password"
            type="password"
            name="password"
            placeholder="••••••••••"
            required
          >
        </div>
      </div>

      <button type="submit" class="btn-submit" id="btn-login">
        Se connecter &nbsp;
      </button>

    </form>

    <!-- Pied -->
    <div class="card-foot">
  <span>© · Site créé par RAFALIMANANA Mamitiana</span>
</div>

  </main>

  <script>
  (function () {

    /* ── Auto-hide du message d'erreur après 4 s ── */
    const flash = document.getElementById('flash-msg');
    if (flash) {
      setTimeout(function () {
        flash.style.transition = 'opacity .5s ease';
        flash.style.opacity    = '0';
        setTimeout(function () { flash.remove(); }, 520);
      }, 4000);
    }

    /* ── Bouton Afficher / Masquer le mot de passe ── */
    const pwdInput = document.getElementById('password');
    const pwdWrap  = document.getElementById('pwd-wrap');

    if (pwdInput && pwdWrap) {
      const EYE_OPEN = `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>`;
      const EYE_OFF  = `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>`;

      const toggle   = document.createElement('button');
      toggle.type    = 'button';
      toggle.className = 'btn-toggle';
      toggle.innerHTML = EYE_OPEN;
      toggle.setAttribute('aria-label', 'Afficher le mot de passe');

      toggle.addEventListener('click', function () {
        const show       = pwdInput.type === 'password';
        pwdInput.type    = show ? 'text'    : 'password';
        toggle.innerHTML = show ? EYE_OFF   : EYE_OPEN;
        toggle.setAttribute('aria-label', show ? 'Masquer le mot de passe' : 'Afficher le mot de passe');
        pwdInput.focus();
      });

      pwdWrap.appendChild(toggle);
    }

    /* ── Désactiver le bouton pendant la soumission ── */
    const form    = document.querySelector('.form');
    const btnLogin = document.getElementById('btn-login');

    if (form && btnLogin) {
      form.addEventListener('submit', function () {
        btnLogin.disabled     = true;
        btnLogin.textContent  = 'Connexion en cours…';
      });
    }

  })();
  </script>

</body>
</html>