<?php
// Configuration de la base de données
$host = 'localhost';
$dbname = 'kadodo_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $departement = $_POST['departement'] ?? '';

    // Validation
    $errors = [];
    if (empty($nom)) $errors[] = "Le nom est requis";
    if (empty($prenom)) $errors[] = "Le prénom est requis";
    if (empty($email)) $errors[] = "L'email est requis";
    if (empty($password)) $errors[] = "Le mot de passe est requis";
    if (empty($departement)) $errors[] = "Le département est requis";

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // Générer matricule unique ADM-X
            $stmt = $pdo->query("SELECT MAX(CAST(SUBSTRING(matricule, 5) AS SIGNED)) as max_num FROM utilisateur WHERE matricule LIKE 'ADM-%'");
            $result = $stmt->fetch();
            $next_num = ($result['max_num'] ?? 0) + 1;
            $matricule = "ADM-" . $next_num;

            // Insérer utilisateur
            $stmt = $pdo->prepare("INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, id_role, matricule, date_creation) VALUES (?, ?, ?, ?, 3, ?, NOW())");
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt->execute([$nom, $prenom, $email, $hashed_password, $matricule]);

            $id_utilisateur = $pdo->lastInsertId();

            // Insérer administrateur
            $stmt = $pdo->prepare("INSERT INTO administrateur (id_utilisateur, departement_admin) VALUES (?, ?)");
            $stmt->execute([$id_utilisateur, $departement]);

            $pdo->commit();
            echo "<div style='color: green;'>Administrateur créé avec succès! Matricule: $matricule</div>";
        } catch (Exception $e) {
            $pdo->rollBack();
            echo "<div style='color: red;'>Erreur: " . $e->getMessage() . "</div>";
        }
    } else {
        foreach ($errors as $error) {
            echo "<div style='color: red;'>$error</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Créer un administrateur</title>
    <style>
        body {
            font-family: Arial;
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
        }

        input,
        select {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
        }

        button {
            background: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background: #45a049;
        }
    </style>
</head>

<body>
    <h2>Créer un nouvel administrateur</h2>
    <form method="POST">
        <div class="form-group">
            <label>Nom:</label>
            <input type="text" name="nom" required>
        </div>
        <div class="form-group">
            <label>Prénom:</label>
            <input type="text" name="prenom" required>
        </div>
        <div class="form-group">
            <label>Email:</label>
            <input type="email" name="email" required>
        </div>
        <div class="form-group">
            <label>Mot de passe:</label>
            <input type="password" name="password" required>
        </div>
        <div class="form-group">
            <label>Département:</label>
            <select name="departement" required>
                <option value="">Sélectionner un département</option>
                <option value="SYSTEM">SYSTEM</option>
                <option value="SCOLARITE">SCOLARITE</option>
                <option value="COMPTABILITE">COMPTABILITE</option>
                <option value="DIRECTION">DIRECTION</option>
            </select>
        </div>
        <button type="submit">Créer l'administrateur</button>
        <a href="/myschoolface">Se connecter</a>
    </form>
</body>

</html>