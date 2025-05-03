<?php
require_once '../../../config/database.php';
header('Content-Type: application/json');

try {
    if (empty($_POST['libelle_filiere'])) {
        throw new Exception('Le nom de la filière est requis');
    }

    $db = Database::getInstance()->getConnection();
    $id_filiere = $_POST['id_filiere'] ?? null;

    if ($id_filiere) {
        $stmt = $db->prepare("UPDATE filiere SET libelle_filiere = ? WHERE id_filiere = ?");
        $stmt->execute([$_POST['libelle_filiere'], $id_filiere]);
        $message = "Filière mise à jour avec succès";
    } else {
        $stmt = $db->prepare("INSERT INTO filiere (libelle_filiere) VALUES (?)");
        $stmt->execute([$_POST['libelle_filiere']]);
        $id_filiere = $db->lastInsertId();
        $message = "Filière créée avec succès";
    }

    echo json_encode([
        'success' => true,
        'message' => $message,
        'id_filiere' => $id_filiere
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
