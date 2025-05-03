<?php
require_once '../../../config/database.php';
require_once '../../../utils/matricule.php';
header('Content-Type: application/json');

$response = [];

try {
    // Validation des données
    if (empty($_POST['prenom']) || empty($_POST['nom']) || empty($_POST['email']) || empty($_POST['role'])) {
        throw new Exception('Veuillez remplir tous les champs obligatoires');
    }

    $db = Database::getInstance()->getConnection();
    $db->beginTransaction();

    try {
        // Génération du matricule
        $matricule = generateMatricule($_POST['role']);

        // Insertion de l'utilisateur
        $stmt = $db->prepare("
            INSERT INTO utilisateur (matricule, prenom, nom, email, mot_de_passe, id_role, statut_compte) 
            VALUES (?, ?, ?, ?, ?, (SELECT id_role FROM role WHERE nom_role = ?), 'inactif')
        ");
        $password = password_hash('password123', PASSWORD_DEFAULT);
        $stmt->execute([$matricule, $_POST['prenom'], $_POST['nom'], $_POST['email'], $password, $_POST['role']]);
        $lastInsertId = $db->lastInsertId();

        // Notification pour tous les admins
        $stmt = $db->prepare("
            SELECT id_utilisateur 
            FROM utilisateur u 
            WHERE id_role = (SELECT id_role FROM role WHERE nom_role = 'admin')
        ");
        $stmt->execute();
        $adminIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($adminIds as $adminId) {
            $stmt = $db->prepare("
                INSERT INTO notifications (
                    id_utilisateur,
                    id_destinataire,
                    id_expediteur,
                    message,
                    type,
                    lu
                ) VALUES (?, ?, ?, ?, ?, 0)
            ");

            $message = "Nouvelle inscription en attente : {$_POST['prenom']} {$_POST['nom']} ({$_POST['email']})";
            $stmt->execute([
                $adminId,
                $adminId,
                $lastInsertId,
                $message,
                'inscription_pending'
            ]);
        }

        $db->commit();

        $response = [
            'success' => true,
            'message' => 'Inscription réussie. En attente de validation par un administrateur.',
        ];
    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }
} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }

    http_response_code(400);
    $response = [
        'success' => false,
        'message' => $e->getMessage(),
    ];
}

echo json_encode($response);
