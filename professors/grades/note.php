<?php

session_start();
require_once '../../config/database.php';
header('Content-Type: application/json');

if (!isset($_POST['note']) || !is_array($_POST['note'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Données invalides'
    ]);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    $db->beginTransaction();

    $Semestre = $_SESSION['SaisieNotes']['Semestre'];
    $id_attribution = $_SESSION['SaisieNotes']['filiereCours'];

    // Vérifier si des notes existent déjà
    $checkNotes = $db->prepare("
        SELECT COUNT(*) 
        FROM note n
        JOIN attribution_cours ac ON n.id_cours = ac.id_cours
        WHERE ac.id_attribution = ? 
        AND n.Semestre = ?
    ");
    $checkNotes->execute([$id_attribution, $Semestre]);

    if ($checkNotes->fetchColumn() > 0) {
        throw new Exception('Les notes pour ce semestre ont déjà été saisies');
    }

    // Récupérer l'ID du cours
    $stmt = $db->prepare("
        SELECT ac.id_cours 
        FROM attribution_cours ac 
        WHERE ac.id_attribution = ?
    ");
    $stmt->execute([$id_attribution]);
    $id_cours = $stmt->fetchColumn();

    if (!$id_cours) {
        throw new Exception("Cours non trouvé");
    }

    // Insérer ou mettre à jour les notes
    $stmt = $db->prepare("
        INSERT INTO note (id_etudiant, id_cours, note, Semestre)
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE note = VALUES(note)
    ");

    foreach ($_POST['note'] as $id_etudiant => $note) {
        if (!is_numeric($note) || $note < 0 || $note > 20) {
            throw new Exception("Note invalide pour l'étudiant $id_etudiant");
        }
        $stmt->execute([$id_etudiant, $id_cours, $note, $Semestre]);
    }

    $db->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Notes enregistrées avec succès'
    ]);
} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    error_log($e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
