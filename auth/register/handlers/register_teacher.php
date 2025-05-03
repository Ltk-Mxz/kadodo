<?php
require_once '../../../config/database.php';
require_once '../../../utils/matricule.php';

header('Content-Type: application/json');

try {
    if (
        empty($_POST['firstname']) || empty($_POST['lastname']) ||
        empty($_POST['email']) || empty($_POST['password']) ||
        empty($_POST['confirm_password']) || empty($_POST['specialite'])
    ) {
        throw new Exception('Veuillez remplir tous les champs obligatoires');
    }

    if ($_POST['password'] !== $_POST['confirm_password']) {
        throw new Exception('Les mots de passe ne correspondent pas');
    }

    $db = Database::getInstance()->getConnection();
    $db->beginTransaction();

    // Vérifier si l'email existe déjà
    $stmt = $db->prepare("SELECT id_utilisateur FROM utilisateur WHERE email = ?");
    $stmt->execute([$_POST['email']]);
    if ($stmt->fetch()) {
        throw new Exception('Cet email est déjà utilisé');
    }

    // Traitement de la photo
    $photoName = 'default-avatar.jpg';
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../../../uploads/avatar/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileExtension = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'jfif', 'webp'];

        if (!in_array($fileExtension, $allowedExtensions)) {
            throw new Exception('Format de fichier non autorisé. Utilisez JPG, JPEG ou PNG.');
        }

        $photoName = uniqid() . '.' . $fileExtension;
        $fullPath = $uploadDir . $photoName;

        if (!move_uploaded_file($_FILES['photo']['tmp_name'], $fullPath)) {
            error_log("Erreur upload : " . error_get_last()['message']);
            throw new Exception('Erreur lors de l\'upload de la photo');
        }
    }

    // Générer le matricule
    $matricule = generateMatricule('professeur');

    // Insérer l'utilisateur avec plus d'informations
    $stmt = $db->prepare("
        INSERT INTO utilisateur (
            matricule, prenom, nom, email, 
            photo_profile, mot_de_passe, 
            id_role, statut_compte, date_creation,
            nom_utilisateur, tel, bio
        ) VALUES (
            ?, ?, ?, ?, ?, ?,
            (SELECT id_role FROM role WHERE nom_role = 'professeur'),
            'inactif', NOW(),
            ?, NULL, NULL
        )
    ");

    $nomUtilisateur = strtolower($_POST['firstname'] . '.' . $_POST['lastname']);

    $stmt->execute([
        $matricule,
        $_POST['firstname'],
        $_POST['lastname'],
        $_POST['email'],
        $photoName,
        password_hash($_POST['password'], PASSWORD_DEFAULT),
        $nomUtilisateur
    ]);

    $userId = $db->lastInsertId();

    // Insérer dans la table professeur
    $stmt = $db->prepare("
        INSERT INTO professeur (
            id_utilisateur, matricule_professeur, specialite, id_cours
        ) VALUES (?, ?, ?, 0)
    ");
    $stmt->execute([$userId, $matricule, $_POST['specialite']]);

    $db->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Compte créé avec succès!',
        'credentials' => [
            'matricule' => $matricule,
            'nom' => $_POST['lastname'],
            'prenom' => $_POST['firstname'],
            'statut' => 'En attente de validation par un administrateur'
        ]
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
