<?php
require_once '../../config/database.php';
session_start();

header('Content-Type: application/json');

try {
    if (!isset($_SESSION['user']['id'])) {
        throw new Exception('Non autorisé');
    }

    $search = $_GET['q'] ?? '';
    $userId = $_SESSION['user']['id'];

    if (strlen($search) < 2) {
        throw new Exception('Recherche trop courte');
    }

    $db = Database::getInstance()->getConnection();

    $stmt = $db->prepare("
        SELECT u.id_utilisateur, u.nom, u.prenom, u.photo_profile, r.nom_role
        FROM utilisateur u
        JOIN role r ON u.id_role = r.id_role 
        WHERE (u.nom LIKE ? OR u.prenom LIKE ?)
        AND u.id_utilisateur != ?
        AND u.statut_compte = 'actif'
        LIMIT 10
    ");

    $searchTerm = "%$search%";
    $stmt->execute([$searchTerm, $searchTerm, $userId]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $users
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
