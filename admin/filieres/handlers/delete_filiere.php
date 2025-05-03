<?php
require_once '../../../config/database.php';
header('Content-Type: application/json');

$db = null;
try {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['id_filiere'])) {
        throw new Exception('ID manquant');
    }

    $db = Database::getInstance()->getConnection();
    $db->beginTransaction();

    // 1. Supprimer les documents
    $stmt = $db->prepare("DELETE FROM document WHERE id_filiere = ?");
    $stmt->execute([$data['id_filiere']]);

    // 2. Supprimer les étudiants des classes de cette filière
    $stmt = $db->prepare("DELETE e FROM etudiant e 
                         INNER JOIN classe c ON e.id_classe = c.id_classe 
                         WHERE c.id_filiere = ?");
    $stmt->execute([$data['id_filiere']]);

    // 3. Supprimer les attributions de cours
    $stmt = $db->prepare("DELETE FROM attribution_cours WHERE id_filiere = ?");
    $stmt->execute([$data['id_filiere']]);

    // 4. Supprimer les classes
    $stmt = $db->prepare("DELETE FROM classe WHERE id_filiere = ?");
    $stmt->execute([$data['id_filiere']]);

    // 5. Supprimer la filière
    $stmt = $db->prepare("DELETE FROM filiere WHERE id_filiere = ?");
    $stmt->execute([$data['id_filiere']]);

    $db->commit();
    echo json_encode([
        'success' => true,
        'message' => 'Filière et toutes ses données associées supprimées avec succès'
    ]);
} catch (Exception $e) {
    if ($db && $db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
