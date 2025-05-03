<?php
session_start();
require_once '../../../config/database.php';

header('Content-Type: application/json');

// Vérification de la session admin
if (!isset($_SESSION['user']) || !isset($_SESSION['user']['type']) || $_SESSION['user']['type'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Accès non autorisé']);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['userId']) || !isset($data['action'])) {
        throw new Exception('Données manquantes');
    }

    $db = Database::getInstance()->getConnection();

    $newStatus = $data['action'] === 'validate' ? 'actif' : 'rejete';

    $stmt = $db->prepare("UPDATE utilisateur SET statut_compte = ? WHERE id_utilisateur = ?");
    $success = $stmt->execute([$newStatus, $data['userId']]);

    if (!$success) {
        throw new Exception('Erreur lors de la mise à jour du statut');
    }

    echo json_encode([
        'success' => true,
        'message' => $data['action'] === 'validate' ? 'Compte validé avec succès' : 'Compte rejeté'
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
