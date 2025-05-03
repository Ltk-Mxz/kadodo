<?php
require_once '../../config/database.php';
require_once '../../utils/auth.php';
require_once '../../utils/sanitize.php';

header('Content-Type: application/json');

try {
    requireAuth();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Méthode non autorisée');
    }

    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $categoryId = (int)($_POST['category'] ?? 0);

    // Validations
    if (strlen($title) < 5) {
        throw new Exception('Le titre doit contenir au moins 5 caractères');
    }

    if (strlen($content) < 20) {
        throw new Exception('Le contenu doit contenir au moins 20 caractères');
    }

    if ($categoryId <= 0) {
        throw new Exception('Catégorie invalide');
    }

    $db = Database::getInstance()->getConnection();

    // Vérifier que la catégorie existe
    $stmt = $db->prepare("SELECT id_categorie_forum FROM categorie_forum WHERE id_categorie_forum = ?");
    $stmt->execute([$categoryId]);
    if (!$stmt->fetch()) {
        throw new Exception('Catégorie invalide');
    }

    // Insertion du sujet avec le bon fuseau horaire
    $stmt = $db->prepare("
        INSERT INTO publication_forum (
            id_utilisateur,
            id_categorie_forum,
            titre,
            contenu,
            date_creation,
            date_mise_a_jour
        ) VALUES (?, ?, ?, ?, NOW(), NOW())
    ");

    $stmt->execute([
        $_SESSION['user']['id'],
        $categoryId,
        sanitize($title),
        sanitize($content)
    ]);

    // Récupérer le nouveau sujet avec toutes les informations
    $newTopicId = $db->lastInsertId();
    $query = "
        SELECT 
            p.*,
            u.prenom,
            u.nom,
            u.photo_profile,
            c.nom_categorie_forum,
            r.nom_role,
            0 as nombre_reponses
        FROM publication_forum p
        LEFT JOIN utilisateur u ON p.id_utilisateur = u.id_utilisateur
        LEFT JOIN categorie_forum c ON p.id_categorie_forum = c.id_categorie_forum
        LEFT JOIN role r ON u.id_role = r.id_role
        WHERE p.id_publication_forum = ?
    ";

    $stmt = $db->prepare($query);
    $stmt->execute([$newTopicId]);
    $newTopic = $stmt->fetch(PDO::FETCH_ASSOC);

    // Forcer la date relative à "à l'instant" pour les nouveaux sujets
    $newTopic['date_relative'] = "à l'instant";
    $newTopic['date_creation'] = date('Y-m-d H:i:s'); // S'assurer que la date est correcte

    echo json_encode([
        'success' => true,
        'message' => 'Sujet créé avec succès',
        'topic' => $newTopic
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
