<?php
require_once '../config/database.php';
require_once '../components/notifications/notification.php';
session_start();

header('Content-Type: application/json');

try {
    if (!isset($_SESSION['user']['id'])) {
        throw new Exception('Non autorisé');
    }

    $userId = $_SESSION['user']['id'];
    $db = Database::getInstance()->getConnection();

    // Marquer toutes les notifications comme lues
    $stmt = $db->prepare("
        UPDATE notifications 
        SET lu = TRUE 
        WHERE id_utilisateur = ? AND lu = FALSE
    ");

    $success = $stmt->execute([$userId]);

    echo json_encode([
        'success' => $success,
        'message' => $success ? 'Toutes les notifications ont été marquées comme lues' : 'Erreur lors de la mise à jour'
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
