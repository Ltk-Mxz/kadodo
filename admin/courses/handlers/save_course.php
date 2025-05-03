<?php
require_once '../../../config/database.php';
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Méthode non autorisée');
    }

    $db = Database::getInstance()->getConnection();
    $db->beginTransaction();

    $data = [
        'nom_cours' => $_POST['nom_cours'],
        'id_professeur' => $_POST['id_professeur'],
        'salle' => $_POST['salle'],
        'nombre_heures' => $_POST['nombre_heures'] ?? 2
    ];

    $filieres = json_decode($_POST['filieres'], true);

    if (empty($_POST['id_cours'])) {
        // Nouveau cours
        $stmt = $db->prepare("INSERT INTO cours (nom_cours, id_professeur, salle, nombre_heures) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $data['nom_cours'],
            $data['id_professeur'],
            $data['salle'],
            $data['nombre_heures']
        ]);
        $courseId = $db->lastInsertId();
        $message = 'Cours ajouté avec succès';
    } else {
        // Mise à jour du cours existant
        $courseId = $_POST['id_cours'];

        // Mise à jour
        $stmt = $db->prepare("UPDATE cours SET nom_cours = ?, id_professeur = ?, salle = ?, date_debut = ?, date_fin = ?, nombre_heures = ? WHERE id_cours = ?");
        $stmt->execute([
            $data['nom_cours'],
            $data['id_professeur'],
            $data['salle'],
            $data['nombre_heures'],
            $courseId
        ]);
    }

    // Supprimer les anciennes relations
    $stmt = $db->prepare("DELETE FROM attribution_cours WHERE id_cours = ?");
    $stmt->execute([$courseId]);

    // Ajouter les nouvelles relations avec les filières
    if (!empty($filieres)) {
        $stmt = $db->prepare("INSERT INTO attribution_cours (id_cours, id_filiere) VALUES (?, ?)");
        foreach ($filieres as $filiereId) {
            $stmt->execute([$courseId, $filiereId]);
        }
    }

    $db->commit();
    echo json_encode(['success' => true, 'message' => $message]);
} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    error_log("Error in save_course.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
