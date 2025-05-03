<?php
require_once '../../../config/database.php';
header('Content-Type: application/json');

try {
    if (!isset($_GET['id'])) {
        throw new Exception('ID du cours manquant');
    }

    $db = Database::getInstance()->getConnection();

    // Récupérer le cours avec ses filières associées
    $stmt = $db->prepare("
        SELECT c.*, GROUP_CONCAT(f.id_filiere) as filiere_ids, GROUP_CONCAT(f.libelle_filiere) as filiere_noms
        FROM cours c
        LEFT JOIN cours_filiere cf ON c.id_cours = cf.id_cours
        LEFT JOIN filiere f ON cf.id_filiere = f.id_filiere
        WHERE c.id_cours = ?
        GROUP BY c.id_cours
    ");

    $stmt->execute([$_GET['id']]);
    $course = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($course) {
        // Formater les filières
        $course['filieres'] = [];
        if ($course['filiere_ids']) {
            $ids = explode(',', $course['filiere_ids']);
            $noms = explode(',', $course['filiere_noms']);
            foreach ($ids as $i => $id) {
                $course['filieres'][] = [
                    'id_filiere' => (int)$id,
                    'libelle_filiere' => $noms[$i]
                ];
            }
        }
        unset($course['filiere_ids'], $course['filiere_noms']);
    }

    echo json_encode([
        'success' => true,
        'course' => $course
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
