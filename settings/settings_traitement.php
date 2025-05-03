<?php
    include '../connexion/connexiondb.php';
    $Id=$_POST['Id'];
    $sql="SELECT * FROM utilisateurs WHERE id_utilisateur='$Id';";
    $resultat=mysqli_query($con,$sql);
    $row=mysqli_fetch_assoc($resultat);
    //$profile_photo_default='../../assets/images/utilisateur.png';
   // $profile_photo=$_FILES['photo-upload'];
    // if(isset($_FILES['photo-upload'])){
    //     $profile_photo=$_FILES['photo-upload'];
    //     }
?>
<?php
try {
// un chemin par default pour les images de profil
$chemin_fichier = 'C:'. DIRECTORY_SEPARATOR .'xampp'. DIRECTORY_SEPARATOR .'htdocs'. DIRECTORY_SEPARATOR .'myschoolface'. DIRECTORY_SEPARATOR .'uploads'. DIRECTORY_SEPARATOR .'avatar'. DIRECTORY_SEPARATOR."utilisateur.png";
// traitement et enregistrement de la photo
if(isset($_FILES['photo-upload'])&& $_FILES['photo-upload']['error'] !== UPLOAD_ERR_NO_FILE){
    $profile_photo=$_FILES['photo-upload'];
   // print_r($profile_photo);
    // taille de l'image
    if ($profile_photo['size'] > 2000000) {
        throw new Exeception ("image trop lourde");
    }
    //verification de l'extension
    $fileInfo = pathinfo($profile_photo['name']);
    $extension = $fileInfo['extension'];
    $permissions = ['jpg', 'jpeg', 'gif', 'png'];
    if (!in_array(strtolower($extension), $permissions)) {
        throw new Exeception("les fichier de ce genre ne sont pas prise en charge.");
    }
    //verifier si le dossier d'enregistrement existe. si oui, enregistre la photo et copier son chemin. si non , creer 
    $enregistrement ='C:'. DIRECTORY_SEPARATOR .'xampp'. DIRECTORY_SEPARATOR .'htdocs'. DIRECTORY_SEPARATOR .'myschoolface'. DIRECTORY_SEPARATOR .'uploads'. DIRECTORY_SEPARATOR .'avatar'. DIRECTORY_SEPARATOR ;
    echo $enregistrement;
    if (!is_dir($enregistrement)) {
        mkdir($enregistrement,0777,true);
    }
    $destination = $enregistrement . basename($profile_photo['name']);
    if (move_uploaded_file($profile_photo['tmp_name'], $destination)) {
        $chemin_fichier = $destination;
        $chemin_fichier = str_replace('\\', '/', $destination);
    echo $chemin_fichier;

    } else {
        throw new Exeception( "Une erreur s'est produite lors du téléchargement du fichier.");
        return;
    }
}
//controle des champ email nom prenom de input
if (isset($_POST['Email']) && isset($_POST['Nom']) && isset($_POST['Prenom'])) {
    if(filter_var($_POST['Email'],FILTER_VALIDATE_EMAIL)){
        $email=$_POST['Email'];
    }else{
        throw new Exeception('reverifier la composition de votre email');
    }
    if (isset($_POST['Nom'])&& isset($_POST['Prenom'])&& !empty($_POST['Nom']) && !empty($_POST['Prenom'])) {
        $nom=htmlspecialchars($_POST['Nom']);
        $prenom=htmlspecialchars($_POST['Prenom']);
    }}
    //controle des champ telephone et mot de pass
    if (isset($_POST['Telephone']) && isset($_POST['new-password']) && !empty($_POST['Telephone']) &&!empty($_POST['new-password'])) {

        $telephone=$_POST['Telephone'];
        $password=password_hash($_POST['new-password'],PASSWORD_DEFAULT);
    }
//mise a jour dans la base de donne si tout est correcte 
$sql="UPDATE utilisateurs SET nom='$nom',prenom='$prenom',photo_profile='$chemin_fichier',email='$email',telephone='$telephone',mot_de_passe='$password' WHERE id_utilisateur='$Id';";
$resultat=mysqli_query($con,$sql);
if ($resultat) {
   // header("location: ../dashboard/index.php");
}else{
    throw new Exeception("update faild");
}
}catch(Exeception $e){
        die('Erreur: '.$e->getMessage());
}

?>
