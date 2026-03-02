<?php
require 'config.php';
$action = $_GET['action'] ?? '';
if ($action === 'delete' && isset($_GET['idEtudiant']) && isset($_GET['idCours'])) {
    $stmt = $pdo->prepare('DELETE FROM inscriptions WHERE idEtudiant=? AND idCours=?');
    $stmt->execute([$_GET['idEtudiant'],$_GET['idCours']]);
    if (function_exists('sync_to_mongodb')) { sync_to_mongodb($pdo); }
    header('Location: inscriptions.php'); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idet = $_POST['idEtudiant'];
    $idc = $_POST['idCours'];
    $note = $_POST['note_finale'];
    // upsert
    $stmt = $pdo->prepare('REPLACE INTO inscriptions (idEtudiant,idCours,note_finale) VALUES (?,?,?)');
    $stmt->execute([$idet,$idc,$note]);
    if (function_exists('sync_to_mongodb')) { sync_to_mongodb($pdo); }
    header('Location: inscriptions.php'); exit;
}

render_header('Inscriptions');
$list = $pdo->query('SELECT i.*, e.nom AS etu, c.intitule AS cours FROM inscriptions i JOIN etudiants e ON i.idEtudiant=e.idEtudiant JOIN cours c ON i.idCours=c.idCours')->fetchAll();
$students = $pdo->query('SELECT idEtudiant, nom, prenom FROM etudiants')->fetchAll();
$cours = $pdo->query('SELECT idCours, intitule FROM cours')->fetchAll();
?>
<h2>Inscriptions</h2>
<table class="table table-striped">
<thead><tr><th>Étudiant</th><th>Cours</th><th>Note</th><th>Actions</th></tr></thead>
<tbody>
<?php foreach($list as $r): ?>
<tr>
<td><?=htmlspecialchars($r['etu'])?></td>
<td><?=htmlspecialchars($r['cours'])?></td>
<td><?=htmlspecialchars($r['note_finale'])?></td>
<td><a href="?action=delete&idEtudiant=<?=$r['idEtudiant']?>&idCours=<?=$r['idCours']?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer?')">X</a></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<h3>Ajouter / modifier note</h3>
<form method="post" class="row g-2">
<div class="col-md-5">
<select name="idEtudiant" class="form-select" required>
<option value="">Étudiant</option>
<?php foreach($students as $s): ?>
<option value="<?=$s['idEtudiant']?>"><?=htmlspecialchars($s['nom'].' '.$s['prenom'])?></option>
<?php endforeach; ?>
</select>
</div>
<div class="col-md-5">
<select name="idCours" class="form-select" required>
<option value="">Cours</option>
<?php foreach($cours as $c): ?>
<option value="<?=$c['idCours']?>"><?=htmlspecialchars($c['intitule'])?></option>
<?php endforeach; ?>
</select>
</div>
<div class="col-md-2"><input name="note_finale" type="number" step="0.01" class="form-control" placeholder="Note"></div>
<div class="col-12"><button class="btn btn-primary">Enregistrer</button></div>
</form>

<?php render_footer();
?>