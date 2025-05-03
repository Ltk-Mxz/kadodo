<?php
require_once '../../../config/database.php';
header('Content-Type: application/json');

try {
    $db = Database::getInstance()->getConnection();

    $query = "SELECT c.*, CONCAT(u.nom, ' ', u.prenom) as prof_nom,
              COALESCE(f.libelle_filiere, 'Non assigné') as libelle_filiere
              FROM cours c
              LEFT JOIN professeur p ON c.id_professeur = p.id_professeur
              LEFT JOIN utilisateur u ON p.id_utilisateur = u.id_utilisateur
              LEFT JOIN attribution_cours ac ON c.id_cours = ac.id_cours
              LEFT JOIN filiere f ON ac.id_filiere = f.id_filiere
              ORDER BY c.id_cours DESC";

    $stmt = $db->prepare($query);
    $stmt->execute();
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'courses' => $courses
    ]);
} catch (Exception $e) {
    error_log("Error in get_courses.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
