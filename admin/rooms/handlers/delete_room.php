<?php
require_once '../../../config/database.php';
header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['id_salle'])) {
        throw new Exception('ID de la salle manquant');
    }

    $db = Database::getInstance()->getConnection();

    $stmt = $db->prepare("DELETE FROM salle WHERE id_salle = ?");
    $stmt->execute([$data['id_salle']]);

    if ($stmt->rowCount() === 0) {
        throw new Exception('Salle non trouvée');
    }

    echo json_encode([
        'success' => true,
        'message' => 'Salle supprimée avec succès'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
