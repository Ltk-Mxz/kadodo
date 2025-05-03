<?php
class Notification
{
    public static function create($userId, $type, $message, $link = null)
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            INSERT INTO notifications (id_utilisateur, type, message, lien, date_creation)
            VALUES (?, ?, ?, ?, NOW())
        ");
        return $stmt->execute([$userId, $type, $message, $link]);
    }

    public static function getUnread($userId, $limit = 50)
    {
        $db = Database::getInstance()->getConnection();

        // Récupérer toutes les notifications
        $query = "
            SELECT * FROM (
                -- Notifications standard
                SELECT 
                    n.id_notification,
                    n.message,
                    n.type,
                    n.lu,
                    n.date_creation,
                    n.id_expediteur,
                    u.prenom, u.nom
                FROM notifications n
                LEFT JOIN utilisateur u ON n.id_expediteur = u.id_utilisateur
                WHERE n.id_destinataire = :user_id
                
                UNION ALL
                
                -- Notifications d'inscription en attente (pour admin)
                SELECT 
                    u.id_utilisateur as id_notification,
                    CONCAT('Nouvelle inscription en attente : ', u.prenom, ' ', u.nom) as message,
                    'inscription_pending' as type,
                    0 as lu,
                    u.date_creation,
                    u.id_utilisateur as id_expediteur,
                    u.prenom, u.nom
                FROM utilisateur u
                WHERE u.statut_compte = 'inactif'
                AND EXISTS (
                    SELECT 1 
                    FROM utilisateur ua
                    JOIN role r ON ua.id_role = r.id_role 
                    WHERE ua.id_utilisateur = :user_id 
                    AND r.nom_role = 'admin'
                )
            ) AS combined
            ORDER BY date_creation DESC
            LIMIT :limit
        ";

        $stmt = $db->prepare($query);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'notifications' => $stmt->fetchAll(PDO::FETCH_ASSOC)
        ];
    }

    public static function getNotificationCounters($userId)
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT 
                type,
                COUNT(*) as total,
                SUM(CASE WHEN lu = FALSE THEN 1 ELSE 0 END) as unread
            FROM notifications 
            WHERE id_utilisateur = ?
            GROUP BY type
        ");
        $stmt->execute([$userId]);

        $counters = [
            'total' => 0,
            'note' => 0,
            'document' => 0,
            'annonce' => 0,
            'message' => 0
        ];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $counters[$row['type']] = (int)$row['unread'];
            $counters['total'] += (int)$row['unread'];
        }

        return $counters;
    }

    public static function markAsRead($notificationId, $userId)
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            UPDATE notifications 
            SET lu = TRUE 
            WHERE id_notification = ? AND id_utilisateur = ?
        ");
        return $stmt->execute([$notificationId, $userId]);
    }

    public static function markAllAsRead($userId)
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            UPDATE notifications 
            SET lu = TRUE 
            WHERE id_utilisateur = ? AND lu = FALSE
        ");
        return $stmt->execute([$userId]);
    }

    public static function getUnreadCount($userId)
    {
        $db = Database::getInstance()->getConnection();

        $query = "
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN type = 'note' THEN 1 ELSE 0 END) as note,
                SUM(CASE WHEN type = 'document' THEN 1 ELSE 0 END) as document,
                SUM(CASE WHEN type = 'annonce' THEN 1 ELSE 0 END) as annonce
            FROM notifications 
            WHERE id_destinataire = :user_id AND lu = 0
        ";

        $stmt = $db->prepare($query);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
