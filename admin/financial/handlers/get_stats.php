<?php
require_once __DIR__ . '/../../../config/database.php';
header('Content-Type: application/json');

try {
    $db = Database::getInstance()->getConnection();

    // Montant total collecté
    $stmt = $db->query("
        SELECT SUM(montant) as total_collecte 
        FROM transactions t
        INNER JOIN utilisateur u ON t.id_etudiant = u.id_utilisateur
        WHERE t.statut_paiement = 'paye'
    ");
    $montantCollecte = $stmt->fetch(PDO::FETCH_ASSOC)['total_collecte'] ?? 0;

    // Montant en attente
    $stmt = $db->query("
        SELECT COUNT(*) as nb_attente, SUM(montant) as montant_attente 
        FROM transactions 
        WHERE statut_paiement = 'en_attente'
    ");
    $attente = $stmt->fetch(PDO::FETCH_ASSOC);

    // Étudiants en retard
    $stmt = $db->query("
        SELECT COUNT(DISTINCT id_etudiant) as nb_retard 
        FROM transactions 
        WHERE statut_paiement = 'en_retard'
    ");
    $retard = $stmt->fetch(PDO::FETCH_ASSOC)['nb_retard'];

    echo json_encode([
        'success' => true,
        'data' => [
            'montant_collecte' => $montantCollecte,
            'montant_attente' => $attente['montant_attente'],
            'nb_attente' => $attente['nb_attente'],
            'nb_retard' => $retard
        ]
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
