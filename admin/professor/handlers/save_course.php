<?php
require_once '../../../config/database.php';
header('Content-Type: application/json');

try {
    $db = Database::getInstance()->getConnection();
    $db->beginTransaction();

    $data = [
        'nom_cours' => $_POST['subject'],
        'id_professeur' => $_POST['professor_id'],
        'salle' => $_POST['room'],
        'date_debut' => $_POST['date_debut'],
        'date_fin' => $_POST['date_fin'],
        'nombre_heures' => $_POST['nombre_heures']
    ];

    $stmt = $db->prepare("
        INSERT INTO cours (nom_cours, id_professeur, salle, date_debut, date_fin, nombre_heures) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $data['nom_cours'],
        $data['id_professeur'],
        $data['salle'],
        $data['date_debut'],
        $data['date_fin'],
        $data['nombre_heures']
    ]);

    $courseId = $db->lastInsertId();

    // Récupérer les informations complètes du cours
    $stmt = $db->prepare("
        SELECT c.*, CONCAT(u.nom, ' ', u.prenom) as prof_nom
        FROM cours c
        LEFT JOIN professeur p ON c.id_professeur = p.id_professeur
        LEFT JOIN utilisateur u ON p.id_utilisateur = u.id_utilisateur
        WHERE c.id_cours = ?
    ");
    $stmt->execute([$courseId]);
    $course = $stmt->fetch(PDO::FETCH_ASSOC);

    $db->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Cours ajouté avec succès',
        'course' => $course
    ]);
} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
