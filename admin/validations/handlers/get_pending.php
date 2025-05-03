<?php
require_once '../../../config/database.php';
header('Content-Type: application/json');

try {
    $db = Database::getInstance()->getConnection();

    $stmt = $db->query("
        SELECT u.*, r.nom_role
        FROM utilisateur u
        JOIN role r ON u.id_role = r.id_role
        WHERE u.statut_compte = 'inactif'
        ORDER BY u.date_creation DESC
    ");

    echo json_encode([
        'success' => true,
        'users' => $stmt->fetchAll(PDO::FETCH_ASSOC)
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
