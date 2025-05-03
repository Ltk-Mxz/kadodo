<?php
require_once __DIR__ . '/../config/database.php';

function generateMatricule($type)
{
    $db = Database::getInstance()->getConnection();
    $prefix = match ($type) {
        'etudiant' => 'ETU',
        'professeur' => 'PRF',
        'administrateur' => 'ADM',
        'moderateur' => 'MOD'
    };

    // Compter selon le type d'utilisateur en utilisant id_role
    $roleNom = match ($type) {
        'etudiant' => 'etudiant',
        'professeur' => 'professeur',
        'administrateur' => 'admin',
        'moderateur' => 'moderateur'
    };

    $stmt = $db->prepare("
        SELECT COUNT(*) FROM utilisateur u
        INNER JOIN role r ON u.id_role = r.id_role
        WHERE r.nom_role = ?
    ");

    $stmt->execute([$roleNom]);
    $count = $stmt->fetchColumn();

    return $prefix . '-' . ($count + 1);
}
