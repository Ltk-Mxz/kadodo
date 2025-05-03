<?php
require_once '../../../config/database.php';
header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['id_cours'])) {
        throw new Exception('ID du cours manquant');
    }

    $db = Database::getInstance()->getConnection();

    $stmt = $db->prepare("DELETE FROM cours WHERE id_cours = ?");
    $stmt->execute([$data['id_cours']]);

    if ($stmt->rowCount() === 0) {
        throw new Exception('Cours non trouvé');
    }

    echo json_encode([
        'success' => true,
        'message' => 'Cours supprimé avec succès'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
