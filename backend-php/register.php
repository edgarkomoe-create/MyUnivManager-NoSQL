<?php
require 'config.php';
session_start();

// Si déjà connecté, redirection vers l'index
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

    if ($email === '' || $prenom === '' || $password === '') {
        $error = 'Les champs Prénom, Email et Mot de passe sont obligatoires.';
    } else {
        try {
            // Vérification si l'email existe déjà
            $stmt = $pdo->prepare('SELECT id FROM utilisateurs WHERE email = ?');
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                $error = 'Cet email est déjà utilisé.';
            } else {
                $pdo->beginTransaction();

                // 1. Insertion dans la table utilisateurs (Système)
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare('INSERT INTO utilisateurs (email, password, prenom, role) VALUES (?, ?, ?, ?)');
                $stmt->execute([$email, $hash, $prenom, $role]);

                // 2. Si Étudiant, insertion dans la table etudiants (Métier)
                if ($role === 'etudiant') {
                    $eNom = !empty($nom) ? $nom : $prenom;
                    $ins = $pdo->prepare('INSERT INTO etudiants (numCarte, nom, prenom, email, filiere, annee_entree, date_naissance) VALUES (?, ?, ?, ?, ?, ?, ?)');
                    $ins->execute([$numCarte, $eNom, $prenom, $email, $filiere, $annee_entree, $date_naissance]);
                }

                $pdo->commit();
                
                // Auto-login after registration
                $_SESSION['user_id'] = $pdo->lastInsertId() ?: substr(md5($email), 0, 8);
                $_SESSION['user_email'] = $email;
                $_SESSION['user_role'] = $role;
                $_SESSION['user_prenom'] = $prenom;

                // 3. AUTOMATISATION : On déclenche la sync NoSQL immédiatement
                if (function_exists('sync_to_mongodb')) { 
                    sync_to_mongodb($pdo); 
                }

                $success = 'Votre compte a été créé avec succès ! Redirection en cours...';
                header('Refresh: 2; url=index.php');
            }
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $error = "Erreur technique : " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription | MyUnivManager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background: #f4f7fe; font-family: 'Inter', sans-serif; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .card-register { border: none; border-radius: 25px; box-shadow: 0 15px 35px rgba(0,0,0,0.05); width: 100%; max-width: 500px; background: white; }
        .card-header { background: #1a365d; color: white; border-radius: 25px 25px 0 0 !important; padding: 30px; text-align: center; }
        .form-control, .form-select { border-radius: 12px; padding: 12px; border: 1px solid #e2e8f0; background: #f8fafc; }
        .btn-register { background: #3182ce; border: none; border-radius: 12px; padding: 14px; font-weight: 600; color: white; transition: 0.3s; }
        .btn-register:hover { background: #2b6cb0; transform: translateY(-2px); }
    </style>
</head>
<body>

<div class="card card-register animate__animated animate__fadeIn">
    <div class="card-header">
        <i class="fas fa-user-plus fa-3x mb-3"></i>
        <h2 class="fw-bold mb-0">Inscription</h2>
        <p class="small opacity-75 mb-0">Créez votre accès intelligent</p>
    </div>
    
    <div class="card-body p-4 p-md-5">
        <?php if ($error): ?>
            <div class="alert alert-danger border-0 small"><?= $error ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success border-0 small">
                <?= $success ?> <br>
                <a href="login.php" class="fw-bold text-decoration-none">Se connecter maintenant →</a>
            </div>
        <?php else: ?>

        <form method="POST">
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label small fw-bold text-secondary">Prénom</label>
                    <input type="text" name="prenom" class="form-control" placeholder="Ali" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold text-secondary">Nom</label>
                    <input type="text" name="nom" class="form-control" placeholder="Diop">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-bold text-secondary">Email</label>
                <input type="email" name="email" class="form-control" placeholder="nom@univ.edu" required>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-bold text-secondary">Mot de passe</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>

            <div class="mb-4">
                <label class="form-label small fw-bold text-secondary">Rôle</label>
                <select name="role" id="roleSelect" class="form-select">
                    <option value="etudiant" selected>Étudiant</option>
                    <option value="enseignant">Enseignant</option>
                    <option value="admin">Administrateur</option>
                </select>
            </div>

            <div id="studentFields">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-primary">N° Carte Étudiant</label>
                    <input type="text" name="numCarte" class="form-control" placeholder="ETU2026-001">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-primary">Filière</label>
                    <input type="text" name="filiere" class="form-control" placeholder="Ex: Informatique">
                </div>
            </div>

            <button type="submit" class="btn btn-register w-100 shadow">Créer mon profil</button>
        </form>
        <?php endif; ?>
    </div>
</div>

<script>
    const roleSelect = document.getElementById('roleSelect');
    const studentFields = document.getElementById('studentFields');
    roleSelect.addEventListener('change', () => {
        studentFields.style.display = (roleSelect.value === 'etudiant') ? 'block' : 'none';
    });
</script>

</body>
</html>