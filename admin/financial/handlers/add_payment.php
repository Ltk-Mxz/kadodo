<?php
require_once __DIR__ . '/../../../config/database.php';
header('Content-Type: application/json');

try {
    if (empty($_POST['student_id']) || empty($_POST['amount']) || empty($_POST['payment_type'])) {
        throw new Exception('Tous les champs sont requis');
    }

    $db = Database::getInstance()->getConnection();

    // Vérifier si l'étudiant existe et a le bon rôle
    $stmt = $db->prepare("
        SELECT id_utilisateur FROM utilisateur 
        WHERE id_utilisateur = ? 
        AND id_role = (SELECT id_role FROM role WHERE nom_role = 'etudiant')
    ");
    $stmt->execute([$_POST['student_id']]);

    if (!$stmt->fetch()) {
        throw new Exception('Étudiant non trouvé');
    }

    $stmt = $db->prepare("
        INSERT INTO transactions (
            id_etudiant, 
            montant, 
            type_paiement, 
            statut_paiement,
            date_paiement
        ) VALUES (?, ?, ?, 'paye', NOW())
    ");

    $stmt->execute([
        $_POST['student_id'],
        $_POST['amount'],
        $_POST['payment_type']
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Paiement enregistré avec succès'
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
