<?php
// ============================================================================
// MYUNIVMANAGER - Configuration Production v2.0
// ============================================================================
// Plateforme universitaire robuste avec synchronisation automatique
// ============================================================================

$dbHost = '127.0.0.1';
$dbName = 'universite';
$dbUser = 'root';
$dbPass = '';

try {
    $pdo = new PDO(
        "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4",
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_PERSISTENT => false
        ]
    );
} catch (PDOException $e) {
    if (!headers_sent()) {
        header('HTTP/1.1 500 Internal Server Error', true, 500);
        header('Content-Type: application/json; charset=utf-8');
    }
    die(json_encode(['error' => 'Impossible de se connecter à la base de données']));
}

function allow_cors() {
    if (!headers_sent()) {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
    }
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);
}
allow_cors();

// ============================================================================
// UI HELPERS - Rendu cohérent Bootstrap 5.3 + Animate.css
// ============================================================================

function render_header($title = 'MyUnivManager') {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $role = $_SESSION['user_role'] ?? 'guest';
    $isLoggedIn = isset($_SESSION['user_id']);
    
    ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?> | MyUnivManager</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { 
            --univ-blue: #1a365d; 
            --light-bg: #f4f7fe;
        }
        
        * {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }
        
        body {
            background-color: var(--light-bg);
            color: #2d3748;
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--univ-blue), #2b6cb0) !important;
            box-shadow: 0 4px 15px rgba(26, 54, 93, 0.1);
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.4rem;
            letter-spacing: -0.5px;
        }
        
        .nav-link {
            font-weight: 500;
            transition: all 0.3s ease;
            color: rgba(255, 255, 255, 0.85) !important;
        }
        
        .nav-link:hover {
            color: #fff !important;
            transform: translateY(-2px);
        }
        
        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transition: box-shadow 0.3s ease, transform 0.3s ease;
        }
        
        .card:hover {
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
            transform: translateY(-2px);
        }
    </style>
</head>
<body>

<!-- Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <i class="fas fa-graduation-cap me-2"></i>MyUnivManager
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
    <?php
    if ($isLoggedIn) {
        switch ($role) {
            case 'admin':
                echo <<<MENU
                <li class="nav-item"><a class="nav-link" href="etudiants.php"><i class="fas fa-users me-1"></i>Étudiants</a></li>
                <li class="nav-item"><a class="nav-link" href="enseignants.php"><i class="fas fa-chalkboard-user me-1"></i>Enseignants</a></li>
                <li class="nav-item"><a class="nav-link" href="cours.php"><i class="fas fa-book me-1"></i>Cours</a></li>
                <li class="nav-item"><a class="nav-link" href="inscriptions.php"><i class="fas fa-clipboard-list me-1"></i>Inscriptions</a></li>
MENU;
                break;
            case 'enseignant':
                echo <<<MENU
                <li class="nav-item"><a class="nav-link" href="mes-cours.php"><i class="fas fa-book-open me-1"></i>Mes Cours</a></li>
                <li class="nav-item"><a class="nav-link" href="notes.php"><i class="fas fa-pen-to-square me-1"></i>Saisie Évaluation</a></li>
MENU;
                break;
            case 'etudiant':
                echo <<<MENU
                <li class="nav-item"><a class="nav-link" href="mon-parcours.php"><i class="fas fa-graduation-cap me-1"></i>Mon Parcours</a></li>
                <li class="nav-item"><a class="nav-link" href="mes-notes.php"><i class="fas fa-chart-line me-1"></i>Mes Notes</a></li>
MENU;
                break;
        }
        
        echo <<<LOGOUT
                <li class="nav-item ms-3">
                    <a class="nav-link text-warning" href="logout.php" title="Déconnexion">
                        <i class="fas fa-sign-out-alt me-1"></i><span class="d-none d-md-inline">Quitter</span>
                    </a>
                </li>
LOGOUT;
    } else {
        echo <<<LOGIN
                <li class="nav-item"><a class="nav-link" href="login.php">Se connecter</a></li>
                <li class="nav-item"><a class="nav-link btn btn-light btn-sm text-dark ms-2" href="register.php">S'inscrire</a></li>
LOGIN;
    }
    ?>
            </ul>
        </div>
    </div>
