<?php
require_once '../config/database.php';
require_once '../components/notifications/notification.php';
session_start();

header('Content-Type: application/json');

try {
    if (!isset($_SESSION['user']['id'])) {
        throw new Exception('Non autorisé');
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!isset($input['notification_id'])) {
        throw new Exception('ID de notification manquant');
    }

    $success = Notification::markAsRead($input['notification_id'], $_SESSION['user']['id']);
    $unreadCount = Notification::getUnreadCount($_SESSION['user']['id']);

    echo json_encode([
        'success' => $success,
        'unread_count' => $unreadCount
    ]
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
