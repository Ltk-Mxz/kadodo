<?php
require_once __DIR__ . '/../../../config/database.php';
header('Content-Type: application/json');

try {
    $db = Database::getInstance()->getConnection();

    // Récupération des paramètres
    $search = $_GET['q'] ?? '';
    $role = $_GET['role'] ?? '';
    $status = $_GET['status'] ?? '';

    // Construction de la requête de base
    $query = "SELECT u.id_utilisateur, u.matricule, u.prenom, u.nom, u.email, 
                     u.photo_profile, u.statut_compte, r.nom_role
              FROM utilisateur u
              INNER JOIN role r ON u.id_role = r.id_role
              WHERE 1=1";
    $params = [];

    // Ajout des conditions de recherche
    if (!empty($search)) {
        $query .= " AND (u.prenom LIKE ? OR u.nom LIKE ? OR u.email LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    if (!empty($role)) {
        $query .= " AND r.nom_role = ?";
        $params[] = $role;
    }

    if (!empty($status)) {
        $query .= " AND u.statut_compte = ?";
        $params[] = $status;
    }

    $query .= " ORDER BY u.date_creation DESC";

    // Exécution de la requête
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Compter le nombre total d'utilisateurs
    $countQuery = str_replace("SELECT u.id_utilisateur, u.matricule, u.prenom, u.nom, u.email, 
                     u.photo_profile, u.statut_compte, r.nom_role", "SELECT COUNT(*) as total", $query);
    $stmtCount = $db->prepare($countQuery);
    $stmtCount->execute($params);
    $total = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];

    echo json_encode([
        'success' => true,
        'users' => $users,
        'total' => $total
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la recherche : ' . $e->getMessage()
    ]);
}
