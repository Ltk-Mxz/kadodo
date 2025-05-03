#!/usr/bin/env php
<?php

// Configuration BD
$host = 'localhost';
$dbname = 'kadodo_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion: " . $e->getMessage() . "\n");
}

// Fonctions helpers
function prompt($message)
{
    echo $message;
    return trim(fgets(STDIN));
}

function validateEmail($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Interface CLI
echo "=== Création d'un nouvel administrateur ===\n\n";

$nom = prompt("Nom: ");
while (empty($nom)) {
    echo "Le nom est requis!\n";
    $nom = prompt("Nom: ");
}

$prenom = prompt("Prénom: ");
while (empty($prenom)) {
    echo "Le prénom est requis!\n";
    $prenom = prompt("Prénom: ");
}

$email = prompt("Email: ");
while (!validateEmail($email)) {
    echo "Email invalide!\n";
    $email = prompt("Email: ");
}

$password = prompt("Mot de passe: ");
while (strlen($password) < 6) {
    echo "Le mot de passe doit contenir au moins 6 caractères!\n";
    $password = prompt("Mot de passe: ");
}

echo "\nDépartements disponibles:\n";
echo "1) SYSTEM\n";
echo "2) SCOLARITE\n";
echo "3) COMPTABILITE\n";
echo "4) DIRECTION\n";

$dept_choice = prompt("Choisir le département (1-4): ");
$departments = ['SYSTEM', 'SCOLARITE', 'COMPTABILITE', 'DIRECTION'];
while (!isset($departments[$dept_choice - 1])) {
    echo "Choix invalide!\n";
    $dept_choice = prompt("Choisir le département (1-4): ");
}
$departement = $departments[$dept_choice - 1];

try {
    $pdo->beginTransaction();

    // Générer matricule unique
    $stmt = $pdo->query("SELECT MAX(CAST(SUBSTRING(matricule, 5) AS SIGNED)) as max_num FROM utilisateur WHERE matricule LIKE 'ADM-%'");
    $result = $stmt->fetch();
    $next_num = ($result['max_num'] ?? 0) + 1;
    $matricule = "ADM-" . $next_num;

    // Insérer utilisateur
    $stmt = $pdo->prepare("INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, id_role, matricule, date_creation, date_mise_a_jour) VALUES (?, ?, ?, ?, 3, ?, NOW(), NOW())");
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt->execute([$nom, $prenom, $email, $hashed_password, $matricule]);

    $id_utilisateur = $pdo->lastInsertId();

    // Insérer administrateur
    $stmt = $pdo->prepare("INSERT INTO administrateur (id_utilisateur, departement_admin) VALUES (?, ?)");
    $stmt->execute([$id_utilisateur, $departement]);

    $pdo->commit();

    echo "\n=== Admin créé avec succès! ===\n";
    echo "Matricule: $matricule\n";
    echo "Nom: $nom $prenom\n";
    echo "Email: $email\n";
    echo "Département: $departement\n";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "\nErreur: " . $e->getMessage() . "\n";
}
