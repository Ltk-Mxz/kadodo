<?php
require_once '../../../config/database.php';
header('Content-Type: application/json');

try {
    $db = Database::getInstance()->getConnection();

    $stmt = $db->query("SELECT id_filiere, libelle_filiere FROM filiere ORDER BY libelle_filiere");
    $filieres = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'filieres' => $filieres
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la récupération des filières'
    ]);
}
