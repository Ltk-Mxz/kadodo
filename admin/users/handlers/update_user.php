<?php
require_once __DIR__ . '/../../../config/database.php';
header('Content-Type: application/json');

try {
    if (empty($_POST['user_id'])) {
        throw new Exception('ID utilisateur manquant');
    }

    $db = Database::getInstance()->getConnection();
    $db->beginTransaction();

    // Traitement de la photo si une nouvelle est uploadée
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../../../uploads/avatar/';
        $fileExtension = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png'];

        if (!in_array($fileExtension, $allowedExtensions)) {
            throw new Exception('Format de fichier non autorisé');
        }

        $photoName = uniqid() . '.' . $fileExtension;
        move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $photoName);

        // Mettre à jour la photo dans la base de données
        $stmt = $db->prepare("UPDATE utilisateur SET photo_profile = ? WHERE id_utilisateur = ?");
        $stmt->execute([$photoName, $_POST['user_id']]);
    }

    // Mise à jour des informations de base
    $stmt = $db->prepare("
        UPDATE utilisateur 
        SET prenom = ?, nom = ?, email = ?
        WHERE id_utilisateur = ?
    ");
    $stmt->execute([
        $_POST['firstname'],
        $_POST['lastname'],
        $_POST['email'],
        $_POST['user_id']
    ]);

    // Mise à jour des informations spécifiques selon le type
    switch ($_POST['user_type']) {
        case 'etudiant':
            $stmt = $db->prepare("
                UPDATE etudiant 
                SET matricule_etudiant = ?
                WHERE id_utilisateur = ?
            ");
            $stmt->execute([$matricule, $_POST['user_id']]);
            break;
    }

    $db->commit();
    echo json_encode([
        'success' => true,
        'message' => 'Utilisateur mis à jour avec succès',
        'photo' => isset($photoName) ? $photoName : null
    ]);
} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
