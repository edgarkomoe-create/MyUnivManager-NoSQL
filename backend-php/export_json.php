<?php
require 'config.php';
session_start();

// security: only logged-in users
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

try {
    // Récupération de tous les étudiants avec PDO
    $stmt = $pdo->query("SELECT * FROM etudiants ORDER BY idEtudiant DESC");
    $etudiants = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Encodage JSON compatible MongoDB
    $json_data = json_encode($etudiants, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    // Chemin du fichier à créer
    $file_path = __DIR__ . '/Univ.json';
    file_put_contents($file_path, $json_data);

    render_header('Extraction de Données');
?>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<style>
    body { background-color: #f4f7fe; font-family: 'Inter', sans-serif; }
</style>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm border-0" style="border-radius: 25px;">
                <div class="card-body text-center p-5">
                    <div style="font-size: 4rem; color: #48bb78; margin-bottom: 30px;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h2 class="fw-bold text-dark mb-3">Extraction Réussie</h2>
                    <p class="text-muted mb-4">Les données de <strong><?= count($etudiants) ?> étudiant(s)</strong> ont été exportées au format JSON.</p>
                    <div class="alert alert-info mb-4" style="border-radius: 15px;">
                        <strong>Fichier généré :</strong><br>
                        <code>backend-php/Univ.json</code>
                    </div>
                    <div class="d-grid gap-2 gap-md-3">
                        <a href="Univ.json" download class="btn btn-success" style="border-radius: 12px; font-weight: 600;">
                            <i class="fas fa-download me-2"></i>Télécharger Univ.json
                        </a>
                        <a href="index.php" class="btn btn-outline-secondary" style="border-radius: 12px; font-weight: 600;">
                            <i class="fas fa-home me-2"></i>Retour au tableau de bord
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
    render_footer();
} catch (Exception $e) {
    render_header('Export de Données');
?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm border-0" style="border-radius: 25px;">
                <div class="card-body text-center p-5">
                    <div style="font-size: 4rem; color: #f56565; margin-bottom: 30px;">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <h2 class="fw-bold text-danger mb-3">Erreur d'Exportation</h2>
                    <p class="text-muted mb-4"><?= htmlspecialchars($e->getMessage()) ?></p>
                    <a href="index.php" class="btn btn-danger" style="border-radius: 12px; font-weight: 600;">
                        <i class="fas fa-home me-2"></i>Retour au tableau de bord
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
    render_footer();
}