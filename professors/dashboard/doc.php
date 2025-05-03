<?php
session_start();
require_once '../../config/database.php';

header('Content-Type: application/json');

try {
    if (!isset($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Veuillez sélectionner un document');
    }

    $id = $_SESSION['user']['id'];
    $db = Database::getInstance()->getConnection();

    $libelle = $_POST['lib'];
    $attr = $_POST['cour'];
    $document = $_FILES['document'];

    // Création du dossier uploads/docs s'il n'existe pas
    $uploadDir = __DIR__ . '/../../uploads/docs/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Vérifie et nettoie les fichiers manquants de la base de données
    $stmt = $db->prepare("
        SELECT c.id_cours, f.id_filiere, p.id_professeur
        FROM professeur p
        JOIN cours c ON p.id_professeur = c.id_professeur
        JOIN attribution_cours ac ON c.id_cours = ac.id_cours
        JOIN filiere f ON ac.id_filiere = f.id_filiere
        WHERE ac.id_attribution = ? AND p.id_utilisateur = ?
    ");
    $stmt->execute([$attr, $id]);
    $info = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$info) {
        throw new Exception('Cours non trouvé ou non autorisé');
    }

    $cleanupStmt = $db->prepare("
        SELECT chemin_doc FROM document 
        WHERE id_professeur = ?
    ");
    $cleanupStmt->execute([$info['id_professeur']]);
    $files = $cleanupStmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($files as $file) {
        if (!file_exists($uploadDir . $file)) {
            // Log le fichier manquant
            error_log("Fichier manquant détecté : " . $file);
        }
    }

    // Gestion du fichier
    $extension = strtolower(pathinfo($document['name'], PATHINFO_EXTENSION));
    $new_filename = 'doc_' . uniqid() . '.' . $extension;
    $destination = $uploadDir . $new_filename;

    if (move_uploaded_file($document['tmp_name'], $destination)) {
        chmod($destination, 0644); // Permissions en lecture seule
    } else {
        throw new Exception("Erreur lors de l'upload du fichier");
    }

    // Insertion dans la base de données
    $stmt = $db->prepare("
        INSERT INTO document (
            lib_doc, chemin_doc, id_filiere, 
            id_cours, id_professeur, date_upload
        ) VALUES (?, ?, ?, ?, ?, NOW())
    ");

    $stmt->execute([
        $libelle,
        $new_filename,
        $info['id_filiere'],
        $info['id_cours'],
        $info['id_professeur']
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Document ajouté avec succès',
        'redirect' => '../dashboard'
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
