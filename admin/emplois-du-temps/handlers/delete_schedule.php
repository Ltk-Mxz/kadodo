<?php
require_once '../../../config/database.php';
header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!isset($data['id'])) {
        throw new Exception('ID non fourni');
    }

    $db = Database::getInstance()->getConnection();

    $query = "DELETE FROM emploi_du_temps WHERE id_emploi_de_temps = ?";
    $stmt = $db->prepare($query);

    if ($stmt->execute([$data['id']])) {
        echo json_encode(['success' => true, 'message' => 'Emploi du temps supprimé avec succès']);
    } else {
        throw new Exception('Erreur lors de la suppression');
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
