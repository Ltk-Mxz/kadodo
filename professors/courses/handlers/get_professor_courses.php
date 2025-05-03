<?php
require_once __DIR__ . "/../../../config/database.php";

function get_professor_courses($professor_id)
{
    try {
        $db = Database::getInstance();
        $conn = $db->getConnection();

        // Récupérer l'ID du professeur
        $stmt = $conn->prepare("
            SELECT id_professeur 
            FROM professeur 
            WHERE id_utilisateur = ?
        ");
        $stmt->execute([$professor_id]);
        $prof = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$prof) {
            error_log("Professeur non trouvé pour l'id_utilisateur: " . $professor_id);
            return [];
        }

        // Récupérer les cours avec le nombre d'étudiants
        $sql = "SELECT DISTINCT
                c.id_cours,
                c.nom_cours,
                c.salle,
                c.date_debut,
                c.date_fin,
                c.nombre_heures,
                (SELECT COUNT(*) FROM etudiant e WHERE e.id_cours = c.id_cours) as nombre_etudiants
            FROM cours c
            WHERE c.id_professeur = ?
            ORDER BY c.date_debut DESC";

        $stmt = $conn->prepare($sql);
        $stmt->execute([$prof['id_professeur']]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erreur SQL dans get_professor_courses: " . $e->getMessage());
        throw new Exception("Erreur lors de la récupération des cours");
    }
}
