<?php
require_once '../config/database.php';
session_start();

header('Content-Type: application/json');

try {
    $userId = $_SESSION['user']['id'];
    $isAdmin = $_SESSION['user']['type'] === 'admin';
    $db = Database::getInstance()->getConnection();

    // Récupérer les notifications
    $query = "
        SELECT * FROM (
            -- Notifications standard
            SELECT 
                n.id_notification,
                n.message,
                n.type,
                n.date_creation,
                n.lu,
                u.prenom, 
                u.nom,
                u.photo_profile
            FROM notifications n
            LEFT JOIN utilisateur u ON n.id_expediteur = u.id_utilisateur
            WHERE n.id_destinataire = :user_id

            UNION ALL

            -- Inscriptions en attente (pour admin seulement)
            SELECT 
                u.id_utilisateur as id_notification,
                CONCAT('Nouvelle inscription en attente: ', u.prenom, ' ', u.nom) as message,
                'inscription_pending' as type,
                u.date_creation,
                0 as lu,
                u.prenom,
                u.nom,
                u.photo_profile
            FROM utilisateur u
            WHERE u.statut_compte = 'inactif'
            AND :is_admin = 1
        ) notifications
        ORDER BY date_creation DESC
        LIMIT 5
    ";

    $stmt = $db->prepare($query);
    $stmt->execute([
        ':user_id' => $userId,
        ':is_admin' => $isAdmin
    ]);

    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'notifications' => $notifications
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
