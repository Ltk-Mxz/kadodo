<?php
require_once __DIR__ . '/../../../config/database.php';
header('Content-Type: application/json');

try {
    $db = Database::getInstance()->getConnection();

    $stmt = $db->query("
        SELECT t.*, u.matricule, u.prenom, u.nom, u.photo_profile
        FROM transactions t
        INNER JOIN utilisateur u ON t.id_etudiant = u.id_utilisateur
        ORDER BY t.date_paiement DESC
        LIMIT 10
    ");

    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'transactions' => $transactions
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
