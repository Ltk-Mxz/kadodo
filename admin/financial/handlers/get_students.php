<?php
require_once __DIR__ . '/../../../config/database.php';
header('Content-Type: application/json');

try {
    $db = Database::getInstance()->getConnection();

    $stmt = $db->query("
        SELECT u.id_utilisateur, u.matricule, u.prenom, u.nom,
               c.nom_classe
        FROM utilisateur u
        INNER JOIN etudiant e ON u.id_utilisateur = e.id_utilisateur
        INNER JOIN classe c ON e.id_classe = c.id_classe
        WHERE u.id_role = (SELECT id_role FROM role WHERE nom_role = 'etudiant')
        AND u.statut_compte = 'actif'
        ORDER BY u.nom, u.prenom
    ");

    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'students' => $students
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
