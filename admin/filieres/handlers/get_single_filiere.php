<?php
require_once '../../../config/database.php';
header('Content-Type: application/json');

try {
    if (!isset($_GET['id'])) {
        throw new Exception('ID non fourni');
    }

    $db = Database::getInstance()->getConnection();

    $query = "SELECT f.* FROM filiere f WHERE f.id_filiere = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$_GET['id']]);
    $filiere = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($filiere) {
        echo json_encode([
            'success' => true,
            'filiere' => $filiere
        ]);
    } else {
        throw new Exception('Filière non trouvée');
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
