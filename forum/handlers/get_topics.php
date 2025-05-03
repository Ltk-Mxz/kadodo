<?php
require_once '../../config/database.php';
require_once '../../utils/auth.php';

// Empêcher l'affichage des erreurs PHP dans la sortie JSON
error_reporting(0);
header('Content-Type: application/json');

try {
    $db = Database::getInstance()->getConnection();
    $categoryId = isset($_GET['category']) ? (int)$_GET['category'] : null;

    // Compter le nombre total de réponses pour la catégorie
    $replyCountQuery = "
        SELECT COUNT(*) as total
        FROM commentaire cm
        JOIN publication_forum pf ON cm.id_publication_forum = pf.id_publication_forum
        WHERE pf.id_categorie_forum = :categoryId
    ";
    $replyStmt = $db->prepare($replyCountQuery);
    $replyStmt->execute(['categoryId' => $categoryId]);
    $totalReplies = $replyStmt->fetch(PDO::FETCH_ASSOC)['total'];

    $query = "
        SELECT 
            p.*,
            u.prenom,
            u.nom,
            u.photo_profile,
            c.nom_categorie_forum,
            r.nom_role,
            (SELECT COUNT(*) FROM commentaire WHERE id_publication_forum = p.id_publication_forum) as nombre_reponses
        FROM publication_forum p
        LEFT JOIN utilisateur u ON p.id_utilisateur = u.id_utilisateur
        LEFT JOIN categorie_forum c ON p.id_categorie_forum = c.id_categorie_forum
        LEFT JOIN role r ON u.id_role = r.id_role
        WHERE p.id_categorie_forum = :categoryId
        GROUP BY p.id_publication_forum
        ORDER BY p.date_creation DESC
    ";

    $stmt = $db->prepare($query);
    $stmt->execute(['categoryId' => $categoryId]);
    $topics = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer le nombre total de sujets pour la catégorie
    $countQuery = "SELECT COUNT(*) as total FROM publication_forum WHERE id_categorie_forum = :categoryId";
    $countStmt = $db->prepare($countQuery);
    $countStmt->execute(['categoryId' => $categoryId]);
    $totalTopics = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Formater les dates pour l'affichage
    foreach ($topics as &$topic) {
        $date = new DateTime($topic['date_creation']);
        $now = new DateTime();
        $interval = $now->diff($date);
        $minutes = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;

        if ($minutes == 0) {
            $topic['date_relative'] = "à l'instant";
        } elseif ($minutes < 60) {
            $topic['date_relative'] = "il y a {$minutes}min";
        } elseif ($minutes < 1440) { // moins de 24h
            $hours = floor($minutes / 60);
            $mins = $minutes % 60;
            $topic['date_relative'] = "il y a {$hours}h" . ($mins > 0 ? " {$mins}min" : "");
        } else {
            $days = floor($minutes / 1440);
            $topic['date_relative'] = "il y a {$days}j";
        }
    }

    echo json_encode([
        'success' => true,
        'topics' => $topics,
        'total_topics' => $totalTopics,
        'total_replies' => $totalReplies
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Une erreur est survenue lors du chargement des sujets'
    ]);
}
