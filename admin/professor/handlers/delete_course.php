<?php
require_once '../../../config/database.php';
header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['id_cours'])) {
        throw new Exception('ID du cours manquant');
    }

    $db = Database::getInstance()->getConnection();
    $db->beginTransaction();

    // Supprimer les documents liés au cours
    $stmt = $db->prepare("DELETE FROM document WHERE id_cours = ?");
    $stmt->execute([$data['id_cours']]);

    // Supprimer les notes liées au cours  
    $stmt = $db->prepare("DELETE FROM note WHERE id_cours = ?");
    $stmt->execute([$data['id_cours']]);

    // Supprimer les relations dans attribution_cours
    $stmt = $db->prepare("DELETE FROM attribution_cours WHERE id_cours = ?");
    $stmt->execute([$data['id_cours']]);

    // Supprimer les relations dans cours_filiere
    $stmt = $db->prepare("DELETE FROM cours_filiere WHERE id_cours = ?");
    $stmt->execute([$data['id_cours']]);

    // Supprimer les relations etudiant_cours
    $stmt = $db->prepare("DELETE FROM etudiant_cours WHERE id_cours = ?");
    $stmt->execute([$data['id_cours']]);

    // Mettre à null la référence dans la table etudiant
    $stmt = $db->prepare("UPDATE etudiant SET id_cours = NULL WHERE id_cours = ?");
    $stmt->execute([$data['id_cours']]);

    // Finalement, supprimer le cours
    $stmt = $db->prepare("DELETE FROM cours WHERE id_cours = ?");
    $stmt->execute([$data['id_cours']]);

    $db->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Cours et toutes ses relations supprimés avec succès'
    ]);
} catch (PDOException $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }

    $message = 'Erreur lors de la suppression du cours';
    if ($e->getCode() == '23000') {
        $message = 'Impossible de supprimer ce cours car il est utilisé ailleurs dans le système';
    }

    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $message,
        'debug' => $e->getMessage()
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
