<?php
function handleImageUpload($file, $userId)
{
    $uploadDir = '../uploads/avatar/';
    $maxFileSize = 2 * 1024 * 1024; // 2MB
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg', 'image/jfif'];

    // Vérifier si le dossier existe, sinon le créer
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Vérifier la taille du fichier
    if ($file['size'] > $maxFileSize) {
        throw new Exception('Le fichier est trop volumineux. Maximum 2MB.');
    }

    // Vérifier le type de fichier
    if (!in_array($file['type'], $allowedTypes)) {
        throw new Exception('Type de fichier non autorisé. Utilisez JPG, PNG ou GIF.');
    }

    // Générer un nom unique pour le fichier
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $newFileName = 'avatar_' . $userId . '_' . time() . '.' . $extension;
    $targetPath = $uploadDir . $newFileName;

    // Déplacer le fichier
    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        throw new Exception('Erreur lors du téléchargement du fichier.');
    }

    return $newFileName;
}
