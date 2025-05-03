<?php
session_start();
require_once '../../config/database.php';
require_once '../../utils/auth.php';

header('Content-Type: application/json');

try {
    requireRole('professeur');

    $data = json_decode(file_get_contents('php://input'), true);
    if (!isset($data['id'])) {
        throw new Exception('ID du document manquant');
    }

    $db = Database::getInstance()->getConnection();

    // Récupérer les informations du document avant suppression
    $stmt = $db->prepare("
        SELECT d.*, p.id_utilisateur 
        FROM document d 
        JOIN professeur p ON d.id_professeur = p.id_professeur 
        WHERE d.id_doc = ? AND p.id_utilisateur = ?
    ");
    $stmt->execute([$data['id'], $_SESSION['user']['id']]);
    $doc = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$doc) {
        throw new Exception('Document non trouvé ou non autorisé');
    }

    // Essayer de supprimer le fichier physique s'il existe
    $filePath = __DIR__ . '/../../uploads/docs/' . $doc['chemin_doc'];
    if (file_exists($filePath)) {
        try {
            unlink($filePath);
        } catch (Exception $e) {
            error_log("Erreur lors de la suppression du fichier: " . $e->getMessage());
            // Continuer même si le fichier ne peut pas être supprimé
        }
    }

    // Supprimer l'entrée de la base de données dans tous les cas
    $stmt = $db->prepare("DELETE FROM document WHERE id_doc = ?");
    $stmt->execute([$data['id']]);

    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Document supprimé avec succès'
        ]);
    } else {
        throw new Exception('Erreur lors de la suppression du document');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
