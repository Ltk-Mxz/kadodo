<?php
require_once '../../../config/database.php';
header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['professor_id']) || !isset($data['filiere_id'])) {
        throw new Exception('Données manquantes');
    }

    $db = Database::getInstance()->getConnection();
    $db->beginTransaction();

    // Récupérer tous les cours du professeur
    $stmt = $db->prepare("SELECT id_cours FROM cours WHERE id_professeur = ?");
    $stmt->execute([$data['professor_id']]);
    $cours = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($cours)) {
        throw new Exception('Ce professeur n\'a pas de cours assignés');
    }

    // Vérifier si la filière existe
    $stmt = $db->prepare("SELECT COUNT(*) FROM filiere WHERE id_filiere = ?");
    $stmt->execute([$data['filiere_id']]);
    if ($stmt->fetchColumn() == 0) {
        throw new Exception('Cette filière n\'existe pas');
    }

    $attributionsReussies = 0;

    // Pour chaque cours du professeur
    foreach ($cours as $id_cours) {
        // Vérifier si l'attribution existe déjà
        $stmt = $db->prepare("SELECT COUNT(*) FROM attribution_cours WHERE id_cours = ? AND id_filiere = ?");
        $stmt->execute([$id_cours, $data['filiere_id']]);

        if ($stmt->fetchColumn() == 0) {
            // Créer l'attribution si elle n'existe pas
            $stmt = $db->prepare("INSERT INTO attribution_cours (id_cours, id_filiere) VALUES (?, ?)");
            $stmt->execute([$id_cours, $data['filiere_id']]);
            $attributionsReussies++;
        }
    }

    if ($attributionsReussies == 0) {
        throw new Exception('Les cours de ce professeur sont déjà attribués à cette filière');
    }

    $db->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Attribution réussie : ' . $attributionsReussies . ' cours attribués à la filière'
    ]);
} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
