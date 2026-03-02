<?php
require 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$prenom = $_SESSION['user_prenom'] ?? 'Utilisateur';
$role = $_SESSION['user_role'] ?? 'etudiant';

// --- LOGIQUE DYNAMIQUE SELON LE RÔLE ---
$stats = [];

if ($role === 'admin') {
    // L'Admin voit tout l'établissement
    $stats['total_etudiants'] = $pdo->query("SELECT COUNT(*) FROM etudiants")->fetchColumn();
    $stats['total_profs'] = $pdo->query("SELECT COUNT(*) FROM utilisateurs WHERE role = 'enseignant'")->fetchColumn();
    $stats['total_ue'] = $pdo->query("SELECT COUNT(*) FROM cours")->fetchColumn();
    
} elseif ($role === 'enseignant') {
    // L'Enseignant voit ses propres attributions
    // 1. Nombre d'UE (Cours) qu'il donne
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM cours WHERE enseignant_id = ?");
    $stmt->execute([$user_id]);
    $stats['mes_ue'] = $stmt->fetchColumn();

    // 2. Nombre de promotions (filières) différentes qu'il touche
    $stmt = $pdo->prepare("SELECT COUNT(DISTINCT filiere) FROM cours WHERE enseignant_id = ?");
    $stmt->execute([$user_id]);
    $stats['mes_promotions'] = $stmt->fetchColumn();

    // 3. Taux de remplissage des notes pour ses cours
    $stmt = $pdo->prepare("SELECT 
        (SELECT COUNT(*) FROM notes WHERE cours_id IN (SELECT id FROM cours WHERE enseignant_id = ?)) / 
        (SELECT COUNT(*) FROM etudiants e JOIN cours c ON e.filiere = c.filiere WHERE c.enseignant_id = ?) * 100");
    $stmt->execute([$user_id, $user_id]);
    $stats['taux_notes'] = round($stmt->fetchColumn() ?? 0);

} elseif ($role === 'etudiant') {
    // L'Étudiant voit son propre parcours
    // 1. Sa moyenne générale actuelle
    $stmt = $pdo->prepare("SELECT AVG(note_valeur) FROM notes n 
                           JOIN etudiants e ON n.etudiant_id = e.id 
                           WHERE e.email = ?"); // On suppose que l'email lie l'utilisateur à l'étudiant
    $stmt->execute([$_SESSION['user_email']]);
    $stats['ma_moyenne'] = round($stmt->fetchColumn(), 2) ?: '--';

    // 2. Nombre de crédits validés (notes >= 10)
    $stmt = $pdo->prepare("SELECT SUM(c.credit_ects) FROM notes n 
                           JOIN cours c ON n.cours_id = c.id 
                           JOIN etudiants e ON n.etudiant_id = e.id
                           WHERE e.email = ? AND n.note_valeur >= 10");
    $stmt->execute([$_SESSION['user_email']]);
    $stats['credits'] = $stmt->fetchColumn() ?: 0;
}

render_header('ENT - MyUnivManager');
?>

<div class="row g-4">
    <div class="col-lg-2 d-none d-lg-block">
        <div class="sidebar-sticky shadow-sm p-3 bg-white" style="border-radius: 20px;">
            <nav class="nav flex-column gap-1">
                <a href="index.php" class="nav-link-custom active"><i class="fas fa-home me-2"></i> Accueil</a>
                <a href="planning.php" class="nav-link-custom"><i class="fas fa-calendar me-2"></i> Planning</a>
                <a href="notes.php" class="nav-link-custom"><i class="fas fa-graduation-cap me-2"></i> Notes</a>
                <hr>
                <a href="logout.php" class="nav-link-custom text-danger"><i class="fas fa-power-off me-2"></i> Quitter</a>
            </nav>
        </div>
    </div>

    <div class="col-lg-10">
        <div class="card border-0 shadow-sm mb-4 p-4" style="border-radius: 20px;">
            <h3 class="fw-bold mb-1">Bonjour, <?= htmlspecialchars($prenom) ?> 👋</h3>
            <p class="text-muted">Tableau de bord - <?= ucfirst($role) ?> • <?= date('d/m/Y') ?></p>
        </div>

        <?php if ($role === 'admin'): ?>
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="card shadow-sm border-0 p-4 text-white bg-primary">
                        <h6>Étudiants Inscrits</h6>
                        <h2><?= $stats['total_etudiants'] ?></h2>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm border-0 p-4 text-white bg-success">
                        <h6>Corps Enseignant</h6>
                        <h2><?= $stats['total_profs'] ?></h2>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm border-0 p-4 text-white bg-dark">
                        <h6>Unités d'Enseignement</h6>
                        <h2><?= $stats['total_ue'] ?></h2>
                    </div>
                </div>
            </div>

        <?php elseif ($role === 'enseignant'): ?>
            <div class="card border-0 shadow-sm overflow-hidden mb-4" style="border-radius: 20px;">
                <div class="p-4 text-white bg-success d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="fw-bold mb-0">Espace Transmission</h4>
                        <small>Gérez vos cours et évaluations en temps réel.</small>
                    </div>
                    <i class="fas fa-chalkboard-teacher fa-2x opacity-50"></i>
                </div>
                <div class="p-4 bg-white">
                    <div class="row text-center">
                        <div class="col-4 border-end">
                            <h4 class="fw-bold text-success"><?= $stats['mes_promotions'] ?></h4>
                            <small class="text-muted">Promotions</small>
                        </div>
                        <div class="col-4 border-end">
                            <h4 class="fw-bold text-success"><?= $stats['mes_ue'] ?></h4>
                            <small class="text-muted">Unités (UE)</small>
                        </div>
                        <div class="col-4">
                            <h4 class="fw-bold text-success"><?= $stats['taux_notes'] ?>%</h4>
                            <small class="text-muted">Notes Saisies</small>
                        </div>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <div class="row g-4">
                <div class="col-md-8">
                    <div class="card border-0 shadow-sm p-4 bg-info text-white" style="border-radius: 20px;">
                        <h4 class="fw-bold">Ma Progression</h4>
                        <p>Vous avez validé <strong><?= $stats['credits'] ?> crédits ECTS</strong> ce semestre.</p>
                        <a href="releve_notes.php" class="btn btn-light rounded-pill btn-sm">Voir mon relevé</a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm p-4 text-center bg-white" style="border-radius: 20px;">
                        <small class="text-muted d-block">Moyenne Actuelle</small>
                        <h1 class="fw-bold text-primary"><?= $stats['ma_moyenne'] ?></h1>
                        <span class="badge bg-primary-subtle text-primary rounded-pill">Sur 20</span>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php render_footer(); ?>