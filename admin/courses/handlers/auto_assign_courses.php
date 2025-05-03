<?php
require_once '../../../config/database.php';
header('Content-Type: application/json');

try {
    $db = Database::getInstance()->getConnection();
    $db->beginTransaction();

    // 1. Récupérer tous les étudiants sans cours attribués
    $sql = "SELECT DISTINCT e.id_etudiant, e.id_filiere, e.id_niveau
            FROM etudiant e
            LEFT JOIN etudiant_cours ec ON e.id_etudiant = ec.id_etudiant
            WHERE ec.id_etudiant IS NULL";

    $stmt = $db->query($sql);
    $etudiants = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $attributionsCount = 0;
    $etudiantsTraites = 0;

    foreach ($etudiants as $etudiant) {
        // 2. Récupérer les cours disponibles pour la filière et le niveau de l'étudiant
        $stmt = $db->prepare("
            SELECT DISTINCT c.id_cours
            FROM cours c
            JOIN attribution_cours ac ON c.id_cours = ac.id_cours
            WHERE ac.id_filiere = ?
            AND c.id_professeur IS NOT NULL
        ");
        $stmt->execute([$etudiant['id_filiere']]);
        $cours = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($cours)) {
            // 3. Attribuer chaque cours à l'étudiant
            $stmt = $db->prepare("
                INSERT INTO etudiant_cours (id_etudiant, id_cours)
                VALUES (?, ?)
            ");

            foreach ($cours as $id_cours) {
                $stmt->execute([$etudiant['id_etudiant'], $id_cours]);
                $attributionsCount++;
            }
        }
        $etudiantsTraites++;
    }

    $db->commit();

    echo json_encode([
        'success' => true,
        'message' => "Attribution terminée avec succès!\n" .
            "$etudiantsTraites étudiants traités\n" .
            "$attributionsCount cours attribués",
    ]);
} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    error_log("Erreur d'attribution automatique: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => "Erreur lors de l'attribution: " . $e->getMessage()
    ]);
}
