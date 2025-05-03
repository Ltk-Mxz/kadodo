<?php
/* * Constants for the application
 * These constants are used throughout the application for various purposes.
 */

// * Constants for roles
define('BASE_PATH', '/myschoolface');
define('ASSETS_PATH', BASE_PATH . '/assets');
define('COMPONENTS_PATH', BASE_PATH . '/components');
define('UPLOADS_PATH', BASE_PATH . '/uploads');
define('AVATAR_PATH', UPLOADS_PATH . '/avatar');
define('BLOG_PATH', UPLOADS_PATH . '/blog');
define('DEFAULT_AVATAR', AVATAR_PATH . '/default-avatar.jpg');
define('DEFAULT_BLOG_IMAGE', BLOG_PATH . '/default-blog.jpg');
define('LOGO_PATH', ASSETS_PATH . '/images/logo.png');
define('CSS_PATH', ASSETS_PATH . '/css');
define('JS_PATH', ASSETS_PATH . '/js');

// Chemins des dashboards par rôle
/*
define('ROLE_PATHS', [
    'etudiant' => 'students',
    'professeur' => 'professors',
    'admin' => 'admin',
    'moderateur' => 'moderators'
]);
*/
