<?php
require_once __DIR__ . "/../../../config/database.php";

function get_professor_classes($professor_id)
{
    try {
        $db = Database::getInstance()->getConnection();

        // Récupérer l'id_professeur
        $stmt = $db->prepare("
            SELECT p.id_professeur 
            FROM professeur p 
            INNER JOIN utilisateur u ON p.id_utilisateur = u.id_utilisateur
            WHERE p.id_utilisateur = ?
        ");
        $stmt->execute([$professor_id]);
        $prof = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$prof) {
            error_log("Professeur non trouvé pour l'id_utilisateur: " . $professor_id);
            return [];
        }

        // Récupérer les cours avec les détails de la salle
        $sql = "SELECT DISTINCT
                c.id_cours,
                c.nom_cours,
                c.salle,
                c.nombre_heures,
                s.capacite,
                s.equipements,
                (SELECT COUNT(*) FROM etudiant e WHERE e.id_cours = c.id_cours) as nb_eleves
            FROM cours c
            LEFT JOIN salle s ON c.salle = s.nom_salle 
            WHERE c.id_professeur = ?
            ORDER BY c.nom_cours";

        $stmt = $db->prepare($sql);
        $stmt->execute([$prof['id_professeur']]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erreur SQL dans get_professor_classes: " . $e->getMessage());
        throw new Exception("Erreur lors de la récupération des classes");
    }
}
