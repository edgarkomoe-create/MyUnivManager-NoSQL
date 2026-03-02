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
    $stmt = $pdo->prepare('DELETE FROM etudiants WHERE idEtudiant = ?');
    $stmt->execute([$_GET['id']]);
    // sync NoSQL after deletion
    if (function_exists('sync_to_mongodb')) { sync_to_mongodb($pdo); }
    header('Location: etudiants.php');
    exit;
}

// insert / update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $numCarte = $_POST['numCarte'] ?? '';
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $email = $_POST['email'] ?? '';
    $tel = $_POST['tel'] ?? '';
    $filiere = $_POST['filiere'] ?? '';
    $annee = $_POST['annee_entree'] ?? '';
    $date_naissance = $_POST['date_naissance'] ?? '';

    if ($id) {
        $stmt = $pdo->prepare('UPDATE etudiants SET numCarte=?, nom=?, prenom=?, email=?, tel=?, filiere=?, annee_entree=?, date_naissance=? WHERE idEtudiant=?');
        $stmt->execute([$numCarte, $nom, $prenom, $email, $tel, $filiere, $annee, $date_naissance, $id]);
    } else {
        $stmt = $pdo->prepare('INSERT INTO etudiants (numCarte, nom, prenom, email, tel, filiere, annee_entree, date_naissance) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$numCarte, $nom, $prenom, $email, $tel, $filiere, $annee, $date_naissance]);
    }
    // sync NoSQL after change
    if (function_exists('sync_to_mongodb')) { sync_to_mongodb($pdo); }
    header('Location: etudiants.php');
    exit;
}

render_header('Étudiants');

// fetch existing students
$students = $pdo->query('SELECT * FROM etudiants ORDER BY idEtudiant DESC')->fetchAll();
?>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<style>
    body { background-color: #f4f7fe; font-family: 'Inter', sans-serif; color: #2d3748; }
    .card { border-radius: 20px; }
    .table thead { background: #fff; }
</style>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Gestion des étudiants</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#studentModal">
            <i class="fas fa-plus"></i> Ajouter
        </button>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Num carte</th>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Filière</th>
                        <th>Année</th>
                        <th>Naissance</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $s): ?>
                    <tr>
                        <td><?= htmlspecialchars($s['idEtudiant']) ?></td>
                        <td><?= htmlspecialchars($s['numCarte']) ?></td>
                        <td><?= htmlspecialchars($s['nom']) ?></td>
                        <td><?= htmlspecialchars($s['prenom']) ?></td>
                        <td><?= htmlspecialchars($s['email']) ?></td>
                        <td><?= htmlspecialchars($s['tel']) ?></td>
                        <td><?= htmlspecialchars($s['filiere']) ?></td>
                        <td><?= htmlspecialchars($s['annee_entree']) ?></td>
                        <td><?= htmlspecialchars($s['date_naissance']) ?></td>
                        <td>
                            <button class="btn btn-sm btn-secondary me-1" onclick="editStudent(<?= $s['idEtudiant'] ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <a href="?action=delete&id=<?= $s['idEtudiant'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer cet étudiant ?');">
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
<div class="modal fade" id="studentModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="post" id="studentForm">
        <div class="modal-header">
          <h5 class="modal-title">Étudiant</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <input type="hidden" name="id" id="editId">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Num carte</label>
                    <input name="numCarte" id="numCarte" class="form-control" required>
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
                    <label class="form-label">Téléphone</label>
                    <input name="tel" id="tel" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Filière</label>
                    <select name="filiere" id="filiere" class="form-select" required>
                        <option value="">Choisir...</option>
                        <option value="BDGL">BDGL</option>
                        <option value="SI">SI</option>
                        <option value="RSI">RSI</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Année d'entrée</label>
                    <input name="annee_entree" id="annee_entree" type="number" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Date de naissance</label>
                    <input name="date_naissance" id="date_naissance" type="date" class="form-control">
                </div>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-primary">Enregistrer</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function editStudent(id) {
    // find student row data
    const row = document.querySelector('tr td:first-child:textContent("' + id + '")');
    // simpler: fetch all fields from table cells
    const tr = document.querySelector('tr td:first-child').parentElement; // fix later
}

// better populate using AJAX or by embedding JSON
const students = <?= json_encode($students) ?>;
function editStudent(id) {
    const s = students.find(st => st.idEtudiant == id);
    if (!s) return;
    document.getElementById('editId').value = s.idEtudiant;
    document.getElementById('numCarte').value = s.numCarte;
    document.getElementById('nom').value = s.nom;
    document.getElementById('prenom').value = s.prenom;
    document.getElementById('email').value = s.email;
    document.getElementById('tel').value = s.tel;
    document.getElementById('filiere').value = s.filiere;
    document.getElementById('annee_entree').value = s.annee_entree;
    document.getElementById('date_naissance').value = s.date_naissance;
    var modal = new bootstrap.Modal(document.getElementById('studentModal'));
    modal.show();
}
</script>

<?php
render_footer();
?>