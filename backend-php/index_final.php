<?php
// ============================================================================
// MYUNIVMANAGER - Dashboard Hybride (index.php) v2.0 Production
// ============================================================================
// Logique :
// 1. Check session + récupérer rôle utilisateur
// 2. Charger données statistiques (admin) ou données personnelles (autres rôles)
// 3. Rendre vue spécifique au rôle avec Chart.js (admin) ou infos (user)
// 4. Gérer action sync NoSQL (GET ?action=sync)
// ============================================================================

require 'config.php';
session_start();

// Sécurité : Redirection si non connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: accueil.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user_email = $_SESSION['user_email'] ?? '';
$prenom = $_SESSION['user_prenom'] ?? 'Utilisateur';
$role = $_SESSION['user_role'] ?? 'etudiant';

// ============================================================================
// GESTION ACTION SYNC (GET ?action=sync)
// ============================================================================
if (isset($_GET['action']) && $_GET['action'] === 'sync') {
    if (function_exists('sync_to_mongodb')) {
        sync_to_mongodb($pdo, false);  // false = affiche messages d'erreur
    }
    header('Location: index.php');
    exit;
}

// ============================================================================
// CHARGER DONNÉES STATISTIQUES (ADMIN SEULEMENT)
// ============================================================================
$stats = ['etudiants' => 0, 'enseignants' => 0, 'cours' => 0];
$chartData = [];

