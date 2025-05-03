<?php
require_once '../../../config/database.php';
header('Content-Type: application/json');

try {
    if (!isset($_GET['id'])) {
        throw new Exception('ID de la salle manquant');
    }

    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT * FROM salle WHERE id_salle = ?");
    $stmt->execute([$_GET['id']]);

    $room = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$room) {
        throw new Exception('Salle non trouvée');
    }

    echo json_encode([
        'success' => true,
        'room' => $room
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
