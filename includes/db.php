<?php
$dbDir = __DIR__ . '/../data';
if (!is_dir($dbDir)) mkdir($dbDir, 0777, true);
$pdo = new PDO('sqlite:' . $dbDir . '/tafaray.db');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec("CREATE TABLE IF NOT EXISTS etudiant (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  NomEt TEXT NOT NULL,
  PrenomEt TEXT NOT NULL,
  GenerationEt TEXT NOT NULL CHECK(GenerationEt IN ('Ancien','Novice')),
  LogementsEt TEXT NOT NULL CHECK(LogementsEt IN ('Interne','Externe'))
)");
$pdo->exec("CREATE TABLE IF NOT EXISTS droit (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  etudiant_id INTEGER NOT NULL,
  MontantPayee REAL NOT NULL DEFAULT 0,
  RestePayer REAL NOT NULL DEFAULT 0,
  DatePaiement TEXT NOT NULL,
  FOREIGN KEY(etudiant_id) REFERENCES etudiant(id) ON DELETE CASCADE
)");

function e($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

function getEtudiantWithDroit(PDO $pdo, int $id) {
  $st = $pdo->prepare("SELECT e.*, d.MontantPayee, d.RestePayer, d.DatePaiement
    FROM etudiant e LEFT JOIN droit d ON d.etudiant_id = e.id AND d.id = (
      SELECT id FROM droit WHERE etudiant_id = e.id ORDER BY id DESC LIMIT 1
    ) WHERE e.id = ?");
  $st->execute([$id]);
  return $st->fetch(PDO::FETCH_ASSOC);
}

function allEtudiantsWithDroit(PDO $pdo) {
  return $pdo->query("SELECT e.*, d.MontantPayee, d.RestePayer, d.DatePaiement
    FROM etudiant e LEFT JOIN droit d ON d.etudiant_id = e.id AND d.id = (
      SELECT id FROM droit WHERE etudiant_id = e.id ORDER BY id DESC LIMIT 1
    ) ORDER BY e.NomEt, e.PrenomEt")->fetchAll(PDO::FETCH_ASSOC);
}

function qrCodeContent(array $et): string {
  return "SENI-2026-{$et['NomEt']}.{$et['PrenomEt']}\n"
       . "Nom: {$et['NomEt']}\n"
       . "Prenom: {$et['PrenomEt']}\n"
       . "Generation: {$et['GenerationEt']}\n"
       . "Logement: {$et['LogementsEt']}\n"
       . "Montant Paye: " . ($et['MontantPayee'] ?? 0) . " Ar\n"
       . "Reste a payer: " . ($et['RestePayer'] ?? 0) . " Ar\n"
       . "Date paiement: " . ($et['DatePaiement'] ?? '-');
}

function qrImageUrl(string $data, int $size = 300): string {
  return "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&data=" . urlencode($data);
}


function fetchQrPng(string $data, int $size = 300): string {
  require_once __DIR__ . '/../lib/qrcode.php';

  $tmp = tempnam(sys_get_temp_dir(), 'qr_') . '.png';

  try {

    $qr = QRCode::getMinimumQRCode($data, '1');
$qr->make();

    // createImage retourne une resource GD ; on l'enregistre en PNG
    $im = $qr->createImage(6, 2); // 6 = taille pixel par module, 2 = marge
    imagepng($im, $tmp);
    imagedestroy($im);
  } catch (\Throwable $e) {
    $im = imagecreatetruecolor($size, $size);
    $white = imagecolorallocate($im, 255, 255, 255);
    imagefill($im, 0, 0, $white);
    imagepng($im, $tmp);
    imagedestroy($im);
  }

  return $tmp;
}
