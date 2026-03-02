<?php
// backend-php/config.php
$dbHost = '127.0.0.1';
$dbName = 'universite';
$dbUser = 'root';
$dbPass = '';

try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8", $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    if (!headers_sent()) {
        header('Content-Type: application/json', true, 500);
    }
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// CORS helper
function allow_cors() {
    if (!headers_sent()) {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
    }
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;
}
allow_cors();

// --- UI HELPERS AMÉLIORÉS ---
function render_header($title = 'MyUnivManager') {
    if (session_status() === PHP_SESSION_NONE) { session_start(); }
    
    $role = $_SESSION['user_role'] ?? 'guest';
    $isLoggedIn = isset($_SESSION['user_id']);
    
    echo "<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='utf-8'>
    <meta name='viewport' content='width=device-width,initial-scale=1'>
    <title>" . htmlspecialchars($title) . "</title>
    <link href='https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap' rel='stylesheet'>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css'>
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css'/>
    <style>
        :root { --univ-blue: #1a365d; --univ-light: #f4f7fe; }
        * { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
        body { background-color: var(--univ-light); color: #143048; }
        .navbar { background: linear-gradient(135deg, var(--univ-blue), #2b6cb0); box-shadow: 0 4px 12px rgba(26,54,93,0.15); }
        .nav-link { font-weight: 500; transition: all 0.3s ease; color: rgba(255,255,255,0.85) !important; }
        .nav-link:hover { color: #fff !important; transform: translateY(-2px); }
        .card { border: none; border-radius: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.06); transition: all 0.3s ease; }
        .card:hover { transform: translateY(-4px); box-shadow: 0 12px 24px rgba(0,0,0,0.1); }
        .icon-box { display: flex; align-items: center; justify-content: center; background-color: rgba(0,0,0,0.05) !important; border-radius: 12px; }
        .text-muted { color: #718096 !important; }
        .badge { border-radius: 8px; padding: 0.5rem 1rem; font-weight: 600; }
        .btn { border-radius: 10px; font-weight: 600; transition: all 0.3s ease; }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 8px 16px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
<nav class='navbar navbar-expand-lg navbar-dark'>
    <div class='container'>
        <a class='navbar-brand fw-bold' href='accueil.php'><i class='fas fa-graduation-cap me-2'></i>MyUnivManager</a>
        <button class='navbar-toggler' type='button' data-bs-toggle='collapse' data-bs-target='#navbarNav'></button>
        <div class='collapse navbar-collapse' id='navbarNav'>
            <ul class='navbar-nav ms-auto'>";
                
                if($isLoggedIn) {
                    if($role === 'admin') {
                        echo "<li class='nav-item'><a class='nav-link' href='etudiants.php'><i class='fas fa-users me-1'></i>Étudiants</a></li>";
                        echo "<li class='nav-item'><a class='nav-link' href='enseignants.php'><i class='fas fa-chalkboard-user me-1'></i>Enseignants</a></li>";
                        echo "<li class='nav-item'><a class='nav-link' href='cours.php'><i class='fas fa-book me-1'></i>Cours</a></li>";
                    } elseif($role === 'etudiant') {
                        echo "<li class='nav-item'><a class='nav-link' href='index.php'><i class='fas fa-chart-pie me-1'></i>Mon Tableau</a></li>";
                    }
                    echo "<li class='nav-item'><a class='nav-link text-danger' href='logout.php'><i class='fas fa-sign-out-alt me-1'></i>Déconnexion</a></li>";
                } else {
                    echo "<li class='nav-item'><a class='nav-link' href='login.php'><i class='fas fa-sign-in-alt me-1'></i>Connexion</a></li>";
                    echo "<li class='nav-item'><a class='nav-link' href='register.php'><i class='fas fa-user-plus me-1'></i>Inscription</a></li>";
                }

    echo "    </ul>
        </div>
    </div>
</nav>
<div class='container'>
";
}

function render_footer() {
    echo "</div>
    <footer class='bg-white border-top py-5 mt-5'>
        <div class='container'>
            <div class='row'>
                <div class='col-md-4 mb-4'>
                    <h6 class='fw-bold mb-3'><i class='fas fa-graduation-cap me-2 text-primary'></i>MyUnivManager</h6>
                    <p class='small text-muted'>Une plateforme académique robuste et évolutive.</p>
                </div>
                <div class='col-md-4 mb-4'>
                    <h6 class='fw-bold mb-3'>Liens Rapides</h6>
                    <ul class='list-unstyled small text-muted'>
                        <li><a href='accueil.php' class='text-decoration-none text-muted'>Accueil</a></li>
                        <li><a href='login.php' class='text-decoration-none text-muted'>Connexion</a></li>
                        <li><a href='register.php' class='text-decoration-none text-muted'>Inscription</a></li>
                    </ul>
                </div>
                <div class='col-md-4 mb-4'>
                    <h6 class='fw-bold mb-3'>Support</h6>
                    <p class='small text-muted'>contact@myunivmanager.edu<br>+33 (0)1 23 45 67 89</p>
                </div>
            </div>
            <hr class='my-3'>
            <div class='row'>
                <div class='col-md-6'>
                    <small class='text-muted'>&copy; " . date('Y') . " MyUnivManager. Tous droits réservés.</small>
                </div>
                <div class='col-md-6 text-end'>
                    <small class='text-muted'>
                        contact@myunivmanager.edu<br>
                        +225 27 22 27 54 54 <br>
                        Abidjan, Côte d'Ivoire
                    </small>
                </div>
            </div>
        </div>
    </footer>
    <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js'></script>
</body>
</html>";
}

// --- SYNCHRONISATION MONGODB ---
function sync_to_mongodb($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM etudiants");
        $etudiants = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $data = [
            'synced_at' => date('c'),
            'total_records' => count($etudiants),
            'data' => $etudiants
        ];

        // 1. Export JSON tampon
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        file_put_contents(__DIR__ . '/../Univ.json', $json, LOCK_EX);

        // 2. MongoDB (On vérifie si la classe existe pour éviter les erreurs VS Code)
        if (class_exists('MongoDB\Driver\Manager')) {
            try {
                $manager = new \MongoDB\Driver\Manager('mongodb://127.0.0.1:27017', ['connectTimeoutMS' => 2000]);
                $bulk = new \MongoDB\Driver\BulkWrite();
                $bulk->delete([], ['limit' => 0]); 
                
                foreach ($etudiants as $e) {
                    $e['sync_timestamp'] = new \MongoDB\BSON\UTCDateTime();
                    $bulk->insert($e);
                }
                $manager->executeBulkWrite('univ_db.archives_etudiants', $bulk);
            } catch (Exception $e) {
                // Silencieux si Mongo est off
            }
        }
        return true;
    } catch (Exception $e) {
        return false;
    }
}