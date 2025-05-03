<?php
require_once '../../../config/database.php';
header('Content-Type: application/json');

try {
    $db = Database::getInstance()->getConnection();

    $query = "SELECT c.id_cours, u.nom as prof_nom, u.prenom as prof_prenom, 
              cl.nom_classe, c.nom_cours, c.salle
              FROM cours c
              INNER JOIN professeur p ON c.id_professeur = p.id_professeur
              INNER JOIN utilisateur u ON p.id_utilisateur = u.id_utilisateur
              INNER JOIN classe cl ON c.id_classe = cl.id_classe
              ORDER BY u.nom, u.prenom, cl.nom_classe";

    $stmt = $db->prepare($query);
    $stmt->execute();
    $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'assignments' => $assignments
    ]);
} catch (Exception $e) {
    error_log("Error in get_assignments.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
