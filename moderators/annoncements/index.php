<?php 
    require_once('../../config/database.php');

    
    //les annonces en attente
        function get_pending_news_count($db) {
            $pending_news = $db->query("SELECT COUNT(*) FROM annonce WHERE etat_annonce = 'en attente'");
            return $pending_news->fetchColumn();
        }
    
        $number_pending_news = get_pending_news_count($dbase_connected);



    //les annonces pubiés
        function get_published_news_count($db) {
            $published_news = $db->query("SELECT COUNT(*) FROM annonce WHERE etat_annonce = 'publié'");
            return $published_news->fetchColumn();
        }

        $number_published_news = get_published_news_count($dbase_connected);


    //le total
        function get_total_news_count($db) {
            $all_news = $db->query("SELECT COUNT(*) FROM annonce");
            return $all_news->fetchColumn();
        }

        $total_news = get_total_news_count($dbase_connected);
    

    //le statut de publié to en attente
        function deactivate_a_news($db, $id) {
            $action = $db->prepare("UPDATE annonce SET etat_annonce = 'en attente', date_pub_annonce = NOW() WHERE id_annonce = ?");
            return $action->execute([$id]);
        }

    
    //Supprimer une annonce
        function delete_a_news($db, $id) {
            $action = $db->prepare("DELETE FROM annonce WHERE id_annonce = ?");
            return $action->execute([$id]);
        }
    
    //le status de "en attente" to "publié"
    function activate_a_news($db, $id) {
        $requete = $db->prepare("UPDATE annonce SET etat_annonce = 'publié', date_pub_annonce = NOW() WHERE id_annonce = ?");
        return $action->execute([$id]);
    } 
    
    


        