<?php
require_once '../config/database.php';
require_once '../components/notifications/notification.php';
session_start();

header('Content-Type: application/json');

try {
    if (!isset($_SESSION['user']['id'])) {
        throw new Exception('Non autorisé');
    }

    if (!isset($_POST['type']) || !isset($_POST['message'])) {
        throw new Exception('Paramètres manquants');
    }

    $type = htmlspecialchars($_POST['type']);
    $message = htmlspecialchars($_POST['message']);
    $userId = $_SESSION['user']['id'];

    // Validation du type
    $allowedTypes = ['info', 'success', 'warning', 'error'];
    if (!in_array($type, $allowedTypes)) {
        throw new Exception('Type de notification invalide');
    }

    // Validation du message
    if (empty($message)) {
        throw new Exception('Le message ne peut pas être vide');
    }

    if (Notification::create($userId, $type, $message)) {
        echo json_encode([
            'success' => true,
            'message' => 'Notification envoyée avec succès'
        ]);
    } else {
        throw new Exception('Erreur lors de la création de la notification');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
