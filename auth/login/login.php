<?php
session_start();
require_once '../../config/database.php';
require_once '../../utils/auth.php';

header('Content-Type: application/json');
error_reporting(0);

try {
    if (empty($_POST['identifiant']) || empty($_POST['password'])) {
        throw new Exception('Veuillez remplir tous les champs');
    }

    $matricule = $_POST['identifiant'];
    $password = $_POST['password'];

    $db = Database::getInstance()->getConnection();

    $stmt = $db->prepare("
        SELECT 
            u.id_utilisateur,
            u.matricule,
            u.mot_de_passe,
            u.statut_compte,
            r.nom_role as type_utilisateur
        FROM utilisateur u
        INNER JOIN role r ON u.id_role = r.id_role
        WHERE u.matricule = ?
    ");

    $stmt->execute([$matricule]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user['mot_de_passe'])) {
        throw new Exception('Identifiants incorrects');
    }

    // Vérifier le statut du compte
    if ($user['statut_compte'] === 'inactif') {
        echo json_encode([
            'success' => false,
            'status' => 'inactif',
            'message' => 'Votre compte est en attente de validation par un administrateur.'
        ]);
        exit;
    } else if ($user['statut_compte'] === 'rejete') {
        echo json_encode([
            'success' => false,
            'status' => 'rejete',
            'message' => 'Votre demande d\'inscription a été rejetée. Veuillez contacter l\'administration pour plus d\'informations.'
        ]);
        exit;
    }

    $_SESSION['user'] = [
        'id' => $user['id_utilisateur'],
        'matricule' => $user['matricule'],
        'type' => $user['type_utilisateur']
    ];

    $redirectUrl = '/myschoolface/' . ROLE_PATHS[$user['type_utilisateur']] . '/dashboard/';

    echo json_encode([
        'success' => true,
        'message' => 'Connexion réussie.',
        'redirect' => $redirectUrl
    ]);
    exit();
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    exit();
}
