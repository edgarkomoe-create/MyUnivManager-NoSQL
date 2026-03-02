<?php
require 'config.php';

try {
    $stmt = $pdo->query('SELECT * FROM etudiants ORDER BY id DESC');
    $students = $stmt->fetchAll();
    header('Content-Type: application/json');
    echo json_encode(['students' => $students]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Query failed', 'detail' => $e->getMessage()]);
}
