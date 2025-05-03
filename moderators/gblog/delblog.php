<?php
        require_once '../config/database.php';

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = "Méthode non autorisée";
            header('Location: blog_manager.php');
            exit;
        }


        // if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['type'], ['modérateur', 'admin'])) {
        //     $_SESSION['error'] = "Accès non autorisé";
        //     header('Location: ../auth/index.php');
        //     exit;
        // }


        if (!isset($_POST['id_publication_blog']) || !ctype_digit($_POST['id_publication_blog'])) {
            $_SESSION['error'] = "ID d'annonce invalide";
            header('Location: blog_manager.php');
            exit;
        }

        $id_publication = (int)$_POST['id_publication_blog'];


        try {
            // Requête pour la suppression
            $requete = $dbase_connected->prepare("DELETE FROM publication_blog WHERE id_publication_blog = ?");
            $requete->execute([$id_publication]);
            
            if ($requete->rowCount() > 0) {
                $_SESSION['success'] = "Annonce supprimée avec succès";
            } else {
                $_SESSION['error'] = "Aucune annonce correspondante trouvée";
            }
        } catch (PDOException $e) {
            error_log("Erreur suppression annonce #$id_publication: " . $e->getMessage());
            $_SESSION['error'] = "Erreur technique lors de la suppression";
        }

        // Redirection avec fallback sécurisé
        header('Location: blog_manager.php');
        exit;

?>