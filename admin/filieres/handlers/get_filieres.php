<?php
require_once '../../../config/database.php';
header('Content-Type: application/json');

try {
    $db = Database::getInstance()->getConnection();

    $query = "SELECT f.*, 
              (SELECT COUNT(*) FROM etudiant e WHERE e.id_filiere = f.id_filiere) as nombre_etudiants 
              FROM filiere f";

    $stmt = $db->query($query);
    $filieres = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'filieres' => $filieres
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
