<?php
require_once '../../config/database.php';
session_start();

header('Content-Type: application/json');

try {
    if (!isset($_SESSION['user']['id'])) {
        throw new Exception('Non autorisé: Session utilisateur non trouvée');
    }

    $expediteurId = $_SESSION['user']['id'];
    $destinataireId = $_POST['destinataire_id'] ?? null;
    $conversationId = $_POST['conversation_id'] ?? null;
    $contenu = $_POST['contenu'] ?? null;

    if (!$destinataireId && !$conversationId) {
        throw new Exception('ID du destinataire ou de la conversation manquant');
    }

    if ($destinataireId && !is_numeric($destinataireId)) {
        throw new Exception('ID du destinataire invalide');
    }

    if ($conversationId && !is_numeric($conversationId)) {
        throw new Exception('ID de conversation invalide');
    }

    if (empty(trim($contenu))) {
        throw new Exception('Le message ne peut pas être vide');
    }

    $db = Database::getInstance()->getConnection();
    $db->beginTransaction();

    try {
        // Si on a un destinataire_id, on cherche/crée la conversation
        if ($destinataireId) {
            // Vérifier si le destinataire existe
            $stmt = $db->prepare("SELECT id_utilisateur FROM utilisateur WHERE id_utilisateur = ?");
            $stmt->execute([$destinataireId]);
            if (!$stmt->fetch()) {
                throw new Exception('Destinataire non trouvé');
            }

            $stmt = $db->prepare("
                SELECT id_conversation 
                FROM conversation 
                WHERE (id_expediteur = ? AND id_destinataire = ?)
                OR (id_expediteur = ? AND id_destinataire = ?)
                LIMIT 1
            ");
            $stmt->execute([$expediteurId, $destinataireId, $destinataireId, $expediteurId]);
            $conversation = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$conversation) {
                // Créer une nouvelle conversation
                $stmt = $db->prepare("
                    INSERT INTO conversation (id_expediteur, id_destinataire, derniere_mise_a_jour)
                    VALUES (?, ?, NOW())
                ");
                $stmt->execute([$expediteurId, $destinataireId]);
                $conversationId = $db->lastInsertId();
            } else {
                $conversationId = $conversation['id_conversation'];
            }
        } else {
            // Vérifier l'accès à la conversation existante
            $stmt = $db->prepare("
                SELECT id_conversation 
                FROM conversation 
                WHERE id_conversation = ? 
                AND (id_expediteur = ? OR id_destinataire = ?)
                LIMIT 1
            ");
            $stmt->execute([$conversationId, $expediteurId, $expediteurId]);
            if (!$stmt->fetch()) {
                throw new Exception('Conversation non trouvée ou accès non autorisé');
            }
        }

        // Insérer le message
        $stmt = $db->prepare("
            INSERT INTO messages (id_conversation, id_expediteur, contenu, date_envoi, lu)
            VALUES (?, ?, ?, NOW(), FALSE)
        ");
        $stmt->execute([$conversationId, $expediteurId, $contenu]);
        $messageId = $db->lastInsertId();

        // Récupérer les informations du message envoyé avec le nombre de non lus
        $stmt = $db->prepare("
            SELECT 
                m.*,
                u.prenom,
                u.nom,
                u.photo_profile,
                (
                    SELECT COUNT(*) 
                    FROM messages 
                    WHERE id_conversation = m.id_conversation 
                    AND lu = FALSE 
                    AND id_expediteur != ?
                ) as non_lus
            FROM messages m
            JOIN utilisateur u ON m.id_expediteur = u.id_utilisateur
            WHERE m.id_message = ?
        ");
        $stmt->execute([$expediteurId, $messageId]);
        $messageInfo = $stmt->fetch(PDO::FETCH_ASSOC);

        // Mettre à jour la date de dernière mise à jour
        $stmt = $db->prepare("
            UPDATE conversation 
            SET derniere_mise_a_jour = NOW()
            WHERE id_conversation = ?
        ");
        $stmt->execute([$conversationId]);

        // Récupérer les informations du message envoyé avec le nombre de non lus pour toutes les conversations
        $stmt = $db->prepare("
            SELECT 
                c.id_conversation,
                COUNT(CASE WHEN m.lu = FALSE AND m.id_expediteur != ? THEN 1 END) as non_lus
            FROM conversation c
            LEFT JOIN messages m ON m.id_conversation = c.id_conversation
            WHERE (c.id_expediteur = ? OR c.id_destinataire = ?)
            GROUP BY c.id_conversation
        ");
        $stmt->execute([$expediteurId, $expediteurId, $expediteurId]);
        $unreadCounts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Message envoyé avec succès',
            'conversation_id' => $conversationId,
            'message_info' => $messageInfo,
            'unread_counts' => $unreadCounts
        ]);
    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur de base de données: ' . $e->getMessage(),
        'debug_info' => [
            'conversation_id' => $conversationId ?? null,
            'expediteur_id' => $expediteurId ?? null
        ]
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug_info' => [
            'conversation_id' => $conversationId ?? null,
            'expediteur_id' => $expediteurId ?? null,
            'contenu_length' => $contenu ? strlen($contenu) : 0
        ]
    ]);
}
