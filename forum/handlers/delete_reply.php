<?php
require_once '../../config/database.php';
require_once '../../utils/auth.php';

header('Content-Type: application/json');

try {
    requireAuth();

    $replyId = (int)($_GET['id'] ?? 0);
    $userId = $_SESSION['user']['id'];
    $userRole = $_SESSION['user']['type'];
    $TIME_LIMIT = 30; // 30 minutes

    if ($replyId <= 0) {
        throw new Exception('ID de commentaire invalide');
    }

    $db = Database::getInstance()->getConnection();

    // Vérifier les permissions
    $stmt = $db->prepare("
        SELECT c.*, p.id_publication_forum 
        FROM commentaire c
        JOIN publication_forum p ON c.id_publication_forum = p.id_publication_forum
        WHERE c.id_commentaire = ?
    ");
    $stmt->execute([$replyId]);
    $reply = $stmt->fetch();

    if (!$reply) {
        throw new Exception('Commentaire non trouvé');
    }

    $isAdmin = $userRole === 'admin';
    $isModerator = $userRole === 'moderateur';
    $isAuthor = $reply['id_utilisateur'] == $userId;

    if (!$isAdmin && !$isModerator && !$isAuthor) {
        throw new Exception('Vous n\'êtes pas autorisé à supprimer ce commentaire');
    }

    // Vérifier le délai pour les utilisateurs normaux
    if (!$isAdmin && !$isModerator && $isAuthor) {
        $creationTime = new DateTime($reply['date_creation']);
        $now = new DateTime();
        $diffInMinutes = $creationTime->diff($now)->format('%i'); // Juste les minutes

        if ($diffInMinutes > $TIME_LIMIT) {
            throw new Exception("Vous ne pouvez plus supprimer ce commentaire après {$TIME_LIMIT} minutes");
        }
    }

    // Supprimer le commentaire
    $stmt = $db->prepare("DELETE FROM commentaire WHERE id_commentaire = ?");
    $stmt->execute([$replyId]);

    // Récupérer le nouveau nombre total de réponses
    $stmt = $db->prepare("
        SELECT COUNT(*) as total 
        FROM commentaire 
        WHERE id_publication_forum = ?
    ");
    $stmt->execute([$reply['id_publication_forum']]);
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    echo json_encode([
        'success' => true,
        'message' => 'Commentaire supprimé avec succès',
        'total_replies' => $total
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
