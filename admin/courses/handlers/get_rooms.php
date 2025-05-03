<?php
require_once '../../../config/database.php';
header('Content-Type: application/json');

try {
    $db = Database::getInstance()->getConnection();

    // Requête plus détaillée pour debug
    $query = "SELECT id_salle, nom_salle, capacite FROM salle WHERE disponible = 1 ORDER BY nom_salle";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Debug: Afficher les salles dans les logs
    error_log(print_r($rooms, true));

    echo json_encode([
        'success' => true,
        'rooms' => $rooms
    ]);
} catch (Exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
