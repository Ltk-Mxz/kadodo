<?php
require_once '../../config/database.php';
session_start();

header('Content-Type: application/json');

try {
    if (!isset($_SESSION['user']['id'])) {
        throw new Exception('Non autorisé: Session utilisateur non trouvée');
    }

    $userId = $_SESSION['user']['id'];
    $conversationId = $_GET['conversation_id'] ?? null;

    if ($conversationId && !is_numeric($conversationId)) {
        throw new Exception('ID de conversation invalide');
    }

    $offset = (int)($_GET['offset'] ?? 0);
    $limit = 20;

    $db = Database::getInstance()->getConnection();

    if ($conversationId) {
        // Vérifier d'abord si l'utilisateur a accès à cette conversation
        $stmt = $db->prepare("
            SELECT id_conversation 
            FROM conversation 
            WHERE id_conversation = ? 
            AND (id_expediteur = ? OR id_destinataire = ?)
            LIMIT 1
        ");
        $stmt->execute([$conversationId, $userId, $userId]);

        if (!$stmt->fetch()) {
            throw new Exception("Accès refusé à la conversation $conversationId pour l'utilisateur $userId");
        }

        // Récupérer les informations de l'interlocuteur avec son statut en ligne
        $stmt = $db->prepare("
            SELECT 
                u.id_utilisateur,
                u.prenom,
                u.nom,
                u.photo_profile,
                c.id_conversation,
                CASE 
                    WHEN us.id_utilisateur IS NULL THEN FALSE
                    WHEN us.is_online = FALSE THEN FALSE
                    WHEN us.last_activity < DATE_SUB(NOW(), INTERVAL 1 MINUTE) THEN FALSE
                    ELSE TRUE 
                END as is_online
            FROM conversation c
            JOIN utilisateur u ON (
                CASE 
                    WHEN c.id_expediteur = ? THEN c.id_destinataire = u.id_utilisateur
                    ELSE c.id_expediteur = u.id_utilisateur
                END
            )
            LEFT JOIN user_sessions us ON u.id_utilisateur = us.id_utilisateur
            WHERE c.id_conversation = ?
            LIMIT 1
        ");
        $stmt->execute([$userId, $conversationId]);
        $interlocuteur = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$interlocuteur) {
            throw new Exception("Impossible de trouver l'interlocuteur pour la conversation $conversationId");
        }

        // Récupérer directement tous les messages de la conversation
        $stmt = $db->prepare("
            SELECT 
                m.*,
                u.prenom,
                u.nom,
                u.photo_profile 
            FROM messages m
            JOIN utilisateur u ON m.id_expediteur = u.id_utilisateur
            WHERE m.id_conversation = ?
            ORDER BY m.date_envoi ASC
        ");
        $stmt->execute([$conversationId]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Marquer les messages comme lus
        $stmt = $db->prepare("
            UPDATE messages 
            SET lu = TRUE 
            WHERE id_conversation = ? AND id_expediteur != ? AND lu = FALSE
        ");
        $stmt->execute([$conversationId, $userId]);

        echo json_encode([
            'success' => true,
            'data' => $messages,
            'interlocuteur' => $interlocuteur
        ]);
    } else {
        // Pour la liste des conversations
        $lastUpdate = $_GET['last_update'] ?? '0';
        $includeUnread = isset($_GET['include_unread']) && $_GET['include_unread'] === 'true';

        // Vérifier s'il y a des messages non lus ou des mises à jour
        $hasUpdates = false;
        $hasUnreadMessages = false;

        if ($includeUnread) {
            $stmt = $db->prepare("
                SELECT COUNT(*) as count
                FROM messages m
                JOIN conversation c ON m.id_conversation = c.id_conversation
                WHERE m.lu = FALSE 
                AND m.id_expediteur != ?
                AND (c.id_expediteur = ? OR c.id_destinataire = ?)
            ");
            $stmt->execute([$userId, $userId, $userId]);
            $hasUnreadMessages = $stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0;
        }

        $stmt = $db->prepare("
            SELECT COUNT(*) as count
            FROM conversation
            WHERE derniere_mise_a_jour > ?
            AND (id_expediteur = ? OR id_destinataire = ?)
        ");
        $stmt->execute([$lastUpdate, $userId, $userId]);
        $hasUpdates = $stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0;

        if (!$hasUpdates && !$hasUnreadMessages) {
            echo json_encode([
                'success' => true,
                'hasUpdates' => false,
                'hasUnreadMessages' => false
            ]);
            exit;
        }

        // Récupérer la liste des conversations où l'utilisateur est participant
        $stmt = $db->prepare("
            SELECT 
                c.*,
                u.id_utilisateur,
                u.prenom,
                u.nom,
                u.photo_profile,
                r.nom_role,
                (
                    SELECT m2.contenu 
                    FROM messages m2 
                    WHERE m2.id_conversation = c.id_conversation 
                    ORDER BY m2.date_envoi DESC 
                    LIMIT 1
                ) as dernier_message,
                (
                    SELECT m2.date_envoi 
                    FROM messages m2 
                    WHERE m2.id_conversation = c.id_conversation 
                    ORDER BY m2.date_envoi DESC 
                    LIMIT 1
                ) as date_dernier_message,
                COUNT(CASE WHEN m.lu = FALSE AND m.id_expediteur != ? THEN 1 END) as non_lus
            FROM conversation c
            JOIN utilisateur u ON (
                CASE 
                    WHEN c.id_expediteur = ? THEN c.id_destinataire = u.id_utilisateur
                    WHEN c.id_destinataire = ? THEN c.id_expediteur = u.id_utilisateur
                END
            )
            JOIN role r ON u.id_role = r.id_role
            LEFT JOIN messages m ON m.id_conversation = c.id_conversation
            WHERE (c.id_expediteur = ? OR c.id_destinataire = ?)
            GROUP BY c.id_conversation
            ORDER BY c.derniere_mise_a_jour DESC
        ");
        $stmt->execute([$userId, $userId, $userId, $userId, $userId]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'hasUpdates' => $hasUpdates,
            'hasUnreadMessages' => $hasUnreadMessages,
            'data' => $messages
        ]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur de base de données: ' . $e->getMessage(),
        'error_code' => $e->getCode()
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug_info' => [
            'conversation_id' => $conversationId ?? null,
            'user_id' => $userId ?? null
        ]
    ]);
}
