<?php
require __DIR__.'/includes/auth.php';
requireAuth($pdo);


$action = $_GET['action'] ?? 'list';
$flash = $_SESSION['flash'] ?? null; unset($_SESSION['flash']);

if ($_SERVER['REQUEST_METHOD']==='POST') {
  $id = $_POST['id'] ?? null;
  $data = [
    trim($_POST['NomEt']),
    trim($_POST['PrenomEt']),
    $_POST['GenerationEt'],
    $_POST['LogementsEt'],
  ];
  if ($id) {
    $st = $pdo->prepare("UPDATE etudiant SET NomEt=?,PrenomEt=?,GenerationEt=?,LogementsEt=? WHERE id=?");
    $st->execute([...$data,$id]);
    $_SESSION['flash']=['ok','Étudiant mis à jour avec succès.'];
  } else {
    $st = $pdo->prepare("INSERT INTO etudiant(NomEt,PrenomEt,GenerationEt,LogementsEt) VALUES(?,?,?,?)");
    $st->execute($data);
    $_SESSION['flash']=['ok','Étudiant ajouté avec succès.'];
  }
  header('Location: etudiants.php'); exit;
}

if ($action==='delete' && !empty($_GET['id'])) {
  $pdo->prepare("DELETE FROM etudiant WHERE id=?")->execute([$_GET['id']]);
  $_SESSION['flash']=['ok','Étudiant supprimé.'];
  header('Location: etudiants.php'); exit;
}

$edit = null;
if ($action==='edit' && !empty($_GET['id'])) {
  $st = $pdo->prepare("SELECT * FROM etudiant WHERE id=?"); $st->execute([$_GET['id']]);
  $edit = $st->fetch(PDO::FETCH_ASSOC);
}

$pageTitle = $action==='new' ? 'Nouvel étudiant' : ($action==='edit' ? 'Modifier étudiant' : 'Étudiants');
$topbarActions = $action==='list'
  ? '<a href="etudiants.php?action=new" class="btn btn-primary">+ Nouvel étudiant</a><a href="qr_all.php" class="btn">Tous les QR (PDF)</a>'
  : '<a href="etudiants.php" class="btn btn-ghost">← Retour</a>';
include 'includes/header.php';

if ($flash) echo '<div class="flash flash-'.e($flash[0]).'">'.e($flash[1]).'</div>';

if ($action==='new' || $action==='edit'):
?>
<div class="card" style="max-width:760px">
  <h2 class="section-title"><?= $edit ? 'Modifier l\'étudiant' : 'Ajouter un étudiant' ?></h2>
  <form method="post">
    <?php if ($edit): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>"><?php endif; ?>
    <div class="form-grid">
      <div><label>Nom</label><input name="NomEt" required value="<?= e($edit['NomEt']??'') ?>"></div>
      <div><label>Prénom</label><input name="PrenomEt" required value="<?= e($edit['PrenomEt']??'') ?>"></div>
      <div>
        <label>Génération</label>
        <select name="GenerationEt">
          <?php foreach (['Novice','Ancien'] as $g): ?>
            <option <?= ($edit['GenerationEt']??'')===$g?'selected':'' ?>><?= $g ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label>Logement</label>
        <select name="LogementsEt">
          <?php foreach (['Interne','Externe'] as $g): ?>
            <option <?= ($edit['LogementsEt']??'')===$g?'selected':'' ?>><?= $g ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="form-actions">
      <a class="btn btn-ghost" href="etudiants.php">Annuler</a>
      <button class="btn btn-primary"><?= $edit?'Enregistrer':'Créer l\'étudiant' ?></button>
    </div>
  </form>
</div>
<?php else:
  $rows = allEtudiantsWithDroit($pdo);
?>
<div class="card">
  <?php if (!$rows): ?>
    <div class="empty"><h3>Aucun étudiant</h3><p>Aucun étudiant n'a encore été enregistré.</p>
      <a href="etudiants.php?action=new" class="btn btn-primary">+ Ajouter le premier</a></div>
  <?php else: ?>
  <div style="margin-bottom:15px;">
    <input
        type="text"
        id="searchInput"
        placeholder="🔍 Rechercher un étudiant..."
        style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;"
    >
</div>

<div class="table-wrap">
    <table class="table" id="etudiantsTable">
      <thead><tr><th>Nom</th><th>Prénom</th><th>Génération</th><th>Logement</th><th>Payé</th><th>Reste</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><strong><?= e($r['NomEt']) ?></strong></td>
          <td><?= e($r['PrenomEt']) ?></td>
          <td><span class="badge badge-<?= strtolower($r['GenerationEt']) ?>"><?= e($r['GenerationEt']) ?></span></td>
          <td><span class="badge badge-<?= strtolower($r['LogementsEt']) ?>"><?= e($r['LogementsEt']) ?></span></td>
          <td><?= number_format($r['MontantPayee']??0,0,',',' ') ?> Ar</td>
          <td><?php $rest=$r['RestePayer']??null; if($rest===null) echo '—'; elseif($rest==0) echo '<span class="badge badge-ok">Soldé</span>'; else echo '<span class="badge badge-due">'.number_format($rest,0,',',' ').' Ar</span>'; ?></td>
          <td class="actions">
            <a class="btn btn-sm" href="qr.php?id=<?= $r['id'] ?>" target="_blank">QR PDF</a>
            <a class="btn btn-sm" href="etudiants.php?action=edit&id=<?= $r['id'] ?>">Modifier</a>
            <a class="btn btn-sm btn-danger" href="etudiants.php?action=delete&id=<?= $r['id'] ?>" onclick="return confirm('Supprimer cet étudiant ?')">Suppr.</a>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

<script>
document.getElementById('searchInput')?.addEventListener('keyup', function () {
    let filtre = this.value.toLowerCase();
    let lignes = document.querySelectorAll('#etudiantsTable tbody tr');

    lignes.forEach(function (ligne) {
        let texte = ligne.textContent.toLowerCase();
        ligne.style.display = texte.includes(filtre) ? '' : 'none';
    });
});
</script>

  <?php endif; ?>
</div>
<?php endif; include 'includes/footer.php'; ?>
