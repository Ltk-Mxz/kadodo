<?php
require_once __DIR__ . '/../config/database.php';

function getNotifications($userId)
{
    try {
        $db = Database::getInstance()->getConnection();

        $query = "SELECT n.*, 
                  u.nom as expediteur_nom, 
                  u.prenom as expediteur_prenom 
                  FROM notifications n
                  LEFT JOIN utilisateur u ON n.id_expediteur = u.id_utilisateur 
                  WHERE n.id_destinataire = ? 
                  ORDER BY n.date_creation DESC";

        $stmt = $db->prepare($query);
        $stmt->execute([$userId]);

        return [
            'success' => true,
            'notifications' => $stmt->fetchAll(PDO::FETCH_ASSOC)
        ];
    } catch (Exception $e) {
        error_log("Erreur notifications: " . $e->getMessage());
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}
