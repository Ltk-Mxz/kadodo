<?php
require_once '../../config/database.php';
session_start();

// Désactiver l'affichage des erreurs PHP dans la sortie
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

try {
    if (!isset($_SESSION['user']['id'])) {
        throw new Exception('Non autorisé');
    }

    $userId = $_SESSION['user']['id'];
    $userIds = $_GET['user_ids'] ?? '';
    $userIds = array_filter(explode(',', $userIds), 'is_numeric');

    if (empty($userIds)) {
        throw new Exception('Aucun utilisateur spécifié');
    }

    $db = Database::getInstance()->getConnection();

    // Nettoyer les anciennes sessions
    $db->query("DELETE FROM user_sessions WHERE last_activity < DATE_SUB(NOW(), INTERVAL 1 MINUTE)");

    // Mettre à jour notre propre session
    $stmt = $db->prepare("
        INSERT INTO user_sessions (id_utilisateur, last_activity, is_online)
        VALUES (?, NOW(), TRUE)
        ON DUPLICATE KEY UPDATE last_activity = NOW(), is_online = TRUE
    ");
    $stmt->execute([$userId]);

    // Vérifier uniquement les sessions actives des dernières 60 secondes
    $placeholders = str_repeat('?,', count($userIds) - 1) . '?';
    $stmt = $db->prepare("
        SELECT 
            u.id_utilisateur,
            IF(us.last_activity > DATE_SUB(NOW(), INTERVAL 1 MINUTE), TRUE, FALSE) as is_online
        FROM utilisateur u
        LEFT JOIN user_sessions us ON u.id_utilisateur = us.id_utilisateur
        WHERE u.id_utilisateur IN ($placeholders)
    ");

    $stmt->execute($userIds);
    $statuses = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    // Par défaut tout le monde est hors ligne sauf ceux avec une session active
    foreach ($userIds as $id) {
        if (!isset($statuses[$id])) {
            $statuses[$id] = false;
        }
        // Conversion explicite en booléen
        $statuses[$id] = $statuses[$id] === true || $statuses[$id] === "1";
    }

    echo json_encode([
        'success' => true,
        'statuses' => $statuses,
        'debug' => [
            'requested_users' => count($userIds),
            'active_sessions' => count(array_filter($statuses)),
            'time' => date('Y-m-d H:i:s')
        ]
    ]);
} catch (Exception $e) {
    error_log($e->getMessage()); // Logger l'erreur au lieu de l'afficher
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Une erreur est survenue'
    ]);
}
