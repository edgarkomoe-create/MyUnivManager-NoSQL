<?php
// backend-php/accueil.php
// Landing page - L'Université du Futur
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Accueil - MyUnivManager</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
  <style>
    :root{--institutional:#1a365d;--accent-blue:#2b6cb0;--light-bg:#f4f7fe}
    *{font-family:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif}
    body{background:linear-gradient(180deg,var(--light-bg) 0%,#eaf0fb 100%);color:#143048;overflow-x:hidden}
    .hero{background:linear-gradient(135deg,var(--institutional),var(--accent-blue));color:#fff;padding:100px 0;border-radius:24px;overflow:hidden;position:relative}
    .hero::before{content:'';position:absolute;top:-50%;right:-10%;width:600px;height:600px;background:rgba(255,255,255,0.05);border-radius:50%;z-index:0}
    .hero-content{position:relative;z-index:1}
    .hero h1{font-weight:700;font-size:3rem;line-height:1.2}
    .hero p{opacity:0.95;font-size:1.2rem}
    .profile-card{transition:all .3s cubic-bezier(0.34, 1.56, 0.64, 1);border-radius:16px;overflow:hidden;position:relative}
    .profile-card:hover{transform:translateY(-12px);box-shadow:0 30px 60px rgba(26,54,93,0.15)}
    .profile-card::before{content:'';position:absolute;top:0;left:0;right:0;height:4px;background:linear-gradient(90deg,#667eea,#764ba2);z-index:1}
    .interactive-img{transition:all .8s cubic-bezier(0.34, 1.56, 0.64, 1);border-radius:16px;display:block}
    .interactive-img:hover{transform:scale(1.05) rotate(-2deg);filter:brightness(1.1)}
    .card-spot{border-radius:16px;box-shadow:0 20px 50px rgba(20,48,72,0.12);overflow:hidden}
    .profile-card .btn{background:var(--institutional);border:none;border-radius:10px;font-weight:600;transition:all .3s}
    .profile-card .btn:hover{background:var(--accent-blue);transform:translateY(-2px);box-shadow:0 8px 16px rgba(26,54,93,0.2)}
    .icon-circle{width:60px;height:60px;display:flex;align-items:center;justify-content:center;border-radius:14px;font-size:1.8rem;margin-bottom:15px}
    .feature-list li{margin-bottom:10px;color:#555;font-weight:500}
    .feature-list i{color:#667eea;margin-right:8px;font-weight:700}
    nav{background:white!important;box-shadow:0 2px 10px rgba(0,0,0,0.05)!important;padding:15px 0!important}
    nav a{font-weight:600!important;transition:all .3s}
    nav a:hover{color:#667eea!important}
    .cta-btn{background:linear-gradient(135deg,#667eea,#764ba2);border:none;border-radius:12px;padding:12px 32px;font-weight:600;color:white;transition:all .3s}
    .cta-btn:hover{transform:translateY(-4px);box-shadow:0 15px 30px rgba(102,126,234,0.4)}
    .grad-image{max-width:100%;height:auto;object-fit:contain}
    @media (max-width:767px){
      .hero{padding:50px 0}
      .hero h1{font-size:2rem}
      .hero p{font-size:1rem}
      .profile-card{margin-bottom:20px}
    }
  </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light">
  <div class="container">
    <a class="navbar-brand fw-bold" href="#"><i class="fas fa-graduation-cap me-2" style="color:#667eea"></i>MyUnivManager</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"></button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="#apropos">À propos</a></li>
        <li class="nav-item"><a class="nav-link" href="#fonctionnalites">Fonctionnalités</a></li>
        <li class="nav-item"><a class="nav-link" href="login.php" style="color:#667eea;font-weight:700">Connexion</a></li>
        <li class="nav-item"><a class="nav-link ms-2 cta-btn" href="register.php">S'inscrire</a></li>
      </ul>
    </div>
  </div>
</nav>

<main class="container py-5">
  <!-- Hero Section -->
  <section class="hero mb-5 animate__animated animate__fadeInDown">
    <div class="container">
      <div class="row align-items-center hero-content">
        <div class="col-lg-6">
          <h1 class="mb-4">L'Excellence Académique à l'Ère du Numérique</h1>
          <p class="lead mb-4">Une plateforme pensée pour connecter étudiants, enseignants et administrateurs. Donnez à chaque acteur les outils pour réussir dans un monde numérique.</p>
          <div class="d-flex gap-2 flex-wrap">
            <a href="login.php" class="btn btn-light btn-lg rounded-3 px-4 fw-bold">Commencer maintenant →</a>
            <a href="register.php" class="btn btn-outline-light btn-lg rounded-3 px-4 fw-bold">S'inscrire</a>
          </div>
        </div>
        <div class="col-lg-6 d-none d-lg-block">
          <div class="card card-spot p-4 animate__animated animate__fadeInUp">
            <img src="data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgNDRgyIRwhMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjL/wAARCADIAMgDASIAAhEBAxEB/8QAHwAAAQUBAQEBAQEAAAAAAAAAAAECAwQFBgcICQoL/8QAtRAAAgEDAwIEAwUFBAQAAAF9AQIDAAQRBRIhMUEGE1FhByJxFDKBkaEII0KxwRVS0fAkM2JyggkKFhcYGRolJicoKSo0NTY3ODk6Q0RFRkdISUpTVFVWV1hZWmNkZWZnaGlqc3R1dnd4eXqDhIWGh4iJipKTlJWWm5ybnJ2eoqOkpaanqKmqsrO0tba3uLm6wsPExcbHyMnK0tPU1dbX2Nna4uPk5ebn6Onq8vP09fb3+Pn6/8QAHwEAAwEBAQEBAQAAAAAAAAECAwQFBgcICQoL/8QAtREAAgECBAQDBAcFBAQAAQJ3AAECAxEEBSExBhJBUQdhcRMiMoEIFEKRobHBCSMzUvAVYnLRChYkNOEl8RcYGRomJygpKjU2Nzg5OkNERUZHSElKU1RVVldYWVpjZGVmZ2hpanN0dXZ3eHl6goOEhYaHiImKkpOUlbaWmJmaoqOkpaanqKmqsrO0tba3uLm6wsPExcbHyMnK0tPU1dbX2Nna4uPk5ebn6Onq8vP09fb3+Pn6/9oADAMBAAIRAxEAPwD3+iiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigD/9k=" class="img-fluid grad-image animate__animated animate__zoomIn" alt="Graduation" style="max-height:400px">
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Profils Section -->
  <section class="mb-5" id="fonctionnalites">
    <h2 class="text-center fw-bold mb-5"><span style="color:#667eea">Trois Rôles</span>, Une Plateforme</h2>
    <div class="row g-4">
      <div class="col-md-4 animate__animated animate__fadeInLeft" style="animation-delay:0.1s">
        <div class="card profile-card p-5 h-100 border-0 shadow">
          <div class="icon-circle" style="background:linear-gradient(135deg,#667eea,#764ba2);color:white">
            <i class="fas fa-graduation-cap"></i>
          </div>
          <h5 class="fw-bold mb-2">Étudiant — Réussite</h5>
          <p class="text-muted mb-3">Accédez à vos cours, notes et ressources pour exceller académiquement.</p>
          <ul class="feature-list list-unstyled mb-4">
            <li><i class="fas fa-check-circle"></i>Parcours personnalisé</li>
            <li><i class="fas fa-check-circle"></i>Suivi des acquis</li>
            <li><i class="fas fa-check-circle"></i>Accès mobile</li>
          </ul>
          <a href="login.php" class="btn btn-primary w-100 fw-bold">Se connecter</a>
        </div>
      </div>

      <div class="col-md-4 animate__animated animate__fadeInUp" style="animation-delay:0.2s">
        <div class="card profile-card p-5 h-100 border-0 shadow">
          <div class="icon-circle" style="background:linear-gradient(135deg,#f093fb,#f5576c);color:white">
            <i class="fas fa-chalkboard-user"></i>
          </div>
          <h5 class="fw-bold mb-2">Enseignant — Transmission</h5>
          <p class="text-muted mb-3">Créez des cours, évaluez et suivez la progression de vos étudiants.</p>
          <ul class="feature-list list-unstyled mb-4">
            <li><i class="fas fa-check-circle"></i>Interface de gestion</li>
            <li><i class="fas fa-check-circle"></i>Suivi des promotions</li>
            <li><i class="fas fa-check-circle"></i>Outils de notation</li>
          </ul>
          <a href="login.php" class="btn btn-primary w-100 fw-bold">Se connecter</a>
        </div>
      </div>

      <div class="col-md-4 animate__animated animate__fadeInRight" style="animation-delay:0.3s">
        <div class="card profile-card p-5 h-100 border-0 shadow">
          <div class="icon-circle" style="background:linear-gradient(135deg,#4facfe,#00f2fe);color:white">
            <i class="fas fa-chart-pie"></i>
          </div>
          <h5 class="fw-bold mb-2">Admin — Pilotage</h5>
          <p class="text-muted mb-3">Tableaux de bord et export pour piloter l'établissement en temps réel.</p>
          <ul class="feature-list list-unstyled mb-4">
            <li><i class="fas fa-check-circle"></i>Statistiques détaillées</li>
            <li><i class="fas fa-check-circle"></i>Export & archivage</li>
            <li><i class="fas fa-check-circle"></i>Gestion des comptes</li>
          </ul>
          <a href="login.php" class="btn btn-primary w-100 fw-bold">Se connecter</a>
        </div>
      </div>
    </div>
  </section>

  <!-- Avantages Section -->
  <section class="py-5 text-center" id="apropos" style="background:rgba(102,126,234,0.05);border-radius:20px">
    <div class="container">
      <h2 class="fw-bold mb-5">Pourquoi MyUnivManager ?</h2>
      <div class="row g-4">
        <div class="col-md-3 animate__animated animate__fadeInUp">
          <i class="fas fa-lock fa-3x mb-3" style="color:#667eea"></i>
          <h6 class="fw-bold">Sécuriser</h6>
          <p class="small text-muted">Chiffrement de bout en bout et authentification robuste</p>
        </div>
        <div class="col-md-3 animate__animated animate__fadeInUp" style="animation-delay:0.1s">
          <i class="fas fa-bolt fa-3x mb-3" style="color:#f5576c"></i>
          <h6 class="fw-bold">Rapide</h6>
          <p class="small text-muted">Performance optimale grâce à une architecture évolutive</p>
        </div>
        <div class="col-md-3 animate__animated animate__fadeInUp" style="animation-delay:0.2s">
          <i class="fas fa-mobile-alt fa-3x mb-3" style="color:#00f2fe"></i>
          <h6 class="fw-bold">Mobile</h6>
          <p class="small text-muted">Accessible depuis n'importe quel appareil, n'importe quand</p>
        </div>
        <div class="col-md-3 animate__animated animate__fadeInUp" style="animation-delay:0.3s">
          <i class="fas fa-chart-line fa-3x mb-3" style="color:#fbbf24"></i>
          <h6 class="fw-bold">Scalable</h6>
          <p class="small text-muted">Croissez sans limites avec notre infrastructure cloud-ready</p>
        </div>
      </div>
    </div>
  </section>

  <!-- CTA Final -->
  <section class="text-center py-5">
    <h3 class="fw-bold mb-3">Prêt à vous lancer ?</h3>
    <p class="lead text-muted mb-4">Rejoignez des milliers d'établissements qui font confiance à MyUnivManager</p>
    <div class="d-flex gap-2 justify-content-center flex-wrap">
      <a href="register.php" class="btn btn-primary btn-lg rounded-3 px-5 fw-bold">Créer un compte</a>
      <a href="login.php" class="btn btn-outline-primary btn-lg rounded-3 px-5 fw-bold">Se connecter</a>
    </div>
  </section>
</main>

<footer class="bg-white border-top py-5 mt-5">
  <div class="container">
    <div class="row">
      <div class="col-md-4 mb-4">
        <h6 class="fw-bold mb-3"><i class="fas fa-graduation-cap me-2" style="color:#667eea"></i>MyUnivManager</h6>
        <p class="small text-muted">Une plateforme pensée pour l'excellence académique au 21e siècle.</p>
      </div>
      <div class="col-md-4 mb-4">
        <h6 class="fw-bold mb-3">Liens Rapides</h6>
        <ul class="list-unstyled small">
          <li><a href="#" class="text-decoration-none text-muted">À propos</a></li>
          <li><a href="login.php" class="text-decoration-none text-muted">Connexion</a></li>
          <li><a href="register.php" class="text-decoration-none text-muted">Inscription</a></li>
        </ul>
      </div>
      <div class="col-md-4 mb-4">
        <h6 class="fw-bold mb-3">Contact</h6>
        <p class="small text-muted">
          contact@myunivmanager.edu<br>
          +33 (0)1 23 45 67 89<br>
          Paris, France
        </p>
      </div>
    </div>
    <hr class="my-4">
    <div class="row">
      <div class="col-md-6">
        <small class="text-muted">&copy; 2026 MyUnivManager. Tous droits réservés.</small>
      </div>
      <div class="col-md-6 text-end">
        <small class="text-muted">
          <i class="fas fa-server me-1" style="color:#27ae60"></i>
          Infrastructure performante et sécurisée
        </small>
      </div>
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>