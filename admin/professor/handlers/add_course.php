<?php
require_once '../../../config/database.php';
header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['professor_id']) || !isset($data['course_name']) || !isset($data['filiere_id'])) {
        throw new Exception('Données manquantes');
    }

    $db = Database::getInstance()->getConnection();
    $db->beginTransaction();

    // Créer le cours
    $stmt = $db->prepare("INSERT INTO cours (nom_cours, id_professeur) VALUES (?, ?)");
    $stmt->execute([$data['course_name'], $data['professor_id']]);

    $id_cours = $db->lastInsertId();

    // Attribution du cours à la filière
    $stmt = $db->prepare("INSERT INTO attribution_cours (id_cours, id_filiere) VALUES (?, ?)");
    $stmt->execute([$id_cours, $data['filiere_id']]);

    // Attribution automatique aux étudiants de la filière
    $stmt = $db->prepare("
        INSERT INTO etudiant_cours (id_etudiant, id_cours)
        SELECT e.id_etudiant, ?
        FROM etudiant e
        JOIN classe c ON e.id_classe = c.id_classe
        WHERE c.id_filiere = ?
    ");
    $stmt->execute([$id_cours, $data['filiere_id']]);

    $db->commit();
    echo json_encode(['success' => true, 'message' => 'Cours créé et attribué avec succès']);
} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) $db->rollBack();
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
