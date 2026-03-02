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
    $stmt = $pdo->prepare('DELETE FROM enseignants WHERE idEns = ?');
    $stmt->execute([$_GET['id']]);
    if (function_exists('sync_to_mongodb')) { sync_to_mongodb($pdo); }
    header('Location: enseignants.php');
    exit;
}

// insert / update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $numEns = $_POST['numEns'] ?? '';
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $email = $_POST['email'] ?? '';
    $departement = $_POST['departement'] ?? '';
    $grade = $_POST['grade'] ?? '';
    $specialite = $_POST['specialite'] ?? '';

    if ($id) {
        $stmt = $pdo->prepare('UPDATE enseignants SET numEns=?, nom=?, prenom=?, email=?, departement=?, grade=?, specialite=? WHERE idEns=?');
        $stmt->execute([$numEns, $nom, $prenom, $email, $departement, $grade, $specialite, $id]);
    } else {
        $stmt = $pdo->prepare('INSERT INTO enseignants (numEns, nom, prenom, email, departement, grade, specialite) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$numEns, $nom, $prenom, $email, $departement, $grade, $specialite]);
    }
    if (function_exists('sync_to_mongodb')) { sync_to_mongodb($pdo); }
    header('Location: enseignants.php');
    exit;
}

render_header('Enseignants');

// fetch existing teachers
$enseignants = $pdo->query('SELECT * FROM enseignants ORDER BY idEns DESC')->fetchAll();
?>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<style>
    body { background-color: #f4f7fe; font-family: 'Inter', sans-serif; color: #2d3748; }
    .card { border-radius: 20px; }
    .table thead { background: #fff; }
</style>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Gestion des enseignants</h2>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#teacherModal">
            <i class="fas fa-plus"></i> Ajouter
        </button>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Num</th>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Email</th>
                        <th>Département</th>
                        <th>Grade</th>
                        <th>Spécialité</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($enseignants as $e): ?>
                    <tr>
                        <td><?= htmlspecialchars($e['idEns']) ?></td>
                        <td><?= htmlspecialchars($e['numEns']) ?></td>
                        <td><?= htmlspecialchars($e['nom']) ?></td>
                        <td><?= htmlspecialchars($e['prenom']) ?></td>
                        <td><?= htmlspecialchars($e['email']) ?></td>
                        <td><?= htmlspecialchars($e['departement']) ?></td>
                        <td><?= htmlspecialchars($e['grade']) ?></td>
                        <td><?= htmlspecialchars($e['specialite']) ?></td>
                        <td>
                            <button class="btn btn-sm btn-secondary me-1" onclick="editTeacher(<?= $e['idEns'] ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <a href="?action=delete&id=<?= $e['idEns'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer cet enseignant ?');">
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
<div class="modal fade" id="teacherModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="post" id="teacherForm">
        <div class="modal-header">
          <h5 class="modal-title">Enseignant</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <input type="hidden" name="id" id="editId">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Num</label>
                    <input name="numEns" id="numEns" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nom</label>
                    <input name="nom" id="nom" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Prénom</label>
                    <input name="prenom" id="prenom" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input name="email" id="email" type="email" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Département</label>
                    <input name="departement" id="departement" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Grade</label>
                    <input name="grade" id="grade" class="form-control">
                </div>
                <div class="col-12">
                    <label class="form-label">Spécialité</label>
                    <input name="specialite" id="specialite" class="form-control">
                </div>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-success">Enregistrer</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
const teachers = <?= json_encode($enseignants) ?>;
function editTeacher(id) {
    const t = teachers.find(tk => tk.idEns == id);
    if (!t) return;
    document.getElementById('editId').value = t.idEns;
    document.getElementById('numEns').value = t.numEns;
    document.getElementById('nom').value = t.nom;
    document.getElementById('prenom').value = t.prenom;
    document.getElementById('email').value = t.email;
    document.getElementById('departement').value = t.departement;
    document.getElementById('grade').value = t.grade;
    document.getElementById('specialite').value = t.specialite;
    var modal = new bootstrap.Modal(document.getElementById('teacherModal'));
    modal.show();
}
</script>

<?php
render_footer();
?>