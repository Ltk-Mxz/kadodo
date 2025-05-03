<?php
    require_once('index.php');
            
    // pour update la db
    function publish_news(PDO $db, int $id): bool {
        try {
            $requete = $db->prepare("UPDATE annonce 
                                 SET etat_annonce = 'publié', 
                                     date_pub_annonce = NOW() 
                                 WHERE id_annonce = ?");
            return $requete->execute([$id]);
        } catch (PDOException $e) {
            error_log("Erreur de publication de l'annonce #$id: " . $e->getMessage());
            return false;
        }
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
        if (isset($_POST['id_annonce']) && ctype_digit($_POST['id_annonce'])) {
            $id_annonce = (int)$_POST['id_annonce'];
            
            if (publish_news($dbase_connected, $id_annonce)) {
                $_SESSION['success'] = "L'annonce a été publiée avec succès";
            } else {
                $_SESSION['error'] = "Erreur lors de la publication";
            }
        } else {
            $_SESSION['error'] = "ID d'annonce invalide";
        }
        
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '../dashboard/index.php'));
        exit;
    }