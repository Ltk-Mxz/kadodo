<?php
require_once '../../../config/database.php';
header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'ID du cours non fourni'
    ]);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();

    $query = "SELECT c.*, CONCAT(u.nom, ' ', u.prenom) as prof_nom, ac.id_filiere
              FROM cours c
              LEFT JOIN professeur p ON c.id_professeur = p.id_professeur
              LEFT JOIN utilisateur u ON p.id_utilisateur = u.id_utilisateur
              LEFT JOIN attribution_cours ac ON c.id_cours = ac.id_cours
              WHERE c.id_cours = :id_cours
              LIMIT 1";

    $stmt = $db->prepare($query);
    $stmt->execute(['id_cours' => $_GET['id']]);
    $course = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($course) {
        echo json_encode([
            'success' => true,
            'course' => $course
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Cours non trouvé'
        ]);
    }
} catch (Exception $e) {
    error_log("Error in get_single_course.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la récupération du cours'
    ]);
}
