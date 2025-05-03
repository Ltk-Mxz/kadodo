<?php
require_once '../../config/database.php';
require_once '../../utils/auth.php';

header('Content-Type: application/json');

try {
    $topicId = (int)($_GET['id'] ?? 0);
    if ($topicId <= 0) {
        throw new Exception('ID de sujet invalide');
    }

    $db = Database::getInstance()->getConnection();

    // Récupérer le sujet avec toutes les informations
    $query = "
        SELECT 
            p.*,
            u.prenom,
            u.nom,
            u.photo_profile,
            c.nom_categorie_forum,
            r.nom_role
        FROM publication_forum p
        LEFT JOIN utilisateur u ON p.id_utilisateur = u.id_utilisateur
        LEFT JOIN categorie_forum c ON p.id_categorie_forum = c.id_categorie_forum
        LEFT JOIN role r ON u.id_role = r.id_role
        WHERE p.id_publication_forum = ?
    ";

    $stmt = $db->prepare($query);
    $stmt->execute([$topicId]);
    $topic = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$topic) {
        throw new Exception('Sujet non trouvé');
    }

    // Récupérer les commentaires avec les infos utilisateur et rôle
    $query = "
        SELECT 
            c.*,
            u.prenom,
            u.nom,
            u.photo_profile,
            r.nom_role
        FROM commentaire c
        LEFT JOIN utilisateur u ON c.id_utilisateur = u.id_utilisateur
        LEFT JOIN role r ON u.id_role = r.id_role
        WHERE c.id_publication_forum = ?
        ORDER BY c.date_creation ASC
    ";

    $stmt = $db->prepare($query);
    $stmt->execute([$topicId]);
    $replies = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'topic' => $topic,
        'replies' => $replies
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
