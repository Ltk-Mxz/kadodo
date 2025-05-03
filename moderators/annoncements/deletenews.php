<?php
require_once 'index.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Méthode non autorisée";
    header('Location: ../dashboard/index.php');
    exit;
}


// if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['modérateur', 'admin'])) {
//     $_SESSION['error'] = "Accès non autorisé";
//     header('Location: /login.php');
//     exit;
// }


if (!isset($_POST['id_annonce']) || !ctype_digit($_POST['id_annonce'])) {
    $_SESSION['error'] = "ID d'annonce invalide";
    header('Location: ../dashboard/index.php');
    exit;
}

$id_annonce = (int)$_POST['id_annonce'];


try {
    // Requête pour la suppression
    $requete = $dbase_connected->prepare("DELETE FROM annonce WHERE id_annonce = ?");
    $requete->execute([$id_annonce]);
    
    if ($requete->rowCount() > 0) {
        $_SESSION['success'] = "Annonce supprimée avec succès";
    } else {
        $_SESSION['error'] = "Aucune annonce correspondante trouvée";
    }
} catch (PDOException $e) {
    error_log("Erreur suppression annonce #$id_annonce: " . $e->getMessage());
    $_SESSION['error'] = "Erreur technique lors de la suppression";
}

// Redirection avec fallback sécurisé
$redirect_url = $_SERVER['HTTP_REFERER'] ?? '../dashboard/index.php';
header('Location: ' . $redirect_url);
exit;
?>