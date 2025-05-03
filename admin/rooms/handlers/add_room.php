<?php
require_once '../../../config/database.php';
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Méthode non autorisée');
    }

    $db = Database::getInstance()->getConnection();
    $db->beginTransaction();

    // Vérifier si une salle avec le même nom existe déjà
    $checkStmt = $db->prepare("SELECT COUNT(*) FROM salle WHERE LOWER(nom_salle) = LOWER(?)");
    $checkStmt->execute([trim($_POST['nom_salle'])]);

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
        INSERT INTO salle (nom_salle, capacite, equipements, disponible) 
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([
        $data['nom_salle'],
        $data['capacite'],
        $data['equipements'],
        $data['disponible']
    ]);

    $db->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Salle ajoutée avec succès'
    ]);
} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    error_log("Error in add_room.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
