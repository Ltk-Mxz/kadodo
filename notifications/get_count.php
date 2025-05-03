<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

try {
    if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id'])) {
        throw new Exception('Session invalide');
    }

    $db = Database::getInstance()->getConnection();
    $userId = $_SESSION['user']['id'];

    $query = "SELECT COUNT(*) as count FROM notifications WHERE id_destinataire = ? AND lu = 0";
    $stmt = $db->prepare($query);
    $stmt->execute([$userId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'count' => (int)$result['count']
    ]);
} catch (Exception $e) {
    error_log("Erreur notification count: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erreur lors de la récupération des notifications'
    ]);
}
