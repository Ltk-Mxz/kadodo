<?php
require_once '../../../config/database.php';
header('Content-Type: application/json');

try {
    $db = Database::getInstance()->getConnection();

    $query = "SELECT p.id_professeur, u.nom, u.prenom 
              FROM professeur p 
              INNER JOIN utilisateur u ON p.id_utilisateur = u.id_utilisateur 
              ORDER BY u.nom, u.prenom";

    $stmt = $db->prepare($query);
    $stmt->execute();

    echo json_encode([
        'success' => true,
        'professors' => $stmt->fetchAll(PDO::FETCH_ASSOC)
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
