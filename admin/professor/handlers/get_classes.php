<?php
require_once '../../../config/database.php';
header('Content-Type: application/json');

try {
    $db = Database::getInstance()->getConnection();

    $query = "SELECT id_classe, nom_classe FROM classe ORDER BY nom_classe";

    $stmt = $db->prepare($query);
    $stmt->execute();

    $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'classes' => $classes
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
