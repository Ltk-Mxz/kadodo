<?php
require_once '../../config/database.php';
require_once '../../utils/auth.php';

header('Content-Type: application/json');

try {
    requireAuth();

    $topicId = (int)($_GET['id'] ?? 0);
    $userId = $_SESSION['user']['id'];
    $userRole = $_SESSION['user']['type'];
    $TIME_LIMIT = 30; // 30 minutes pour modification/suppression

    if ($topicId <= 0) {
        throw new Exception('ID de sujet invalide');
    }

    $db = Database::getInstance()->getConnection();

    // Vérifier que l'utilisateur est l'auteur et que le délai n'est pas dépassé
    $stmt = $db->prepare("
        SELECT id_utilisateur, date_creation 
        FROM publication_forum 
        WHERE id_publication_forum = ?
    ");
    $stmt->execute([$topicId]);
    $topic = $stmt->fetch();

    if (!$topic) {
        throw new Exception('Sujet non trouvé');
    }

    $isAdmin = $userRole === 'admin';
    $isModerator = $userRole === 'moderateur';
    $isAuthor = $topic['id_utilisateur'] == $userId;

    // Vérifier les permissions
    if (!$isAdmin && !$isModerator && !$isAuthor) {
        throw new Exception('Vous n\'êtes pas autorisé à supprimer ce sujet');
    }

    // Vérifier le délai pour les utilisateurs normaux
    if (!$isAdmin && !$isModerator && $isAuthor) {
        $creationTime = strtotime($topic['date_creation']);
        $now = time();
        $minutesElapsed = round(abs($now - $creationTime) / 60);

        if ($minutesElapsed > $TIME_LIMIT) {
            throw new Exception("Vous ne pouvez plus supprimer ce sujet après {$TIME_LIMIT} minutes");
        }
    }

    // Supprimer les commentaires d'abord
    $stmt = $db->prepare("DELETE FROM commentaire WHERE id_publication_forum = ?");
    $stmt->execute([$topicId]);

    // Supprimer le sujet
    $stmt = $db->prepare("DELETE FROM publication_forum WHERE id_publication_forum = ?");
    $stmt->execute([$topicId]);

    echo json_encode([
        'success' => true,
        'message' => 'Sujet supprimé avec succès'
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
