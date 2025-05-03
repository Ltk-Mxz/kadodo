<?php

/**
 * Initialiser le dossier uploads si nécessaire
 */
function initializeUploadsDirectory()
{
    $avatarPath = __DIR__ . '/../uploads/avatar/';

    if (!file_exists($avatarPath)) {
        mkdir($avatarPath, 0755, true);
    }

    if (!is_writable($avatarPath)) {
        chmod($avatarPath, 0755);
    }
}

/**
 * Utilitaire pour gérer les photos de profil
 */
function getAvatarPath($photoName)
{
    // Nettoyage du nom de fichier
    $photoName = trim($photoName);

    // Configuration des chemins
    $defaultAvatar = 'default.jpg';
    $uploadsPath = __DIR__ . '/../uploads/avatar/';

    // Copier l'avatar par défaut s'il n'existe pas
    $defaultAvatarPath = $uploadsPath . $defaultAvatar;
    if (!file_exists($defaultAvatarPath)) {
        $defaultSource = __DIR__ . '/../assets/images/default-avatar.jpg';
        if (file_exists($defaultSource)) {
            copy($defaultSource, $defaultAvatarPath);
        }
    }

    // Si aucun nom fourni ou fichier vide
    if (empty($photoName) || $photoName === 'default.jpg') {
        return $defaultAvatar;
    }

    // Vérifier si le fichier existe
    $fullPath = $uploadsPath . $photoName;
    if (file_exists($fullPath) && is_readable($fullPath)) {
        return $photoName;
    }

    // Retourner l'avatar par défaut si le fichier n'existe pas
    return $defaultAvatar;
}

/**
 * Utilitaire pour gérer les photos du blog
 */
function getBlogImagePath($photo)
{
    if (empty($photo) || !file_exists(__DIR__ . '/../uploads/blog/' . $photo)) {
        return 'default-blog.jpg';
    }
    return $photo;
}
