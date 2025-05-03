<?php

/**
 * Ce script est le point d'entrée de l'application. Il est appelé pour chaque requête HTTP.
 * On va vérifier si la requête correspond à un fichier existant, et si c'est le cas, on l'inclut.
 * Sinon, on affiche une page d'erreur 404.

 * @author Ltk Mxz
 * @version 1.0
 * @since 2025-03-18
 * @link https://github.com/Ltk-Mxz/kadodo
 * @see https://www.w3schools.com/php/
 * @see https://www.php.net/manual/fr/
 */

$requestUri = $_SERVER['REQUEST_URI'];
$basePath = '/myschoolface/';

// Redirection vers la page de connexion si on est à la racine
if ($requestUri === $basePath || $requestUri === $basePath . 'index.php') {
    header('Location: auth/login/');
    exit();
}

// On remplacera le chemin de la requête par le chemin du fichier demandé
// Exemple: /myschoolface/auth/index.php -> /var/www/html/myschoolface/auth/index.php

$requestedFile = __DIR__ . str_replace($basePath, '/', $requestUri);

// Vérifier si le fichier demandé existe
if (!file_exists($requestedFile)) {
    include '404.php';
    exit();
}

require_once 'utils/auth.php';

// Rediriger vers la page appropriée selon le rôle
if (isAuthenticated()) {
    $redirectUrl = match ($_SESSION['user']['type']) {
        'etudiant' => '/myschoolface/students/dashboard/',
        'professeur' => '/myschoolface/professors/dashboard/',
        'administrateur' => '/myschoolface/admin/dashboard/',
        'moderateur' => '/myschoolface/moderators/dashboard/',
        default => '/myschoolface/'
    };
    header("Location: $redirectUrl");
    exit();
} else {
    header('Location: /myschoolface/auth/login');
    exit();
}

/*

$_SERVER['REQUEST_URI']
contient la partie de l'URL demandée après le nom de domaine. 
Par exemple, pour http://exemple.com/myschoolface/page.php?id=1, $_SERVER['REQUEST_URI'] 
vaut /myschoolface/page.php?id=1. Ça inclut le chemin et les paramètres.

__DIR__
contient le chemin absolu du répertoire où se trouve le fichier script en cours d'exécution, 
sans inclure le nom du fichier lui-même. Par exemple, si le script est dans /var/www/myschoolface/index.php,
__DIR__ vaut /var/www/myschoolface.

$requestedFile = __DIR__ . str_replace($basePath, '/', $requestUri);
crée un chemin de fichier local en combinant __DIR__ (répertoire actuel du script,
ex. /var/www/myschoolface) avec $requestUri (ex. /myschoolface/test.php), 
où $basePath (ex. /myschoolface) est remplacé par /. 
Résultat : $requestedFile = /var/www/myschoolface/test.php.

*/