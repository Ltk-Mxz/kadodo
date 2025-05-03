<?php
session_start();
require_once '../config/database.php';
require_once './handlers/upload_helper.php';
header('Content-Type: application/json');

try {
    $id_user = $_SESSION['user']['id'];
    $updateFields = [];
    $params = [];

    // Only add fields that are actually set in $_POST
    if (isset($_POST['firstname'])) {
        $updateFields[] = "prenom=?";
        $params[] = $_POST['firstname'];
    }
    if (isset($_POST['lastname'])) {
        $updateFields[] = "nom=?";
        $params[] = $_POST['lastname'];
    }
    if (isset($_POST['email'])) {
        $updateFields[] = "email=?";
        $params[] = $_POST['email'];
    }
    if (isset($_POST['tel'])) {
        $updateFields[] = "tel=?";
        $params[] = $_POST['tel'];
    }

    // Gestion de l'upload d'avatar
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $newFileName = handleImageUpload($_FILES['avatar'], $id_user);
        $updateFields[] = "photo_profile=?";
        $params[] = $newFileName;
    }

    // Only proceed with update if there are fields to update
    if (!empty($updateFields)) {
        $db = Database::getInstance()->getConnection();
        $params[] = $id_user; // Ajouter l'ID utilisateur à la fin

        $sql = $db->prepare("UPDATE utilisateur SET " . implode(", ", $updateFields) . " WHERE id_utilisateur=?");
        $reponse = $sql->execute($params);

        if ($reponse) {
            http_response_code(200);
            header("location: /myschoolface/settings/");
            exit();
        } else {
            throw new Exception('Erreur lors de la mise à jour du profil');
        }
    } else {
        throw new Exception('Aucun champ à mettre à jour');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
