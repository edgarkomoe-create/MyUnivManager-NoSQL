<?php
require 'config.php';
session_start();

// security: only logged-in users
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// handle deletion
if (isset($_GET['action']) && $_GET['action'] === 'delete' && !empty($_GET['id'])) {
    $stmt = $pdo->prepare('DELETE FROM cours WHERE idCours = ?');
    $stmt->execute([$_GET['id']]);
    if (function_exists('sync_to_mongodb')) { sync_to_mongodb($pdo); }
    header('Location: cours.php');
    exit;
}

// insert / update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $codeCours = $_POST['codeCours'] ?? '';
    $intitule = $_POST['intitule'] ?? '';
    $description = $_POST['description'] ?? '';
    $credits = $_POST['credits_ects'] ?? 0;
    $semestre = $_POST['semestre'] ?? 0;
    $niveau = $_POST['niveau'] ?? '';
    $departement = $_POST['departement'] ?? '';
    $prerequis = $_POST['prerequis'] ?? '';
    $idEns_responsable = $_POST['idEns_responsable'] ?? null;

    if ($id) {
        $stmt = $pdo->prepare('UPDATE cours SET codeCours=?, intitule=?, description=?, credits_ects=?, semestre=?, niveau=?, departement=?, prerequis=?, idEns_responsable=? WHERE idCours=?');
        $stmt->execute([$codeCours, $intitule, $description, $credits, $semestre, $niveau, $departement, $prerequis, $idEns_responsable, $id]);
    } else {
        $stmt = $pdo->prepare('INSERT INTO cours (codeCours, intitule, description, credits_ects, semestre, niveau, departement, prerequis, idEns_responsable) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$codeCours, $intitule, $description, $credits, $semestre, $niveau, $departement, $prerequis, $idEns_responsable]);
    }
    if (function_exists('sync_to_mongodb')) { sync_to_mongodb($pdo); }
    header('Location: cours.php');
    exit;
}

render_header('Cours');

// fetch courses with teacher names (JOIN)
$courses = $pdo->query('SELECT c.*, CONCAT(e.nom, " ", e.prenom) AS responsable_nom FROM cours c LEFT JOIN enseignants e ON c.idEns_responsable = e.idEns ORDER BY c.idCours DESC')->fetchAll();

// fetch all teachers for dropdown
$teachers = $pdo->query('SELECT idEns, nom, prenom FROM enseignants ORDER BY nom')->fetchAll();
?>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<style>
    body { background-color: #f4f7fe; font-family: 'Inter', sans-serif; color: #2d3748; }
    .card { border-radius: 20px; }
    .table thead { background: #fff; }
</style>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Catalogue des cours</h2>
        <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#courseModal">
            <i class="fas fa-plus"></i> Ajouter
        </button>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Code</th>
                        <th>Intitulé</th>
                        <th>Crédits</th>
                        <th>Semestre</th>
                        <th>Niveau</th>
                        <th>Responsable</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($courses as $c): ?>
                    <tr>
                        <td><?= htmlspecialchars($c['idCours']) ?></td>
                        <td><strong><?= htmlspecialchars($c['codeCours']) ?></strong></td>
                        <td><?= htmlspecialchars($c['intitule']) ?></td>
                        <td><span class="badge bg-info"><?= htmlspecialchars($c['credits_ects']) ?></span></td>
                        <td><?= htmlspecialchars($c['semestre']) ?></td>
                        <td><?= htmlspecialchars($c['niveau']) ?></td>
                        <td><?= htmlspecialchars($c['responsable_nom'] ?? 'Non assigné') ?></td>
                        <td>
                            <button class="btn btn-sm btn-secondary me-1" onclick="editCourse(<?= $c['idCours'] ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <a href="?action=delete&id=<?= $c['idCours'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ce cours ?');">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- modal form -->
<div class="modal fade" id="courseModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="post" id="courseForm">
        <div class="modal-header">
          <h5 class="modal-title">Cours</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <input type="hidden" name="id" id="editId">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Code Cours</label>
                    <input name="codeCours" id="codeCours" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Intitulé</label>
                    <input name="intitule" id="intitule" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Crédits ECTS</label>
                    <input name="credits_ects" id="credits_ects" type="number" class="form-control" min="1">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Semestre</label>
                    <input name="semestre" id="semestre" type="number" class="form-control" min="1" max="8">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Niveau</label>
                    <input name="niveau" id="niveau" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Département</label>
                    <input name="departement" id="departement" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Prérequis</label>
                    <input name="prerequis" id="prerequis" class="form-control">
                </div>
                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea name="description" id="description" class="form-control" rows="3"></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Responsable du cours</label>
                    <select name="idEns_responsable" id="idEns_responsable" class="form-select">
                        <option value="">-- Aucun --</option>
                        <?php foreach ($teachers as $t): ?>
                        <option value="<?= $t['idEns'] ?>"><?= htmlspecialchars($t['nom'] . ' ' . $t['prenom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-warning">Enregistrer</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
const courses = <?= json_encode($courses) ?>;
function editCourse(id) {
    const c = courses.find(cr => cr.idCours == id);
    if (!c) return;
    document.getElementById('editId').value = c.idCours;
    document.getElementById('codeCours').value = c.codeCours;
    document.getElementById('intitule').value = c.intitule;
    document.getElementById('description').value = c.description;
    document.getElementById('credits_ects').value = c.credits_ects;
    document.getElementById('semestre').value = c.semestre;
    document.getElementById('niveau').value = c.niveau;
    document.getElementById('departement').value = c.departement;
    document.getElementById('prerequis').value = c.prerequis;
    document.getElementById('idEns_responsable').value = c.idEns_responsable || '';
    var modal = new bootstrap.Modal(document.getElementById('courseModal'));
    modal.show();
}
</script>

<?php
render_footer();
?>