<?php
require_once '../utils/photo.php';
header('Content-Type: application/json');

try {
    // Vérifier et créer le dossier uploads
    $uploadDir = __DIR__ . '/../uploads/avatar/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Copier l'image par défaut si elle n'existe pas
    $defaultImage = $uploadDir . 'default.jpg';
    if (!file_exists($defaultImage)) {
        copy(__DIR__ . '/../assets/images/default-avatar.jpg', $defaultImage);
    }

    initializeUploadsDirectory();

    if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Aucune photo envoyée ou erreur upload');
    }

    $photo = $_FILES['photo'];
    $extension = strtolower(pathinfo($photo['name'], PATHINFO_EXTENSION));
    $allowedTypes = ['jpg', 'jpeg', 'png'];

    if (!in_array($extension, $allowedTypes)) {
        throw new Exception('Type de fichier non autorisé');
    }

    // Vérifier la taille du fichier (max 2MB)
    if ($photo['size'] > 10 * 1024 * 1024) {
        throw new Exception('Image trop volumineuse (max 2MB)');
    }

    $newFileName = uniqid() . '.' . $extension;
    $uploadPath = $uploadDir . $newFileName;

    if (!move_uploaded_file($photo['tmp_name'], $uploadPath)) {
        error_log("Erreur d'upload: " . error_get_last()['message']);
        throw new Exception('Erreur lors du déplacement du fichier');
    }

    // Définir les permissions du fichier
    chmod($uploadPath, 0644);

    echo json_encode([
        'success' => true,
        'filename' => $newFileName,
        'path' => '/myschoolface/uploads/avatar/' . $newFileName
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
