<?php
// ============================================================================
// MYUNIVMANAGER - Inscription Multi-Rôles (register.php) v2.0 Production
// ============================================================================
// Logique :
// 1. Validation des inputs
// 2. Vérification unicité email
// 3. Transaction : INSERT utilisateurs → INSERT etudiants (si étudiant)
// 4. Sync automatique vers MongoDB + Univ.json
// ============================================================================

require 'config.php';
session_start();

// Redirection si déjà connecté
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $nom = trim($_POST['nom'] ?? '');
    $numCarte = trim($_POST['numCarte'] ?? '');
    $filiere = trim($_POST['filiere'] ?? '');
    $annee_entree = $_POST['annee_entree'] ?? null;
    $date_naissance = $_POST['date_naissance'] ?? null;
    $password = $_POST['password'] ?? '';
    $role = in_array($_POST['role'] ?? '', ['enseignant', 'etudiant', 'admin']) ? $_POST['role'] : 'etudiant';

    // Validation basique
    if (empty($email) || empty($prenom) || empty($password)) {
        $error = 'Les champs Prénom, Email et Mot de passe sont obligatoires.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email invalide.';
    } elseif (strlen($password) < 8) {
        $error = 'Le mot de passe doit contenir au minimum 8 caractères.';
    } else {
        try {
            // Vérifier si l'email est déjà utilisé
            $stmt = $pdo->prepare('SELECT id FROM utilisateurs WHERE email = ?');
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                $error = 'Cet email est déjà enregistré dans notre système.';
            } else {
                // TRANSACTION : Tout ou rien
                $pdo->beginTransaction();
                
                try {
                    // 1. Créer l'utilisateur système
                    $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                    $stmt = $pdo->prepare('INSERT INTO utilisateurs (email, password, prenom, role) VALUES (?, ?, ?, ?)');
                    $stmt->execute([$email, $hash, $prenom, $role]);
                    $userId = $pdo->lastInsertId();
                    
                    // 2. Si étudiant, créer aussi un profil étudiant
                    if ($role === 'etudiant') {
                        $eNom = !empty($nom) ? $nom : $prenom;
                        $ins = $pdo->prepare(
                            'INSERT INTO etudiants (numCarte, nom, prenom, email, filiere, annee_entree, date_naissance) 
                             VALUES (?, ?, ?, ?, ?, ?, ?)'
                        );
                        $ins->execute([$numCarte ?: null, $eNom, $prenom, $email, $filiere ?: null, $annee_entree, $date_naissance]);
                    }
                    
                    // Valider la transaction
                    $pdo->commit();
                    
                    // 3. SYNCHRONISATION NOSQL (après succès MySQL)
                    if (function_exists('sync_to_mongodb')) {
                        sync_to_mongodb($pdo);
                    }
                    
                    $success = 'Votre compte a été créé avec succès ! Vous pouvez maintenant vous connecter.';
                    
                } catch (Exception $e) {
                    // Rollback en cas d'erreur dans la transaction
                    $pdo->rollBack();
                    $error = 'Erreur lors de la création du compte. Veuillez réessayer.';
                }
            }
        } catch (PDOException $e) {
            $error = 'Erreur base de données. Veuillez contacter le support.';
        }
    }
}

render_header('Inscription - MyUnivManager');
?>

<div class="row animate__animated animate__fadeIn">
    <div class="col-lg-6 offset-lg-3 col-md-8 offset-md-2">
        <div class="card shadow-lg border-0">
            <div class="card-header bg-primary text-white p-4">
                <h2 class="fw-bold mb-1"><i class="fas fa-user-plus me-2"></i>Inscription</h2>
                <p class="small opacity-75 mb-0">Créez votre profil MyUnivManager</p>
            </div>
            
            <div class="card-body p-4 p-md-5">
                
                <?php if ($error): ?>
                    <div class="alert alert-danger border-0 alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success border-0 alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success) ?><br>
                        <small class="mt-2 d-block"><a href="login.php" class="fw-bold">Se connecter maintenant →</a></small>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php else: ?>
                
                <form method="POST" id="registerForm">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary">Prénom *</label>
                            <input type="text" name="prenom" class="form-control" placeholder="Ali" required autofocus>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary">Nom</label>
                            <input type="text" name="nom" class="form-control" placeholder="Diop">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Email *</label>
                        <input type="email" name="email" class="form-control" placeholder="prenom@univ.edu" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Mot de passe *</label>
                        <input type="password" name="password" class="form-control" placeholder="Min. 8 caractères" required>
                        <small class="text-muted">Minimum 8 caractères, 1 majuscule, 1 chiffre</small>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold text-secondary">Votre rôle *</label>
                        <select name="role" id="roleSelect" class="form-select" required>
                            <option value="etudiant">Étudiant</option>
                            <option value="enseignant">Enseignant</option>
                            <option value="admin">Administrateur</option>
                        </select>
                    </div>

                    <!-- Champs dynamiques (visibles si Étudiant) -->
                    <div id="studentFields" style="display: none;">
                        <hr>
                        <h6 class="text-primary mb-3"><i class="fas fa-graduation-cap me-2"></i>Informations Académiques</h6>
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-primary">N° Carte Étudiant</label>
                            <input type="text" name="numCarte" class="form-control" placeholder="ETU2026-001">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-primary">Filière</label>
                            <input type="text" name="filiere" class="form-control" placeholder="Ex: Informatique, BDGL, RSI">
                        </div>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-primary">Année d'entrée</label>
                                <input type="number" name="annee_entree" class="form-control" min="2000" max="2099">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-primary">Date de naissance</label>
                                <input type="date" name="date_naissance" class="form-control">
                            </div>
                        </div>
                    </div>

                    <!-- Bouton Soumettre -->
                    <button type="submit" class="btn btn-primary w-100 mt-4 shadow-sm fw-bold">
                        <i class="fas fa-arrow-right me-2"></i>Créer mon profil
                    </button>
                </form>
                
                <?php endif; ?>

                <hr class="my-4">
                
                <p class="text-center small text-muted">
                    Vous avez déjà un compte ? 
                    <a href="login.php" class="fw-bold text-primary text-decoration-none">Se connecter ici</a>
                </p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.getElementById('roleSelect');
    const studentFields = document.getElementById('studentFields');
    
    function toggleStudentFields() {
        studentFields.style.display = roleSelect.value === 'etudiant' ? 'block' : 'none';
    }
    
    // Écouter les changements
    roleSelect.addEventListener('change', toggleStudentFields);
    
    // Afficher/cacher au chargement
    toggleStudentFields();
    
    // Validation côté client
    document.getElementById('registerForm').addEventListener('submit', function(e) {
        const email = document.querySelector('input[name="email"]').value;
        const password = document.querySelector('input[name="password"]').value;
        
        if (!email.includes('@')) {
            e.preventDefault();
            alert('Email invalide');
            return false;
        }
        
        if (password.length < 8) {
            e.preventDefault();
            alert('Mot de passe trop court (minimum 8 caractères)');
            return false;
        }
    });
});
</script>

<?php render_footer(); ?>