</nav>

<div class="container py-4">
    <?php
}

function render_footer() {
    ?>
</div>

<!-- Footer -->
<footer class="bg-white border-top mt-5 py-4">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <p class="text-muted small mb-0">&copy; 2026 MyUnivManager</p>
            </div>
            <div class="col-md-6 text-end">
                <p class="text-muted small mb-0">
                    contact@myunivmanager.edu<br>
                    +225 27 22 27 54<br>
                    Abidjan, Côte d'Ivoire
                </p>
            </div>
        </div>
    </div>
</footer>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
</body>
</html>
    <?php
}

// ============================================================================
// SYNCHRONISATION MONGODB - POINT CENTRAL NOSQL
// ============================================================================

function sync_to_mongodb($pdo, $silent = true) {
    try {
        // Récupérer données étudiants depuis MySQL
        $stmt = $pdo->query("SELECT * FROM etudiants ORDER BY idEtudiant");
        if (!$stmt) return false;
        
        $etudiants = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Préparer document synchronisation
        $document = [
            'synced_at' => date('c'),
            'sync_timestamp' => time(),
            'total_records' => count($etudiants),
            'data' => $etudiants
        ];
        
        // PERSISTER TOUJOURS DANS UNIV.JSON (backup JSON)
        $json = json_encode($document, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $jsonPath = realpath(__DIR__ . '/../') . DIRECTORY_SEPARATOR . 'Univ.json';
        
        if (!@file_put_contents($jsonPath, $json, LOCK_EX)) {
            if (!$silent) error_log("Erreur: Impossible d'écrire Univ.json à $jsonPath");
            return false;
        }
        
        // MONGODB OPTIONNEL (si extension disponible et serveur accessible)
        if (class_exists('MongoDB\Driver\Manager')) {
            try {
                $manager = new MongoDB\Driver\Manager(
                    'mongodb://127.0.0.1:27017',
                    [],
                    ['connectTimeoutMS' => 2000, 'socketTimeoutMS' => 2000]
                );
                
                // Ping rapide
                $ping = new MongoDB\Driver\Command(['ping' => 1]);
                $status = $manager->executeCommand('admin', $ping);
                
                // Effacer anciens, insérer nouveaux
                $bulk = new MongoDB\Driver\BulkWrite();
                $bulk->delete([], ['limit' => 0]);
                $manager->executeBulkWrite('univ_db.archives_etudiants', $bulk);
                
                $bulk = new MongoDB\Driver\BulkWrite();
                foreach ($etudiants as $student) {
                    $student['_synced_at'] = new MongoDB\BSON\UTCDateTime();
                    $bulk->insert($student);
                }
                $result = $manager->executeBulkWrite('univ_db.archives_etudiants', $bulk);
                
                if (!$silent) error_log("MongoDB Sync OK: " . $result->getInsertedCount() . " documents");
            } catch (Exception $e) {
                // MongoDB down - pas d'erreur fatale, Univ.json a déjà été écrit
                if (!$silent) error_log("MongoDB optionnel: " . $e->getMessage());
            }
        }
        
        return true;
    } catch (Exception $e) {
        if (!$silent) error_log("Erreur sync_to_mongodb: " . $e->getMessage());
        return false;
    }
}

// ============================================================================
// UTILITAIRES SÉCURITÉ
// ============================================================================

function safe_output($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

function valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function check_password_strength($password) {
    if (strlen($password) < 8) return 'Minimum 8 caractères';
    if (!preg_match('/[A-Z]/', $password)) return 'Au moins 1 majuscule';
    if (!preg_match('/[0-9]/', $password)) return 'Au moins 1 chiffre';
    return true;
}

?>
