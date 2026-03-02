<?php
// 1. CONNEXION
$pdo = new PDO("mysql:host=127.0.0.1;dbname=universite;charset=utf8mb4", "root", "");

// 2. REQUÊTES DE COMPTAGE PRÉCISES
// On utilise COUNT(*) pour être sûr de ne rien rater
$totalEtudiants = $pdo->query("SELECT COUNT(*) FROM etudiants")->fetchColumn();
$totalEnseignants = $pdo->query("SELECT COUNT(*) FROM enseignants")->fetchColumn();
$totalUsers = $pdo->query("SELECT COUNT(*) FROM utilisateurs")->fetchColumn();
$totalInscriptions = $pdo->query("SELECT COUNT(*) FROM inscriptions")->fetchColumn();

// 3. FONCTION DE SYNCHRONISATION
function sync_to_mongodb($pdo) {
    if (class_exists('MongoDB\Driver\Manager')) {
        try {
            $manager = new MongoDB\Driver\Manager("mongodb://127.0.0.1:27017");
            $bulk = new MongoDB\Driver\BulkWrite();
            
            // On récupère les 5 enseignants et les étudiants
            $enseignants = $pdo->query("SELECT * FROM enseignants")->fetchAll(PDO::FETCH_ASSOC);
            $etudiants = $pdo->query("SELECT * FROM etudiants")->fetchAll(PDO::FETCH_ASSOC);
            
            $bulk->delete([], ['limit' => 0]); 
            
            foreach ($enseignants as $ens) {
                $ens['type_entite'] = 'enseignant';
                $bulk->insert($ens);
            }
            foreach ($etudiants as $etu) {
                $etu['type_entite'] = 'etudiant';
                $bulk->insert($etu);
            }
            
            $manager->executeBulkWrite('myunivdb.archives_universite', $bulk);
            return (count($enseignants) + count($etudiants));
        } catch (Exception $e) { return "Erreur : " . $e->getMessage(); }
    }
    return "Extension MongoDB absente.";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>MyUnivManager - Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .card-stat { border-radius: 15px; border: none; transition: 0.3s; }
        .card-stat:hover { transform: scale(1.05); }
        .icon-box { font-size: 2.5rem; opacity: 0.3; position: absolute; right: 15px; bottom: 10px; }
    </style>
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <h2 class="fw-bold">Tableau de Bord Académique</h2>
        <span class="badge bg-dark p-2">Session 2026</span>
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card card-stat bg-primary text-white shadow">
                <div class="card-body p-4">
                    <h6 class="text-uppercase small fw-bold">Enseignants</h6>
                    <h2 class="display-4 fw-bold"><?= $totalEnseignants ?></h2>
                    <i class="fas fa-chalkboard-teacher icon-box"></i>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-stat bg-success text-white shadow">
                <div class="card-body p-4">
                    <h6 class="text-uppercase small fw-bold">Étudiants</h6>
                    <h2 class="display-4 fw-bold"><?= $totalEtudiants ?></h2>
                    <i class="fas fa-user-graduate icon-box"></i>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-stat bg-warning text-dark shadow">
                <div class="card-body p-4">
                    <h6 class="text-uppercase small fw-bold">Inscriptions Total</h6>
                    <h2 class="display-4 fw-bold"><?= $totalInscriptions ?></h2>
                    <i class="fas fa-file-invoice icon-box"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-5">
        <div class="col-12">
            <div class="card border-0 shadow-sm p-5 text-center bg-white">
                <h4 class="fw-bold">Passerelle NoSQL</h4>
                <p class="text-muted">Migration vers MongoDB (Base: myunivdb)</p>
                
                <?php if(isset($_GET['sync'])): ?>
                    <?php $count = sync_to_mongodb($pdo); ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <strong>Succès !</strong> <?= $count ?> enregistrements archivés dans MongoDB Compass.
                    </div>
                <?php endif; ?>

                <div class="mt-4">
                    <a href="?sync=1" class="btn btn-primary btn-lg px-5 rounded-pill shadow">
                        <i class="fas fa-sync-alt me-2"></i>Lancer la Synchronisation
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>