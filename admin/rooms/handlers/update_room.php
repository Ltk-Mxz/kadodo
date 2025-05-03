<?php
require_once '../../../config/database.php';
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Méthode non autorisée');
    }

    if (!isset($_POST['id_salle'])) {
        throw new Exception('ID de la salle manquant');
    }

    $db = Database::getInstance()->getConnection();
    $db->beginTransaction();

    // Vérifier si une autre salle avec le même nom existe déjà
    $checkStmt = $db->prepare("SELECT COUNT(*) FROM salle WHERE LOWER(nom_salle) = LOWER(?) AND id_salle != ?");
    $checkStmt->execute([trim($_POST['nom_salle']), $_POST['id_salle']]);

    if ($checkStmt->fetchColumn() > 0) {
        throw new Exception('Une salle avec ce nom existe déjà');
    }

    $data = [
        'nom_salle' => trim($_POST['nom_salle']),
        'capacite' => intval($_POST['capacite']),
        'equipements' => trim($_POST['equipements']),
        'disponible' => isset($_POST['disponible']) ? 1 : 0
    ];

    $stmt = $db->prepare("
        UPDATE salle 
        SET nom_salle = ?, capacite = ?, equipements = ?, disponible = ? 
        WHERE id_salle = ?
    ");
    $stmt->execute([
        $data['nom_salle'],
        $data['capacite'],
        $data['equipements'],
        $data['disponible'],
        $_POST['id_salle']
    ]);

    $db->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Salle mise à jour avec succès'
    ]);
} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    error_log("Error in update_room.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
