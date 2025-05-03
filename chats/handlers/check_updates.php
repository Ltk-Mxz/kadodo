<?php
require_once '../../config/database.php';
session_start();

header('Content-Type: application/json');

try {
    if (!isset($_SESSION['user']['id'])) {
        throw new Exception('Non autorisé');
    }

    $userId = $_SESSION['user']['id'];
    $currentTime = $_GET['current_time'] ?? null;

    $db = Database::getInstance()->getConnection();

    // Récupérer toutes les conversations de l'utilisateur
    $stmt = $db->prepare("
        SELECT 
            c.*,
            u.id_utilisateur,
            u.prenom,
            u.nom,
            r.nom_role,
            (SELECT contenu FROM messages WHERE id_conversation = c.id_conversation ORDER BY date_envoi DESC LIMIT 1) as dernier_message,
            (SELECT date_envoi FROM messages WHERE id_conversation = c.id_conversation ORDER BY date_envoi DESC LIMIT 1) as date_dernier_message,
            COUNT(CASE WHEN m.lu = FALSE AND m.id_expediteur != ? THEN 1 END) as non_lus,
            MAX(m.date_envoi) > DATE_SUB(NOW(), INTERVAL 5 SECOND) as has_new_messages
        FROM conversation c
        JOIN utilisateur u ON (
            CASE 
                WHEN c.id_expediteur = ? THEN c.id_destinataire = u.id_utilisateur
                ELSE c.id_expediteur = u.id_utilisateur
            END
        )
        JOIN role r ON u.id_role = r.id_role
        LEFT JOIN messages m ON m.id_conversation = c.id_conversation
        WHERE (c.id_expediteur = ? OR c.id_destinataire = ?)
        GROUP BY c.id_conversation
        ORDER BY c.derniere_mise_a_jour DESC
    ");

    $stmt->execute([$userId, $userId, $userId, $userId]);
    $conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Vérifier s'il y a des nouveaux messages dans n'importe quelle conversation
    $hasNewMessages = false;
    foreach ($conversations as $conv) {
        if ($conv['has_new_messages'] || $conv['non_lus'] > 0) {
            $hasNewMessages = true;
            break;
        }
    }

    echo json_encode([
        'success' => true,
        'hasNewMessages' => $hasNewMessages,
        'conversations' => $conversations,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
