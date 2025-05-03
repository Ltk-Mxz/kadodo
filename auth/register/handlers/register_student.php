<?php
require_once '../../../config/database.php';
require_once '../../../utils/matricule.php';
require_once __DIR__ . '/../../../utils/assign_courses.php';

header('Content-Type: application/json');

try {
    if (
        empty($_POST['firstname']) || empty($_POST['lastname']) ||
        empty($_POST['email']) || empty($_POST['password']) ||
        empty($_POST['confirm_password']) || empty($_POST['filiere']) ||
        empty($_POST['niveau']) ||
        empty($_POST['filiere'])
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
    $matricule = generateMatricule('etudiant');

    // Insérer l'utilisateur avec plus d'informations
    $stmt = $db->prepare("
        INSERT INTO utilisateur (
            matricule, prenom, nom, email, 
            photo_profile, mot_de_passe, 
            id_role, statut_compte, date_creation,
            nom_utilisateur, tel, bio
        ) VALUES (
            ?, ?, ?, ?, ?, ?,
            (SELECT id_role FROM role WHERE nom_role = 'etudiant'),
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

    // Ajout de logs pour debug
    error_log("Filière reçue: " . $_POST['filiere']);
    error_log("Niveau reçu: " . $_POST['niveau']);

    // Vérifions d'abord si la classe existe - MODIFIÉ
    $stmt = $db->prepare("
        SELECT c.id_classe, f.libelle_filiere 
        FROM classe c 
        INNER JOIN filiere f ON c.id_filiere = f.id_filiere
        WHERE c.id_filiere = ? AND c.id_niveau = ?
    ");
    $stmt->execute([$_POST['filiere'], $_POST['niveau']]);
    $classeInfo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$classeInfo) {
        // Récupérer la filière et le niveau
        $stmtFiliere = $db->prepare("SELECT libelle_filiere FROM filiere WHERE id_filiere = ?");
        $stmtFiliere->execute([$_POST['filiere']]);
        $libelle_filiere = $stmtFiliere->fetchColumn();

        $stmtNiveau = $db->prepare("SELECT libelle_niveau FROM niveau WHERE id_niveau = ?");
        $stmtNiveau->execute([$_POST['niveau']]);
        $libelle_niveau = $stmtNiveau->fetchColumn();

        // Construction du nom de la classe
        $nom_classe = $libelle_filiere . ' ' . $libelle_niveau;

        // Si la classe n'existe pas, on la crée
        $stmt = $db->prepare("
            INSERT INTO classe (nom_classe, id_filiere, id_niveau, nombre_etudiants)
            VALUES (?, ?, ?, 1)
        ");
        $stmt->execute([$nom_classe, $_POST['filiere'], $_POST['niveau']]);
        $classeId = $db->lastInsertId();
    } else {
        $classeId = $classeInfo['id_classe'];
        // Mettre à jour le nombre d'étudiants
        $db->prepare("UPDATE classe SET nombre_etudiants = nombre_etudiants + 1 WHERE id_classe = ?")->execute([$classeId]);
    }

    // Plus besoin de récupérer id_niveau puisqu'il est déjà validé dans $_POST['niveau']
    $id_niveau = $_POST['niveau'];

    // Debug pour vérifier la valeur
    error_log("ID Niveau final: " . $id_niveau);

    // Insérer dans la table étudiant avec l'id de la filière et l'id_niveau
    $stmt = $db->prepare("
        INSERT INTO etudiant (
            id_utilisateur, matricule_etudiant, id_classe, id_filiere, id_niveau
        ) VALUES (?, ?, ?, ?, ?)
    ");

    // Debug des valeurs avant insertion
    error_log("Valeurs d'insertion: " . json_encode([
        'userId' => $userId,
        'matricule' => $matricule,
        'classeId' => $classeId,
        'filiere' => $_POST['filiere'],
        'niveau' => $id_niveau
    ]));

    $stmt->execute([$userId, $matricule, $classeId, $_POST['filiere'], $id_niveau]);
    $etudiantId = $db->lastInsertId();

    // Attribution automatique des cours
    $result = assignCoursesToStudent($db, $etudiantId, $_POST['filiere']);
    if (!$result['success']) {
        throw new Exception($result['message']);
    }

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
