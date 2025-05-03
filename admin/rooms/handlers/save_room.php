<?php
require_once '../../../config/database.php';
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Méthode non autorisée');
    }

    $db = Database::getInstance()->getConnection();
    $db->beginTransaction();

    // Nettoyer le nom de la salle
    $nom_salle = trim($_POST['nom_salle']);

    // Vérifier si une salle avec le même nom existe déjà (en ignorant la casse)
    $checkStmt = $db->prepare("SELECT COUNT(*) FROM salle WHERE LOWER(nom_salle) = LOWER(?) AND id_salle != ?");
    $checkStmt->execute([
        $nom_salle,
        $_POST['id_salle'] ?? 0
    ]);

    if ($checkStmt->fetchColumn() > 0) {
        throw new Exception('Une salle avec ce nom existe déjà');
    }

    $data = [
        'nom_salle' => $nom_salle,
        'capacite' => intval($_POST['capacite']),
        'equipements' => trim($_POST['equipements']),
        'disponible' => isset($_POST['disponible']) ? 1 : 0
    ];

    if (empty($_POST['id_salle'])) {
        // Nouvelle salle
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
        $message = 'Salle ajoutée avec succès';
    } else {
        // Mise à jour
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
        $message = 'Salle mise à jour avec succès';
    }

    $db->commit();

    echo json_encode([
        'success' => true,
        'message' => $message
    ]);
} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    error_log("Error in save_room.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
