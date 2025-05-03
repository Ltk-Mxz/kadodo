<?php
require_once '../../../config/database.php';
header('Content-Type: application/json');

try {
    $db = Database::getInstance()->getConnection();

    $query = "SELECT DISTINCT salle FROM cours WHERE salle IS NOT NULL ORDER BY salle";

    $stmt = $db->prepare($query);
    $stmt->execute();

    echo json_encode([
        'success' => true,
        'rooms' => $stmt->fetchAll(PDO::FETCH_ASSOC)
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
