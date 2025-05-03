<?php
require_once '../../config/database.php';
require_once '../../utils/auth.php';
require_once '../../utils/sanitize.php';

header('Content-Type: application/json');

try {
    requireAuth();

    $replyId = (int)($_GET['id'] ?? 0);
    $userId = $_SESSION['user']['id'];
    $userRole = $_SESSION['user']['type'];
    $TIME_LIMIT = 30; // 30 minutes

    // Récupérer le contenu du JSON
    $data = json_decode(file_get_contents('php://input'), true);
    $content = trim($data['content'] ?? '');

    if ($replyId <= 0) {
        throw new Exception('ID de commentaire invalide');
    }

    if (strlen($content) < 5) {
        throw new Exception('Le commentaire doit contenir au moins 5 caractères');
    }

    $db = Database::getInstance()->getConnection();

    // Vérifier les permissions
    $stmt = $db->prepare("SELECT * FROM commentaire WHERE id_commentaire = ?");
    $stmt->execute([$replyId]);
    $reply = $stmt->fetch();

    if (!$reply) {
        throw new Exception('Commentaire non trouvé');
    }

    $isAdmin = $userRole === 'admin';
    $isModerator = $userRole === 'moderateur';
    $isAuthor = $reply['id_utilisateur'] == $userId;

    if (!$isAdmin && !$isModerator && !$isAuthor) {
        throw new Exception('Vous n\'êtes pas autorisé à modifier ce commentaire');
    }

    // Vérifier le délai pour les utilisateurs normaux
    if (!$isAdmin && !$isModerator && $isAuthor) {
        $creationTime = new DateTime($reply['date_creation']);
        $now = new DateTime();
        $diffInMinutes = $creationTime->diff($now)->format('%i'); // Juste les minutes
        // Debug temporaire
        error_log("Time created: " . $reply['date_creation']);
        error_log("Current time: " . $now->format('Y-m-d H:i:s'));
        error_log("Minutes elapsed: " . $diffInMinutes);

        if ($diffInMinutes > $TIME_LIMIT) {
            throw new Exception("Vous ne pouvez plus modifier ce commentaire après {$TIME_LIMIT} minutes");
        }
    }

    // Mettre à jour le commentaire
    $stmt = $db->prepare("UPDATE commentaire SET contenu = ? WHERE id_commentaire = ?");
    $stmt->execute([sanitize($content), $replyId]);

    echo json_encode([
        'success' => true,
        'message' => 'Commentaire modifié avec succès'
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
