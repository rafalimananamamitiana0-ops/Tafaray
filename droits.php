<?php
require __DIR__.'/includes/auth.php';
requireAuth($pdo);


$action = $_GET['action'] ?? 'list';
$flash = $_SESSION['flash'] ?? null; unset($_SESSION['flash']);

if ($_SERVER['REQUEST_METHOD']==='POST') {

  if (empty($_POST['etudiant_id'])) {
      $_SESSION['flash'] = ['error', 'Veuillez sélectionner un étudiant dans la liste.'];
      header('Location: ' . $_SERVER['HTTP_REFERER']);
      exit;
  }

  $id = $_POST['id'] ?? null;

  $data = [
    (int)$_POST['etudiant_id'],
    (float)$_POST['MontantPayee'],
    (float)$_POST['RestePayer'],
    $_POST['DatePaiement'],
  ];
  if ($id) {
    $st = $pdo->prepare("UPDATE droit SET etudiant_id=?,MontantPayee=?,RestePayer=?,DatePaiement=? WHERE id=?");
    $st->execute([...$data,$id]);
    $_SESSION['flash']=['ok','Droit mis à jour.'];
  } else {
    $st = $pdo->prepare("INSERT INTO droit(etudiant_id,MontantPayee,RestePayer,DatePaiement) VALUES(?,?,?,?)");
    $st->execute($data);
    $_SESSION['flash']=['ok','Paiement enregistré.'];
  }
  header('Location: droits.php'); exit;
}
if ($action==='delete' && !empty($_GET['id'])) {
  $pdo->prepare("DELETE FROM droit WHERE id=?")->execute([$_GET['id']]);
  $_SESSION['flash']=['ok','Droit supprimé.'];
  header('Location: droits.php'); exit;
}

$edit = null;
if ($action==='edit' && !empty($_GET['id'])) {
  $st = $pdo->prepare("SELECT * FROM droit WHERE id=?"); $st->execute([$_GET['id']]);
  $edit = $st->fetch(PDO::FETCH_ASSOC);
}
$etudiants = $pdo->query("SELECT id,NomEt,PrenomEt FROM etudiant ORDER BY NomEt")->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = $action==='new' ? 'Nouveau paiement' : ($action==='edit'?'Modifier paiement':'Droits & Paiements');
$topbarActions = $action==='list'
  ? '<a href="droits.php?action=new" class="btn btn-primary">+ Nouveau paiement</a>'
  : '<a href="droits.php" class="btn btn-ghost">← Retour</a>';
include 'includes/header.php';
if ($flash) echo '<div class="flash flash-'.e($flash[0]).'">'.e($flash[1]).'</div>';

if ($action==='new' || $action==='edit'):
?>
<div class="card" style="max-width:760px">
  <h2 class="section-title"><?= $edit?'Modifier le droit':'Enregistrer un paiement' ?></h2>
  <?php if (!$etudiants): ?>
    <div class="empty"><h3>Aucun étudiant disponible</h3><a href="etudiants.php?action=new" class="btn btn-primary">+ Créer un étudiant</a></div>
  <?php else: ?>
  <form method="post">
    <?php if ($edit): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>"><?php endif; ?>
    <div class="form-grid">
      <div class="full">
        <label>Étudiant</label>

<input
    type="text"
    id="etudiant_search"
    list="liste_etudiants"
    placeholder="Nom Etudiant..."
    required
>

<datalist id="liste_etudiants">
    <?php foreach ($etudiants as $et): ?>
        <option
            data-id="<?= $et['id'] ?>"
            value="<?= e($et['NomEt'].' '.$et['PrenomEt']) ?>">
        </option>
    <?php endforeach; ?>
</datalist>

<input type="hidden" name="etudiant_id" id="etudiant_id" required>


      </div>
      <div><label>Montant payé (Ar)</label><input type="number" step="0.01" name="MontantPayee" required value="<?= e($edit['MontantPayee']??0) ?>"></div>
      <div><label>Reste à payer (Ar)</label><input type="number" step="0.01" name="RestePayer" required value="<?= e($edit['RestePayer']??0) ?>"></div>
      <div class="full"><label>Date de paiement</label><input type="date" name="DatePaiement" required value="<?= e($edit['DatePaiement']??date('Y-m-d')) ?>"></div>
    </div>
    <div class="form-actions">
      <a class="btn btn-ghost" href="droits.php">Annuler</a>
      <button class="btn btn-primary"><?= $edit?'Enregistrer':'Créer le paiement' ?></button>
    </div>
  </form>
  <?php endif; ?>
</div>
<?php else:
  $rows = $pdo->query("SELECT d.*, e.NomEt, e.PrenomEt FROM droit d JOIN etudiant e ON e.id=d.etudiant_id ORDER BY d.DatePaiement DESC, d.id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="card">
  <?php if (!$rows): ?>
    <div class="empty"><h3>Aucun paiement</h3><a href="droits.php?action=new" class="btn btn-primary">+ Premier paiement</a></div>
  <?php else: ?>
  <div style="margin-bottom:15px;">
    <input
        type="text"
        id="searchInput"
        class="form-control"
        placeholder="🔍 Rechercher un étudiant..."
        style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;"
    >
</div>

<div class="table-wrap">
    <table class="table" id="paiementsTable">
      <thead><tr><th>Étudiant</th><th>Montant payé</th><th>Reste</th><th>Date</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><strong><?= e($r['NomEt'].' '.$r['PrenomEt']) ?></strong></td>
          <td><?= number_format($r['MontantPayee'],0,',',' ') ?> Ar</td>
          <td><?= $r['RestePayer']==0 ? '<span class="badge badge-ok">Soldé</span>' : '<span class="badge badge-due">'.number_format($r['RestePayer'],0,',',' ').' Ar</span>' ?></td>
          <td><?= e($r['DatePaiement']) ?></td>
          <td class="actions">
            <a class="btn btn-sm" href="droits.php?action=edit&id=<?= $r['id'] ?>">Modifier</a>
            <a class="btn btn-sm btn-danger" href="droits.php?action=delete&id=<?= $r['id'] ?>" onclick="return confirm('Supprimer ?')">Suppr.</a>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <script>
document.getElementById('searchInput')?.addEventListener('keyup', function() {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll('#paiementsTable tbody tr');

    rows.forEach(function(row) {
        let text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
});

const search = document.getElementById('etudiant_search');
const hidden = document.getElementById('etudiant_id');

search.addEventListener('change', function() {

    let valeur = this.value;
    let options = document.querySelectorAll('#liste_etudiants option');

    hidden.value = '';

    options.forEach(option => {
        if(option.value === valeur){
            hidden.value = option.dataset.id;
        }
    });

});

</script>


  <?php endif; ?>
</div>
<?php endif; include 'includes/footer.php'; ?>
