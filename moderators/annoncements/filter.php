<?php
    require_once('../../config/database.php');
    $where = [];
    $params = [];

    // Filtre par état
    if (!empty($_POST['etat'])) {
        $where[] = "etat_annonce = ?";
        $params[] = $_POST['etat'];
    }

    // Filtre par date
    if (!empty($_POST['date'])) {
        switch ($_POST['date']) {
            case 'today':
                $where[] = "DATE(date_pub_annonce) = CURDATE()";
                break;
            case 'week':
                $where[] = "YEARWEEK(date_pub_annonce) = YEARWEEK(NOW())";
                break;
            case 'month':
                $where[] = "MONTH(date_pub_annonce) = MONTH(NOW()) AND YEAR(date_pub_annonce) = YEAR(NOW())";
                break;
            case 'year':
                $where[] = "YEAR(date_pub_annonce) = YEAR(NOW())";
                break;
        }
    }

    // Filtre par recherche texte
    if (!empty($_GET['search'])) {
        $where[] = "(titre_annonce LIKE ? OR contenu_annonce LIKE ?)";
        $searchTerm = '%' . $_POST['search'] . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    // la requête
    $sql = "SELECT * FROM annonce";
    if (!empty($where)) {
        $sql .= " WHERE " . implode(" AND ", $where);
    }
    $sql .= " ORDER BY date_pub_annonce DESC";

    $requete = $dbase_connected->prepare($sql);
    $requete->execute($params);
    $annonces = $requete->fetchAll(PDO::FETCH_ASSOC);
        