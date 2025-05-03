<?php
require_once __DIR__ . '/../../../config/database.php';
header('Content-Type: application/json');

try {
    if (!isset($_GET['id'])) {
        throw new Exception('ID utilisateur non fourni');
    }

    $db = Database::getInstance()->getConnection();

    // Récupérer les infos utilisateur
    $stmt = $db->prepare("
        SELECT u.*, 
            e.matricule_etudiant,
            c.nom_classe,
            f.libelle_filiere,
            n.libelle_niveau,
            f.id_filiere,
            n.id_niveau
        FROM utilisateur u
        LEFT JOIN etudiant e ON u.id_utilisateur = e.id_utilisateur
        LEFT JOIN classe c ON e.id_classe = c.id_classe
        LEFT JOIN filiere f ON f.id_filiere = c.id_filiere
        LEFT JOIN niveau n ON n.id_niveau = c.id_niveau
        WHERE u.id_utilisateur = ?
    ");
    $stmt->execute([$_GET['id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception('Utilisateur non trouvé');
    }

    // Récupérer la liste des filières
    $filieres = $db->query("SELECT id_filiere, libelle_filiere FROM filiere ORDER BY libelle_filiere")
        ->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer la liste des niveaux
    $niveaux = $db->query("SELECT id_niveau, libelle_niveau FROM niveau ORDER BY id_niveau")
        ->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'user' => $user,
        'filieres' => $filieres,
        'niveaux' => $niveaux
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
