<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$id = $_POST['id'] ?? null;
if (!$id) {
    http_response_code(400);
    echo json_encode(['error' => 'id required']);
    exit;
}

$fields = ['num_etudiant','nom','prenom','email','filiere','annee_entree'];
$set = [];
$params = [];
foreach ($fields as $f) {
    if (isset($_POST[$f])) {
        $set[] = "$f = ?";
        $params[] = $_POST[$f];
    }
}
if (empty($set)) {
    echo json_encode(['success' => true]);
    exit;
}
$params[] = $id;
$sql = 'UPDATE etudiants SET ' . implode(', ', $set) . ', updated_at = NOW() WHERE id = ?';

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Update failed', 'detail' => $e->getMessage()]);
}
