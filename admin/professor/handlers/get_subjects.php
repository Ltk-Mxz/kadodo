<?php
require_once '../../../config/database.php';
header('Content-Type: application/json');

try {
    $db = Database::getInstance()->getConnection();

    $query = "SELECT DISTINCT nom_cours FROM cours WHERE nom_cours IS NOT NULL AND nom_cours != '' ORDER BY nom_cours";

    $stmt = $db->prepare($query);
    $stmt->execute();

    echo json_encode([
        'success' => true,
        'subjects' => $stmt->fetchAll(PDO::FETCH_ASSOC)
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
