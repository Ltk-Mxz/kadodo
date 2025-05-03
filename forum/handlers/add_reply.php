<?php
require_once '../../config/database.php';
require_once '../../utils/auth.php';
require_once '../../utils/sanitize.php';

header('Content-Type: application/json');

try {
    requireAuth();

    $userId = $_SESSION['user']['id'];
    $topicId = (int)($_POST['topic_id'] ?? 0);
    $content = trim($_POST['content'] ?? '');

    if ($topicId <= 0) {
        throw new Exception('ID de sujet invalide');
    }

    if (empty($content)) {
        throw new Exception('Le contenu ne peut pas être vide');
    }

    $db = Database::getInstance()->getConnection();

    // Vérifier si le sujet existe
    $stmt = $db->prepare("SELECT id_publication_forum FROM publication_forum WHERE id_publication_forum = ?");
    $stmt->execute([$topicId]);
    if (!$stmt->fetch()) {
        throw new Exception('Sujet non trouvé');
    }

    // Insérer le commentaire
    $stmt = $db->prepare("
        INSERT INTO commentaire (
            id_utilisateur,
            id_publication_forum,
            contenu,
            date_creation
        ) VALUES (?, ?, ?, NOW())
    ");

    $stmt->execute([
        $userId,
        $topicId,
        sanitize($content)
    ]);

    $commentId = $db->lastInsertId();

    // Récupérer le commentaire avec les informations de l'utilisateur
    $stmt = $db->prepare("
        SELECT 
            c.*,
            u.prenom,
            u.nom,
            u.photo_profile,
            r.nom_role
        FROM commentaire c
        LEFT JOIN utilisateur u ON c.id_utilisateur = u.id_utilisateur
        LEFT JOIN role r ON u.id_role = r.id_role
        WHERE c.id_commentaire = ?
    ");

    $stmt->execute([$commentId]);
    $reply = $stmt->fetch(PDO::FETCH_ASSOC);

    // Compter le nombre total de réponses
    $stmt = $db->prepare("
        SELECT COUNT(*) as total 
        FROM commentaire 
        WHERE id_publication_forum = ?
    ");
    $stmt->execute([$topicId]);
    $totalReplies = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    echo json_encode([
        'success' => true,
        'message' => 'Réponse ajoutée avec succès',
        'reply' => $reply,
        'total_replies' => $totalReplies
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
