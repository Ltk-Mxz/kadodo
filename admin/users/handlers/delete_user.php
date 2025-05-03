<?php
require_once __DIR__ . '/../../../config/database.php';
header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['userId'])) {
        throw new Exception('ID utilisateur non fourni');
    }

    $db = Database::getInstance()->getConnection();
    $db->beginTransaction();

    // Supprimer d'abord les transactions associées
    $stmt = $db->prepare("DELETE FROM transactions WHERE id_etudiant = ?");
    $stmt->execute([$data['userId']]);

    // Supprimer les enregistrements dans les autres tables liées
    $tables = ['etudiant', 'professeur', 'administrateur', 'moderateur'];
    foreach ($tables as $table) {
        $stmt = $db->prepare("DELETE FROM $table WHERE id_utilisateur = ?");
        $stmt->execute([$data['userId']]);
    }

    // Enfin, supprimer l'utilisateur
    $stmt = $db->prepare("DELETE FROM utilisateur WHERE id_utilisateur = ?");
    $stmt->execute([$data['userId']]);

    $db->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Utilisateur supprimé avec succès'
    ]);
} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }

    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
