<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Updated role constants based on new schema
const ROLES = [
    'etudiant',
    'professeur',
    'admin',
    'moderateur'
];

// La définition de ROLE_PATHS restera ici
if (!defined('ROLE_PATHS')) {
    define('ROLE_PATHS', [
        'etudiant' => 'students',
        'professeur' => 'professors',
        'admin' => 'admin',
        'moderateur' => 'moderators'
    ]);
}

// Ajouter les chemins communs autorisés
const COMMON_PATHS = [
    '/forum/',
    '/chats/',
    '/settings/',
    '/blog/',
    '/blog/File_Uploaded_Zone/',
    '/announcements/',
    '/news/',
    '/tests/',
    '/notifications/',
    '/guest/'
];

function isAuthenticated()
{
    return isset($_SESSION['user']) &&
        !empty($_SESSION['user']['id']) &&
        !empty($_SESSION['user']['type']) &&
        in_array($_SESSION['user']['type'], ROLES);
}

function hasRole($allowedRoles)
{
    if (!isAuthenticated()) {
        header('Location: /myschoolface/auth/login');
        exit();
    }

    if (is_string($allowedRoles)) {
        $allowedRoles = [$allowedRoles];
    }

    // Si l'utilisateur n'a pas le rôle requis, redirection vers 403.php
    if (!in_array($_SESSION['user']['type'], $allowedRoles)) {
        header('Location: /myschoolface/403.php');
        exit();
    }

    return true;
}

function requireAuth()
{
    if (!isAuthenticated()) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        header('Location: /myschoolface/auth/login');
        exit();
    }
}

function requireRole($allowedRoles)
{
    requireAuth();

    if (!hasRole($allowedRoles)) {
        header('Location: /myschoolface/403.php');
        exit();
    }
}

function checkUserAccess()
{
    if (!isAuthenticated()) {
        header('Location: /myschoolface/auth/login');
        exit();
    }

    // Récupérer le chemin actuel
    $currentPath = $_SERVER['REQUEST_URI'];
    $userRole = $_SESSION['user']['type'];

    // Vérifier si c'est un chemin commun autorisé
    foreach (COMMON_PATHS as $commonPath) {
        if (str_contains($currentPath, "/myschoolface$commonPath")) {
            return true;
        }
    }

    // Vérifier que l'utilisateur accède à son propre espace
    $allowedPath = ROLE_PATHS[$userRole];

    if (!str_contains($currentPath, "/myschoolface/$allowedPath/")) {
        header('Location: /myschoolface/403.php');
        exit();
    }
}

function generateCSRFToken()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token)
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
