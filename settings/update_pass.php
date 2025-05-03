<?php
session_start();
require_once '../config/database.php';
header('Content-Type: application/json');
error_reporting(0);

$id_user = $_SESSION['user']['id'];
$firstname = $_POST['firstname'];
$lastname = $_POST['lastname'];
$email = $_POST['email'];
$tel = $_POST['tel'];
$img = $_POST['img'];
$Npassword =  password_hash($_POST['new-password'], PASSWORD_DEFAULT);

$db = Database::getInstance()->getConnection();

try {
    $sql = $db->prepare("UPDATE utilisateur SET mot_de_passe=? WHERE id_utilisateur=?");
    $reponse = $sql->execute(
        array($Npassword, $id_user)
    );
    if ($reponse) {
        http_response_code(200);
        header("location: /myschoolface/settings/");
        exit();
    } else {
        throw new Exception('Erreur lors de la mise à jour du profil');
    }
} catch (Exception $e) {
    http_response_code(400);
    exit();
}

//$sql = $db->prepare("UPDATE utilisateur SET mot_de_passe=? WHERE id_utilisateur=?");

//$reponse = $sql->execute(
//    array($Npassword, $id_user)
//);

//header("location: /myschoolface/settings/");
//exit();
