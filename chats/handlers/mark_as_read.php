<?php
require_once '../../config/database.php';
session_start();

header('Content-Type: application/json');

try {
    if (!isset($_SESSION['user']['id'])) {
        throw new Exception('Non autorisé');
    }

    $data = json_decode(file_get_contents('php://input'), true);
    $conversationId = $data['conversation_id'] ?? null;
    $userId = $_SESSION['user']['id'];

    if (!$conversationId) {
        throw new Exception('ID de conversation manquant');
    }

    $db = Database::getInstance()->getConnection();

    // Vérifier l'accès à la conversation
    $stmt = $db->prepare("
        SELECT id_conversation 
        FROM conversation 
        WHERE id_conversation = ? 
        AND (id_expediteur = ? OR id_destinataire = ?)
    ");
    $stmt->execute([$conversationId, $userId, $userId]);

    if (!$stmt->fetch()) {
        throw new Exception('Conversation non trouvée ou accès non autorisé');
    }

    // Marquer tous les messages non lus comme lus
    $stmt = $db->prepare("
        UPDATE messages 
        SET lu = TRUE 
        WHERE id_conversation = ? 
        AND id_expediteur != ? 
        AND lu = FALSE
    ");

    $stmt->execute([$conversationId, $userId]);

    echo json_encode([
        'success' => true,
        'message' => 'Messages marqués comme lus'
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