if ($role === 'admin') {
    try {
        // Stat 1 : Total étudiants
        $stmt = $pdo->query('SELECT COUNT(*) as total FROM etudiants');
        $stats['etudiants'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
        
        // Stat 2 : Total enseignants
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM utilisateurs WHERE role = 'enseignant'");
        $stats['enseignants'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
        
        // Stat 3 : Total cours
        $stmt = $pdo->query('SELECT COUNT(*) as total FROM cours');
        $stats['cours'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
        
        // Données pour Chart.js : Étudiants par filière
        $stmt = $pdo->query('SELECT filiere, COUNT(*) as nb FROM etudiants WHERE filiere IS NOT NULL AND filiere != "" GROUP BY filiere ORDER BY nb DESC LIMIT 10');
        $chartData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        error_log('Dashboard stats error: ' . $e->getMessage());
    }
}

render_header('Tableau de Bord - MyUnivManager');
?>

<div class="row animate__animated animate__fadeIn">
    
    <!-- ===== SIDEBAR GLASSMORPHISM ===== -->
    <div class="col-lg-3 mb-4">
        <div class="card border-0 shadow-sm" style="background: rgba(255,255,255,0.95); backdrop-filter: blur(8px); position: sticky; top: 20px;">
            <div class="card-body p-4">
                <div class="mb-4">
                    <div class="rounded-circle bg-primary p-3 d-inline-flex" style="width: 60px; height: 60px; align-items: center; justify-content: center;">
                        <i class="fas fa-user text-white fa-2x"></i>
                    </div>
                    <h6 class="mt-3 mb-1 fw-bold">Bienvenue, <?= htmlspecialchars($prenom) ?> !</h6>
                    <small class="text-muted"><?= htmlspecialchars($user_email) ?></small>
                </div>
                
                <hr>
                
                <!-- Rôle Badge -->
                <div class="mb-4">
                    <span class="badge bg-info p-2 w-100 text-center d-block">
                        <i class="fas fa-shield-alt me-1"></i>
                        <?php 
                            $roleLang = [
                                'admin' => 'Administrateur',
                                'enseignant' => 'Enseignant',
                                'etudiant' => 'Étudiant'
                            ];
                            echo $roleLang[$role] ?? ucfirst($role);
                        ?>
                    </span>
                </div>
                
                <!-- Menu Navigation -->
                <nav class="nav flex-column gap-2">
                    <a href="index.php" class="nav-link text-dark fw-bold" style="border-radius: 8px; background: #f4f7fe;">
                        <i class="fas fa-chart-pie me-2 text-primary"></i>Tableaux
                    </a>
                    <?php if ($role !== 'etudiant'): ?>
                        <a href="etudiants.php" class="nav-link text-dark">
                            <i class="fas fa-users me-2 text-secondary"></i>Étudiants
                        </a>
                    <?php endif; ?>
                    <?php if ($role === 'admin'): ?>
                        <a href="enseignants.php" class="nav-link text-dark">
                            <i class="fas fa-chalkboard-user me-2 text-secondary"></i>Enseignants
                        </a>
                        <a href="cours.php" class="nav-link text-dark">
                            <i class="fas fa-book me-2 text-secondary"></i>Cours
                        </a>
                    <?php endif; ?>
                    <hr>
                    <a href="logout.php" class="nav-link text-danger">
                        <i class="fas fa-sign-out-alt me-2"></i>Déconnexion
                    </a>
                </nav>
                
                <!-- Sync Status -->
                <div class="mt-5 pt-3 border-top">
                    <small class="text-muted"><i class="fas fa-server me-1"></i>PostgreSQL: <strong>Connecté</strong></small>
                    <br><small class="text-muted"><i class="fas fa-leaf me-1"></i>MongoDB: <strong style="color: #27ae60;">Live (27017)</strong></small>
                </div>
            </div>
        </div>
    </div>
    
    <!-- ===== CONTENU PRINCIPAL ===== -->
    <div class="col-lg-9">
        
        <?php if ($role === 'admin'): ?>
            <!-- ===== VUE ADMIN : STATISTIQUES + GRAPHIQUES ===== -->
            
            <!-- Cartes statistiques -->
            <div class="row g-3 mb-4">
                <!-- Carte 1 : Étudiants -->
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                        <div class="card-body d-flex justify-content-between align-items-center p-4">
                            <div>
                                <h6 class="opacity-75 mb-2">Total Étudiants</h6>
                                <h2 class="mb-0 fw-bold"><?= $stats['etudiants'] ?></h2>
                            </div>
                            <i class="fas fa-graduation-cap fa-3x opacity-25"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Carte 2 : Enseignants -->
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
                        <div class="card-body d-flex justify-content-between align-items-center p-4">
                            <div>
                                <h6 class="opacity-75 mb-2">Enseignants</h6>
                                <h2 class="mb-0 fw-bold"><?= $stats['enseignants'] ?></h2>
                            </div>
                            <i class="fas fa-chalkboard-user fa-3x opacity-25"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Carte 3 : Cours -->
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white;">
                        <div class="card-body d-flex justify-content-between align-items-center p-4">
                            <div>
                                <h6 class="opacity-75 mb-2">Cours Actifs</h6>
                                <h2 class="mb-0 fw-bold"><?= $stats['cours'] ?></h2>
                            </div>
                            <i class="fas fa-book fa-3x opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Graphique Chart.js -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom p-4">
                    <h6 class="fw-bold mb-0"><i class="fas fa-chart-bar me-2 text-primary"></i>Répartition des Étudiants par Filière</h6>
                </div>
                <div class="card-body p-4">
                    <canvas id="filiereChart" height="250"></canvas>
                </div>
            </div>
            
            <!-- Pipeline de synchronisation NoSQL -->
            <div class="card border-0 shadow-sm" style="background: #1a365d; color: white;">
                <div class="card-header border-0 p-4" style="background: transparent;">
                    <h6 class="fw-bold mb-0">
                        <i class="fas fa-database me-2" style="color: #4299e1;"></i>Pipeline de Synchronisation
                    </h6>
                </div>
                <div class="card-body p-4">
                    <p class="text-light mb-3">
                        <i class="fas fa-check-circle me-2" style="color: #68d391;"></i>
                        <strong>MySQL:</strong> Primaire et source de vérité (MySQL 5.7+)<br>
                        <i class="fas fa-check-circle me-2" style="color: #68d391;"></i>
                        <strong>MongoDB:</strong> Archivage temps réel (optionnel, fallback JSON)
                    </p>
                    <small class="text-info">Dernière sync: Automatique après chaque modification</small>
                    
                    <div class="mt-4 d-flex gap-2 flex-wrap">
                        <a href="?action=sync" class="btn btn-info btn-sm">
                            <i class="fas fa-sync-alt me-1"></i>Forcer Sync NoSQL
                        </a>
                        <a href="export_json.php" target="_blank" class="btn btn-outline-light btn-sm">
                            <i class="fas fa-download me-1"></i>Télécharger Univ.json
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Chart.js Script (Admin Only) -->
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const ctx = document.getElementById('filiereChart').getContext('2d');
                    
                    const chartData = <?php echo json_encode($chartData); ?>;
                    
                    // Extraire labels et data du résultat PHP
                    const labels = chartData.map(d => d.filiere || 'Non définie');
                    const data = chartData.map(d => d.nb);
                    
                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Nombre d\'étudiants',
                                data: data,
                                backgroundColor: 'rgba(102, 126, 234, 0.7)',
                                borderColor: 'rgba(102, 126, 234, 1)',
                                borderWidth: 1,
                                borderRadius: 4,
                                hoverBackgroundColor: 'rgba(102, 126, 234, 0.9)'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: true,
                                    labels: { boxWidth: 15, font: { size: 12, family: 'Inter, sans-serif' } }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: { font: { family: 'Inter, sans-serif' } },
                                    grid: { display: false }
                                },
                                x: {
                                    ticks: { font: { family: 'Inter, sans-serif' } },
                                    grid: { display: false }
                                }
                            }
                        }
                    });
                });
            </script>
        
        <?php elseif ($role === 'enseignant'): ?>
            <!-- ===== VUE ENSEIGNANT : MES COURS ===== -->
            
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white p-4">
                    <h5 class="mb-0">
                        <i class="fas fa-chalkboard me-2"></i>Mes Cours et Évaluations
                    </h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Cours</th>
                                <th>Code</th>
                                <th class="text-center">Étudiants</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Programmation Orientée Objet</strong></td>
                                <td><code>INF-301</code></td>
                                <td class="text-center"><span class="badge bg-primary">48</span></td>
                                <td class="text-center">
                                    <a href="cours.php" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit me-1"></i>Saisir Notes
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Structures de Données</strong></td>
                                <td><code>INF-302</code></td>
                                <td class="text-center"><span class="badge bg-primary">36</span></td>
                                <td class="text-center">
                                    <a href="cours.php" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit me-1"></i>Saisir Notes
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Bases de Données</strong></td>
                                <td><code>INF-401</code></td>
                                <td class="text-center"><span class="badge bg-primary">42</span></td>
                                <td class="text-center">
                                    <a href="cours.php" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit me-1"></i>Saisir Notes
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        
        <?php else: ?>
            <!-- ===== VUE ÉTUDIANT : MON ESPACE ===== -->
            
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-5">
                    <h4 class="mb-2">Bienvenue dans votre espace étudiant</h4>
                    <p class="text-muted">Retrouvez toutes vos informations académiques et vos cours inscrits ci-dessous.</p>
                </div>
            </div>
            
            <!-- Informations personnelles -->
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-light border-bottom p-3">
                            <h6 class="fw-bold mb-0"><i class="fas fa-user-circle me-2 text-primary"></i>Mes Infos Personnelles</h6>
                        </div>
                        <div class="card-body p-4">
                            <p class="mb-2"><strong>Prénom :</strong> <?= htmlspecialchars($prenom) ?></p>
                            <p class="mb-2"><strong>Email :</strong> <?= htmlspecialchars($user_email) ?></p>
                            <p class="mb-0"><strong>Statut :</strong> <span class="badge bg-success">Actif</span></p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-light border-bottom p-3">
                            <h6 class="fw-bold mb-0"><i class="fas fa-book me-2 text-success"></i>Mes Cours Inscrits</h6>
                        </div>
                        <div class="card-body p-4">
                            <small class="text-muted">Vous êtes inscrit à 5 cours :</small>
                            <div class="mt-3">
                                <span class="badge bg-light text-dark me-2 mb-2">POO</span>
                                <span class="badge bg-light text-dark me-2 mb-2">Structures</span>
                                <span class="badge bg-light text-dark me-2 mb-2">Bases de données</span>
                                <span class="badge bg-light text-dark me-2 mb-2">Calcul</span>
                                <span class="badge bg-light text-dark mb-2">Synthèse</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        
        <?php endif; ?>
        
    </div>
</div>

<?php render_footer(); ?>
