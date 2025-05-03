<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../utils/auth.php';
require_once __DIR__ . '/../../utils/photo.php';
require_once __DIR__ . '/../../config/constants.php';

requireAuth();
checkUserAccess();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define the root path
define('ROOT_PATH', dirname(dirname(__DIR__)));

// Update require statements with absolute paths
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/utils/auth.php';
require_once ROOT_PATH . '/utils/photo.php';
require_once ROOT_PATH . '/config/constants.php';

requireAuth();
checkUserAccess();

// Protection contre le clickjacking
header('X-Frame-Options: DENY');
// Protection contre les injections MIME
header('X-Content-Type-Options: nosniff');
// Protection XSS
header('X-XSS-Protection: 1; mode=block');

// Régénérer l'ID de session périodiquement
if (!isset($_SESSION['last_regeneration']) || time() - $_SESSION['last_regeneration'] >= 3600) {
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}

// Vérifier l'existence du dossier avatar et de l'image par défaut
$uploadDir = __DIR__ . '/../../uploads/avatar/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$defaultAvatar = $uploadDir . 'default.jpg';
if (!file_exists($defaultAvatar)) {
    copy(__DIR__ . '/../../assets/images/default-avatar.jpg', $defaultAvatar);
}

// Vérifier l'existence du dossier blog et de l'image par défaut
$blogUploadDir = __DIR__ . '/../../uploads/blog/';
if (!is_dir($blogUploadDir)) {
    mkdir($blogUploadDir, 0777, true);
}

$defaultBlog = $blogUploadDir . 'default-blog.jpg';
if (!file_exists($defaultBlog)) {
    copy(__DIR__ . '/../../assets/images/default-blog.jpg', $defaultBlog);
}

// Récupérer les informations de l'utilisateur
$db = Database::getInstance()->getConnection();

// Initialiser les notifications non lues
$unreadNotifications = 0;
if (isset($_SESSION['user']['id'])) {
    $notificationsQuery = $db->prepare(
        "
        SELECT COUNT(*) as count 
        FROM notifications 
        WHERE id_destinataire = ? AND lu = 0"
    );
    $notificationsQuery->execute([$_SESSION['user']['id']]);
    $result = $notificationsQuery->fetch(PDO::FETCH_ASSOC);
    $unreadNotifications = $result['count'];
}

$stmt = $db->prepare("
    SELECT u.id_utilisateur, u.matricule, u.prenom, u.nom, u.email, r.nom_role as type_utilisateur, u.photo_profile 
    FROM utilisateur u 
    INNER JOIN role r ON u.id_role = r.id_role
    WHERE u.id_utilisateur = ?
");

$stmt->execute([$_SESSION['user']['id']]);
$userInfo = $stmt->fetch(PDO::FETCH_ASSOC);

// Définir un titre par défaut si aucun n'est spécifié
$pageTitle = $pageTitle ?? 'Tableau de bord';
