<?php
require_once '../../../config/database.php';

try {
    $db->beginTransaction();

    // Insérer le professeur
    $stmt = $db->prepare("INSERT INTO professeur (matricule_professeur, specialite, id_utilisateur) VALUES (?, ?, ?)");
    $stmt->execute([$matricule, $specialite, $userId]);

    $id_professeur = $db->lastInsertId();

    // Insérer le cours
    if (!empty($nom_cours)) {
        $stmt = $db->prepare("INSERT INTO cours (nom_cours, id_professeur) VALUES (?, ?)");
        $stmt->execute([$nom_cours, $id_professeur]);

        $id_cours = $db->lastInsertId();

        // Attribution automatique du cours à toutes les filières
        $stmt = $db->prepare("SELECT id_filiere FROM filiere");
        $stmt->execute();
        $filieres = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($filieres as $id_filiere) {
            $stmt = $db->prepare("INSERT INTO attribution_cours (id_cours, id_filiere) VALUES (?, ?)");
            $stmt->execute([$id_cours, $id_filiere]);
        }
    }

    $db->commit();
    echo "Le professeur et le cours ont été ajoutés avec succès.";
} catch (Exception $e) {
    $db->rollBack();
    echo "Erreur : " . $e->getMessage();
}
