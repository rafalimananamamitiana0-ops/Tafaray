<?php
require __DIR__.'/includes/auth.php';
requireAuth($pdo);

$pageTitle = 'Tableau de bord';
$total = (int)$pdo->query("SELECT COUNT(*) FROM etudiant")->fetchColumn();
$novices = (int)$pdo->query("SELECT COUNT(*) FROM etudiant WHERE GenerationEt='Novice'")->fetchColumn();
$anciens = (int)$pdo->query("SELECT COUNT(*) FROM etudiant WHERE GenerationEt='Ancien'")->fetchColumn();
$totalPaye   = $pdo->query("SELECT COALESCE(SUM(MontantPayee), 0) FROM droit")->fetchColumn();
$nbPaiements = (int)$pdo->query("SELECT COUNT(*) FROM droit")->fetchColumn();
$recents = $pdo->query("SELECT * FROM etudiant ORDER BY id DESC LIMIT 6")->fetchAll(PDO::FETCH_ASSOC);
include 'includes/header.php';
?>
<div class="grid grid-4">
  <div class="card">
    <div class="stat-label">Étudiants</div>
    <div class="stat-value"><?= $total ?></div>
    <div class="stat-meta">Total enregistrés</div>
  </div>
  <div class="card">
    <div class="stat-label">Novices</div>
    <div class="stat-value"><?= $novices ?></div>
    <div class="stat-meta">Génération 2026</div>
  </div>
 <div class="card">
  <div class="stat-label">Anciens</div>
  <div class="stat-value"><?= $anciens ?></div>
  <div class="stat-meta">Étudiants anciens</div>
</div>
 <div class="card">
  <div class="stat-label">Encaissé</div>
  <div class="stat-value">
    <?= number_format((float)$totalPaye, 0, ',', ' ') ?>
    <small style="font-size:.45em;font-weight:500"> Ar</small>
  </div>
  <div class="stat-meta">
    <?= $nbPaiements ?> paiement<?= $nbPaiements !== 1 ? 's' : '' ?> enregistré<?= $nbPaiements !== 1 ? 's' : '' ?>
  </div>
</div>
</div>

<div style="height:28px"></div>

<div class="card">
  <h2 class="section-title">Derniers étudiants inscrits</h2>
  <?php if (!$recents): ?>
    <div class="empty"><h3>Aucun étudiant pour l'instant</h3><p>Commencez par ajouter votre premier étudiant.</p>
    <a href="etudiants.php?action=new" class="btn btn-primary">+ Ajouter un étudiant</a></div>
  <?php else: ?>
  <div class="table-wrap">
    <table class="table">
      <thead><tr><th>Nom</th><th>Prénom</th><th>Génération</th><th>Logement</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($recents as $r): ?>
        <tr>
          <td><strong><?= e($r['NomEt']) ?></strong></td>
          <td><?= e($r['PrenomEt']) ?></td>
          <td><span class="badge badge-<?= strtolower($r['GenerationEt']) ?>"><?= e($r['GenerationEt']) ?></span></td>
          <td><span class="badge badge-<?= strtolower($r['LogementsEt']) ?>"><?= e($r['LogementsEt']) ?></span></td>
          <td class="actions">
            <a class="btn btn-sm" href="qr.php?id=<?= $r['id'] ?>" target="_blank">QR PDF</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>
<?php include 'includes/footer.php'; ?>
