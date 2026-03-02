<?php
require 'config.php';

// validate input
$required = ['num_etudiant','nom','prenom','email','filiere','annee_entree'];
foreach ($required as $field) {
    if (empty($_POST[$field])) {
        http_response_code(400);
        echo json_encode(['error' => "Field $field is required"]);
        exit;
    }
}

// sanitize
$num = $_POST['num_etudiant'];
$nom = $_POST['nom'];
$prenom = $_POST['prenom'];
$email = $_POST['email'];
$filiere = $_POST['filiere'];
$annee = $_POST['annee_entree'];

try {
    $stmt = $pdo->prepare('INSERT INTO etudiants (num_etudiant, nom, prenom, email, filiere, annee_entree, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())');
    $stmt->execute([$num, $nom, $prenom, $email, $filiere, $annee]);

    echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Insert failed', 'detail' => $e->getMessage()]);
}
