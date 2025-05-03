<?php
require_once '../../../config/database.php';
header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID non fourni']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();

    $query = "SELECT e.*, p.id_professeur, c.id_cours, c.nom_cours
              FROM emploi_du_temps e
              LEFT JOIN professeur p ON e.id_professeur = p.id_professeur
              LEFT JOIN cours c ON e.id_cours = c.id_cours
              WHERE e.id_emploi_de_temps = ?";

    $stmt = $db->prepare($query);
    $stmt->execute([$_GET['id']]);
    $schedule = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($schedule) {
        echo json_encode(['success' => true, 'schedule' => $schedule]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Emploi du temps non trouvé']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
