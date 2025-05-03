<?php
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../utils/matricule.php';
require_once __DIR__ . '/../../../utils/assign_courses.php';

header('Content-Type: application/json');

$response = [];

try {
    // Validation des données
    if (empty($_POST['firstname']) || empty($_POST['lastname']) || empty($_POST['email'])) {
        throw new Exception('Veuillez remplir tous les champs obligatoires');
    }

    // Vérifier et créer le dossier avatars s'il n'existe pas
    $uploadDir = __DIR__ . '/../../../uploads/avatar/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Copier l'avatar par défaut s'il n'existe pas déjà
    $defaultAvatar = $uploadDir . 'default.jpg';
    if (!file_exists($defaultAvatar)) {
        copy(__DIR__ . '/../../../assets/images/default-avatar.jpg', $defaultAvatar);
    }

    // Traitement de la photo
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

    $db = Database::getInstance()->getConnection();
    $db->beginTransaction();

    try {
        // Validation initiale des champs niveau et filière
        if (empty($_POST['niveau']) || empty($_POST['filiere'])) {
            throw new Exception('Le niveau et la filière sont requis');
        }

        // Debug log
        error_log("Niveau reçu avant validation: " . $_POST['niveau']);

        // Valider que le niveau existe dans la base de données - MODIFIED
        $stmt = $db->prepare("SELECT id_niveau FROM niveau WHERE id_niveau = ? LIMIT 1");
        $stmt->execute([$_POST['niveau']]);
        $niveauData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$niveauData) {
            throw new Exception('Le niveau sélectionné n\'existe pas');
        }

        $idNiveau = $niveauData['id_niveau']; // Store the validated niveau ID
        error_log("ID Niveau validé: " . $idNiveau);

        // Valider que la filière existe
        $stmt = $db->prepare("SELECT id_filiere FROM filiere WHERE id_filiere = ?");
        $stmt->execute([$_POST['filiere']]);
        if (!$stmt->fetch()) {
            throw new Exception('La filière sélectionnée n\'existe pas');
        }

        // Insertion utilisateur
        $stmt = $db->prepare("
            INSERT INTO utilisateur (matricule, prenom, nom, email, photo_profile, mot_de_passe, id_role, statut_compte) 
            VALUES (?, ?, ?, ?, ?, ?, (SELECT id_role FROM role WHERE nom_role = 'etudiant'), 'actif')
        ");

        $matricule = generateMatricule('etudiant');
        $password = password_hash('jekiffelalife', PASSWORD_DEFAULT);

        $stmt->execute([$matricule, $_POST['firstname'], $_POST['lastname'], $_POST['email'], $photoName, $password]);
        $userId = $db->lastInsertId();

        // Récupérer la classe correspondant à la filière et au niveau
        $stmt = $db->prepare("
            SELECT c.id_classe 
            FROM classe c
            WHERE c.id_filiere = ? AND c.id_niveau = ?
            LIMIT 1
        ");
        $stmt->execute([$_POST['filiere'], $_POST['niveau']]);
        $classeId = $stmt->fetchColumn();

        if (!$classeId) {
            throw new Exception('Classe non trouvée pour cette filière et ce niveau');
        }

        // Insertion étudiant avec id_niveau vérifié - MODIFIED
        $stmt = $db->prepare("
            INSERT INTO etudiant (id_utilisateur, matricule_etudiant, id_classe, id_niveau, id_filiere) 
            VALUES (?, ?, ?, ?, ?)
        ");

        // Debug log before execution
        error_log("Valeurs avant insertion: " . json_encode([
            'userId' => $userId,
            'matricule' => $matricule,
            'classeId' => $classeId,
            'idNiveau' => $idNiveau,
            'filiere' => $_POST['filiere']
        ]));

        $stmt->execute([
            $userId,
            $matricule,
            $classeId,
            $idNiveau,
            $_POST['filiere']
        ]);
        $etudiantId = $db->lastInsertId();

        // Attribution automatique des cours
        $result = assignCoursesToStudent($db, $etudiantId, $_POST['filiere']);
        if (!$result['success']) {
            throw new Exception($result['message']);
        }

        // Mettre à jour les détails de la réponse
        $response = [
            'success' => true,
            'message' => "Étudiant ajouté avec succès!\n\n" .
                "Identifiants de connexion :\n" .
                "Matricule : $matricule\n" .
                "Mot de passe : jekiffelalife\n\n" .
                $result['message'],
            'user' => [
                'id_utilisateur' => $userId,
                'matricule' => $matricule,
                'prenom' => $_POST['firstname'],
                'nom' => $_POST['lastname'],
                'email' => $_POST['email'],
                'photo_profile' => $photoName,
                'type_utilisateur' => 'etudiant',
                'statut_compte' => 'actif'
            ]
        ];

        // Mettre à jour le nombre d'étudiants dans la classe
        $stmt = $db->prepare("
            UPDATE classe 
            SET nombre_etudiants = nombre_etudiants + 1 
            WHERE id_classe = ?
        ");
        $stmt->execute([$classeId]);

        $db->commit();

        echo json_encode($response);
        exit();
    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }
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
