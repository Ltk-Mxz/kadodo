<?php  
session_start();
require_once('../../config/database.php');

// authenticité
if (!isset($_SESSION['user']) || $_SESSION['user']['type'] !== 'moderateur') {
    header('Location: ../../auth/login.php');
    exit;
}
$errors = [];
$titre = htmlspecialchars_decode(trim($_POST['titre'] ?? ''));
$contenu = htmlspecialchars_decode(trim($_POST['contenu'] ?? ''));
$etat = in_array($_POST['etat'] ?? '', ['publié', 'en attente']) ? $_POST['etat'] : 'en attente';
$id_moderateur = (int)($_POST['id_moderateur'] ?? 0);
// controle des champs
if (empty($titre)) $errors[] = "Le titre est obligatoire.";
if (strlen($titre) > 100) $errors[] = "Le titre doit faire moins de 100 caractères.";
if (empty($contenu)) $errors[] = "Le contenu est obligatoire.";
if ($id_moderateur <= 0) $errors[] = "ID modérateur invalide";

//connexion à la base de données
if (!isset($dbase_connected)) {
    $errors[] = "Erreur de connexion à la base de données.";
}

// Vérification que le modérateur existe
if (empty($errors)) {
    $check_mod = $dbase_connected->prepare("SELECT id_moderateur FROM moderateur WHERE id_moderateur = ?");
    $check_mod->execute([$id_moderateur]);
    if (!$check_mod->fetch()) {
        $errors[] = "Modérateur non valide";
    }
}

if (empty($errors)) {
    try {
        $query = "INSERT INTO annonce (titre_annonce, contenu_annonce, etat_annonce, date_pub_annonce, id_moderateur) 
                  VALUES (:titre, :contenu, :etat, NOW(), :id_moderateur)";
        $stmt = $dbase_connected->prepare($query);
        $stmt->execute([
            ':id_moderateur' => $id_moderateur,
            ':contenu' => $contenu,
            ':etat' => $etat,
            ':titre' => $titre
        ]);

        $_SESSION['success'] = "Annonce créée avec succès !";
        header('Location: ../dashboard/index.php');
        exit;

    } catch (PDOException $e) {
        $errors[] = "Erreur technique : " . $e->getMessage();
    }
}

// Stocker les erreurs et rediriger
$_SESSION['form_errors'] = $errors;
$_SESSION['form_data'] = $_POST;
header('Location: addnews.php');
exit;
?>