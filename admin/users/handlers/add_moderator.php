<?php
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../utils/matricule.php';

header('Content-Type: application/json');

try {
    if (empty($_POST['firstname']) || empty($_POST['lastname']) || empty($_POST['email'])) {
        throw new Exception('Veuillez remplir tous les champs obligatoires');
    }

    $db = Database::getInstance()->getConnection();
    $db->beginTransaction();

    // Vérifier l'email
    $stmt = $db->prepare("SELECT id_utilisateur FROM utilisateur WHERE email = ?");
    $stmt->execute([$_POST['email']]);
    if ($stmt->fetch()) {
        throw new Exception('Cet email est déjà utilisé');
    }

    // Traitement photo par défaut
    $photoName = 'default.jpg';
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../../../uploads/avatar/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileExtension = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png'];

        if (!in_array($fileExtension, $allowedExtensions)) {
            throw new Exception('Format de fichier non autorisé. Utilisez JPG, JPEG ou PNG.');
        }

        $photoName = uniqid() . '.' . $fileExtension;
        if (!move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $photoName)) {
            throw new Exception('Erreur lors de l\'upload de la photo');
        }
    }

    $stmt = $db->prepare("
        INSERT INTO utilisateur (
            matricule,
            prenom,
            nom,
            email,
            photo_profile,
            mot_de_passe,
            id_role,
            statut_compte,
            date_creation,
            date_mise_a_jour,
            nom_utilisateur
        ) VALUES (?, ?, ?, ?, ?, ?, 
            (SELECT id_role FROM role WHERE nom_role = 'moderateur'),
            'actif',
            NOW(),
            NOW(),
            ?
        )
    ");

    $matricule = generateMatricule('moderateur');
    $password = password_hash('jekiffelalife', PASSWORD_DEFAULT);
    $nomUtilisateur = strtolower($_POST['firstname'] . '.' . $_POST['lastname']);

    $stmt->execute([
        $matricule,
        $_POST['firstname'],
        $_POST['lastname'],
        $_POST['email'],
        $photoName,
        $password,
        $nomUtilisateur
    ]);

    $userId = $db->lastInsertId();

    $stmt = $db->prepare("
        INSERT INTO moderateur (
            id_utilisateur,
            id_publication_forum,
            id_blog
        ) VALUES (?, NULL, NULL)
    ");
    $stmt->execute([$userId]);

    $db->commit();

    echo json_encode([
        'success' => true,
        'message' => "Modérateur ajouté avec succès!\n\n" .
            "Identifiants de connexion :\n" .
            "Matricule : $matricule\n" .
            "Mot de passe : jekiffelalife",
        'user' => [
            'id_utilisateur' => $userId,
            'matricule' => $matricule,
            'prenom' => $_POST['firstname'],
            'nom' => $_POST['lastname'],
            'email' => $_POST['email'],
            'photo_profile' => $photoName,
            'type_utilisateur' => 'moderateur',
            'statut_compte' => 'actif'
        ]
    ]);
} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
