<?php
// Authentification — admin UNIQUE
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/db.php';

const ADMIN_USERNAME = 'admin';
const DEFAULT_ADMIN_PASSWORD = 'SeniAdmin@2026';

$pdo->exec("CREATE TABLE IF NOT EXISTS admin (
  id INTEGER PRIMARY KEY CHECK (id = 1),
  username TEXT NOT NULL UNIQUE,
  password_hash TEXT NOT NULL,
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
)");
if ((int)$pdo->query("SELECT COUNT(*) FROM admin")->fetchColumn() === 0) {
  $st = $pdo->prepare("INSERT INTO admin (id, username, password_hash) VALUES (1, ?, ?)");
  $st->execute([ADMIN_USERNAME, password_hash(DEFAULT_ADMIN_PASSWORD, PASSWORD_DEFAULT)]);
}

function currentAdmin(PDO $pdo) {
  if (empty($_SESSION['admin_id'])) return null;
  $st = $pdo->prepare("SELECT id, username FROM admin WHERE id = ?");
  $st->execute([$_SESSION['admin_id']]);
  return $st->fetch(PDO::FETCH_ASSOC) ?: null;
}
function requireAuth(PDO $pdo) {
  if (!currentAdmin($pdo)) { header('Location: login.php'); exit; }
}
function attemptLogin(PDO $pdo, string $username, string $password): bool {
  $st = $pdo->prepare("SELECT * FROM admin WHERE username = ? LIMIT 1");
  $st->execute([$username]);
  $a = $st->fetch(PDO::FETCH_ASSOC);
  if (!$a || !password_verify($password, $a['password_hash'])) return false;
  session_regenerate_id(true);
  $_SESSION['admin_id'] = (int)$a['id'];
  $_SESSION['admin_username'] = $a['username'];
  return true;
}
function logout() {
  $_SESSION = [];
  if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time()-42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
  }
  session_destroy();
}
