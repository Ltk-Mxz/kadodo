<?php
function assignCoursesToStudent($db, $etudiantId, $filiereId)
{
    try {
        // Récupérer les cours disponibles pour la filière depuis attribution_cours
        $stmt = $db->prepare("
            SELECT DISTINCT c.id_cours 
            FROM cours c
            INNER JOIN attribution_cours ac ON c.id_cours = ac.id_cours
            WHERE ac.id_filiere = ?
        ");
        $stmt->execute([$filiereId]);
        $cours = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($cours)) {
            return [
                'success' => false,
                'message' => 'Aucun cours disponible pour cette filière'
            ];
        }

        // Assigner les cours à l'étudiant
        $stmt = $db->prepare("
            INSERT INTO etudiant_cours (id_etudiant, id_cours, date_attribution) 
            VALUES (?, ?, NOW())
        ");

        foreach ($cours as $cours) {
            $stmt->execute([$etudiantId, $cours['id_cours']]);
        }

        return [
            'success' => true,
            'message' => 'Cours assignés avec succès'
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Erreur lors de l\'attribution des cours: ' . $e->getMessage()
        ];
    }
}
