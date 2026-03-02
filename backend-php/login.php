<?php
require 'config.php';
session_start();

// If already logged in, go to index
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Demo accounts
    $demoAccounts = [
        'admin@univ.edu' => ['password' => 'admin123', 'role' => 'admin', 'prenom' => 'Admin'],
        'prof.martin@univ.edu' => ['password' => 'test123', 'role' => 'enseignant', 'prenom' => 'Martin'],
        'etudiant.ali@univ.edu' => ['password' => 'test123', 'role' => 'etudiant', 'prenom' => 'Ali']
    ];
    
    if (isset($demoAccounts[$email]) && $demoAccounts[$email]['password'] === $password) {
        $_SESSION['user_id'] = substr(md5($email), 0, 8);
        $_SESSION['user_email'] = $email;
        $_SESSION['user_role'] = $demoAccounts[$email]['role'];
        $_SESSION['user_prenom'] = $demoAccounts[$email]['prenom'];
        header('Location: index.php');
        exit;
    }

    // Database check
    try {
        $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_prenom'] = $user['prenom'];
            header('Location: index.php');
            exit;
        } else {
            $error = "Identifiants invalides. Essayez admin@univ.edu / admin123";
        }
    } catch (PDOException $e) {
        $error = "Erreur système. Veuillez réessayer.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - MyUnivManager</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Inter', sans-serif;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            width: 100%;
            max-width: 420px;
            padding: 0 20px;
        }
        .login-card {
            border: none;
            border-radius: 25px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.15);
            overflow: hidden;
            background: white;
        }
        .login-header {
            background: #3182ce;
            color: white;
            padding: 45px 30px;
            text-align: center;
        }
        .login-header h1 {
            font-size: 1.8rem;
            font-weight: 700;
            margin: 0 0 0.5rem 0;
        }
        .login-header p {
            font-size: 0.9rem;
            opacity: 0.9;
            margin: 0;
        }
        .login-body {
            padding: 40px 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-label {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 8px;
        }
        .form-control {
            border-radius: 12px;
            padding: 12px 15px;
            border: 2px solid #edf2f7;
            background: #f8fafc;
            font-size: 0.95rem;
            transition: all 0.3s;
        }
        .form-control:focus {
            border-color: #3182ce;
            background: white;
            box-shadow: 0 0 0 3px rgba(49, 130, 206, 0.1);
        }
        .btn-login {
            background: #3182ce;
            border: none;
            border-radius: 12px;
            padding: 12px 24px;
            font-weight: 600;
            transition: all 0.3s;
            width: 100%;
            color: white;
        }
        .btn-login:hover {
            background: #2b6cb0;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(49, 130, 206, 0.2);
        }
        .login-footer {
            text-align: center;
            padding: 0 30px 30px;
            color: #718096;
            font-size: 0.85rem;
        }
        .alert {
            border-radius: 12px;
            border: none;
            margin-bottom: 20px;
        }
        .demo-credentials {
            background: #edf2f7;
            border-radius: 12px;
            padding: 15px;
            margin-top: 20px;
            font-size: 0.85rem;
            color: #2d3748;
        }
        .demo-credentials strong {
            color: #3182ce;
        }
    </style>
</head>
<body>
<div class="login-container">
<div class="login-card">
    <div class="login-header">
        <h1><i class="fas fa-graduation-cap me-2"></i>MyUnivManager</h1>
        <p>Système de Gestion Universitaire</p>
    </div>
    <div class="login-body">
        <?php if ($error): ?>
        <div class="alert alert-danger" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>
        <form method="post">
            <div class="form-group">
                <label class="form-label" for="email"><i class="fas fa-envelope me-2 text-primary"></i>Email</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="admin@univ.edu" required autofocus>
            </div>
            <div class="form-group">
                <label class="form-label" for="password"><i class="fas fa-lock me-2 text-primary"></i>Mot de passe</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn btn-login">
                <i class="fas fa-sign-in-alt me-2"></i>Se connecter
            </button>
        </form>
        <div class="demo-credentials">
            <strong><i class="fas fa-info-circle me-2"></i>Accès de Démonstration</strong><br>
            Email: <strong>admin@univ.edu</strong><br>
            Mot de passe: <strong>admin123</strong>
        </div>
        <div class="mt-3 text-center">
            <a href="register.php" class="text-primary">Créer un compte enseignant/étudiant</a>
        </div>
    </div>
    <div class="login-footer">
        © 2026 MyUnivManager — Plateforme Universitaire
    </div>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>