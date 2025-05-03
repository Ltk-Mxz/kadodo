<?php
require_once '../../../config/database.php';
header('Content-Type: application/json');

try {
    $db = Database::getInstance()->getConnection();

    $query = "SELECT c.id_classe, c.nom_classe, f.libelle_filiere, n.libelle_niveau
              FROM classe c
              INNER JOIN filiere f ON c.id_filiere = f.id_filiere
              INNER JOIN niveau n ON c.id_niveau = n.id_niveau
              ORDER BY c.nom_classe";

    $stmt = $db->prepare($query);
    $stmt->execute();

    echo json_encode([
        'success' => true,
        'classes' => $stmt->fetchAll(PDO::FETCH_ASSOC)
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
