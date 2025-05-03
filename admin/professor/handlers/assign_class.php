<?php

require_once __DIR__ . '/../../../config/database.php';
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Méthode non autorisée');
    }

    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data) {
        throw new Exception('Données invalides');
    }

    // Validation des données
    if (empty($data['professor_id'])) {
        throw new Exception('Professeur requis');
    }
    if (empty($data['class_id'])) {
        throw new Exception('Classe requise');
    }
    if (empty($data['subject'])) {
        throw new Exception('Matière requise');
    }
    if (empty($data['room'])) {
        throw new Exception('Salle requise');
    }
    if (empty($data['date_debut'])) {
        throw new Exception('Date de début requise');
    }
    if (empty($data['date_fin'])) {
        throw new Exception('Date de fin requise');
    }
    if (empty($data['nombre_heures'])) {
        throw new Exception('Nombre d\'heures requis');
    }

    $db = Database::getInstance()->getConnection();
    $db->beginTransaction();

    // Vérifier si l'attribution existe déjà
    $stmt = $db->prepare("
        SELECT COUNT(*) FROM cours 
        WHERE id_professeur = ? AND id_classe = ? AND nom_cours = ?
    ");
    $stmt->execute([$data['professor_id'], $data['class_id'], $data['subject']]);

    if ($stmt->fetchColumn() > 0) {
        throw new Exception('Cette attribution existe déjà');
    }

    // Récupérer l'id_professeur depuis la table professeur
    $stmt = $db->prepare("
        SELECT p.id_professeur 
        FROM professeur p 
        INNER JOIN utilisateur u ON p.id_utilisateur = u.id_utilisateur
        WHERE p.id_utilisateur = ?
    ");
    $stmt->execute([$data['professor_id']]);
    $prof = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$prof) {
        throw new Exception('Professeur non trouvé');
    }

    // Insérer la nouvelle attribution
    $stmt = $db->prepare("
        INSERT INTO cours (id_professeur, id_classe, nom_cours, salle, date_debut, date_fin, nombre_heures) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $prof['id_professeur'],
        $data['class_id'],
        $data['subject'],
        $data['room'],
        $data['date_debut'],
        $data['date_fin'],
        $data['nombre_heures']
    ]);

    // Mettre à jour la classe avec l'id du professeur
    $stmt = $db->prepare("
        UPDATE classe 
        SET id_professeur = ? 
        WHERE id_classe = ?
    ");

    $stmt->execute([$prof['id_professeur'], $data['class_id']]);

    $db->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Attribution effectuée avec succès'
    ]);
} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    error_log("Error in assign_class.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
