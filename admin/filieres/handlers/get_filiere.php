<?php
require_once '../../../config/database.php';
header('Content-Type: application/json');

try {
    if (!isset($_GET['id'])) {
        throw new Exception('ID manquant');
    }

    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT * FROM filiere WHERE id_filiere = ?");
    $stmt->execute([$_GET['id']]);
    $filiere = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$filiere) {
        throw new Exception('Filière non trouvée');
    }

    echo json_encode([
        'success' => true,
        'filiere' => $filiere
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
