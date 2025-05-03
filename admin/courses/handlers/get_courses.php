<?php
require_once '../../../config/database.php';
header('Content-Type: application/json');

try {
    $db = Database::getInstance()->getConnection();

    $query = "
        SELECT 
            c.*,
            CONCAT(u.nom, ' ', u.prenom) as prof_nom,
            GROUP_CONCAT(DISTINCT f.id_filiere) as filiere_ids,
            GROUP_CONCAT(DISTINCT f.libelle_filiere) as filiere_libelles
        FROM cours c 
        LEFT JOIN professeur p ON c.id_professeur = p.id_professeur
        LEFT JOIN utilisateur u ON p.id_utilisateur = u.id_utilisateur
        LEFT JOIN attribution_cours ac ON c.id_cours = ac.id_cours
        LEFT JOIN filiere f ON ac.id_filiere = f.id_filiere
        GROUP BY c.id_cours
        ORDER BY c.date_debut DESC
    ";

    $stmt = $db->query($query);
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($courses as &$course) {
        // Formater les dates
        $course['date_debut'] = $course['date_debut'] ? date('Y-m-d', strtotime($course['date_debut'])) : '';
        $course['date_fin'] = $course['date_fin'] ? date('Y-m-d', strtotime($course['date_fin'])) : '';

        // Formater les filières
        $course['filieres'] = [];
        if (!empty($course['filiere_ids'])) {
            $ids = explode(',', $course['filiere_ids']);
            $libelles = explode(',', $course['filiere_libelles']);
            for ($i = 0; $i < count($ids); $i++) {
                $course['filieres'][] = [
                    'id_filiere' => (int)$ids[$i],
                    'libelle_filiere' => $libelles[$i]
                ];
            }
        }

        unset($course['filiere_ids'], $course['filiere_libelles']);
    }

    echo json_encode([
        'success' => true,
        'courses' => $courses
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la récupération des cours',
        'error' => $e->getMessage()
    ]);
}
