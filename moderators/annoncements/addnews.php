<?php  
session_start();
require_once('../../components/header/header.php'); 
require_once('../../config/database.php');

if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id'])) {
    die("Erreur : Utilisateur non connecté");
}

$id_utilisateur = $_SESSION['user']['id'];

// Debug: Afficher l'ID utilisateur
 echo "Debug - ID Utilisateur: " . $id_utilisateur . "<br>";

try {
    // Récupérer l'ID du modérateur
    $check_mod = $dbase_connected->prepare("SELECT id_moderateur FROM moderateur WHERE id_utilisateur = :id_utilisateur");
    $check_mod->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);
    $check_mod->execute();
    
    if ($check_mod->rowCount() > 0) {
        $moderateur = $check_mod->fetch(PDO::FETCH_ASSOC);
        $id_mod = $moderateur['id_moderateur'];
        
        //Debug: Afficher l'ID modérateur trouvé
        //echo "Debug - ID Modérateur trouvé: " . $id_mod . "<br>";
    } else {
        // Debug plus complet
        // echo "Debug - Requête SQL: " . $check_mod->queryString . "<br>";
        // die("Erreur : Aucun modérateur trouvé pour cet utilisateur (ID: $id_utilisateur). Vérifiez la table 'moderateur'.");
    }
} catch (PDOException $e) {
    die("Erreur de base de données : " . $e->getMessage());
}
?>


<br><br><br><br><br>
<center><h1>ENREGISTRER UNE ACTUALITE</h1></center>
<form method="POST" action="controler.php" class="container mt-4">
    <input type="hidden" name="id_moderateur" value="<?= $id_mod; ?>">
    
    <div class="mb-3">
        <label class="form-label">Titre*</label>
        <input type="text" name="titre" class="form-control" maxlength="100" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Contenu*</label>
        <textarea name="contenu" class="form-control" rows="5" required></textarea>
    </div>

    <div class="mb-3">
        <label class="form-label">État</label>
        <select name="etat" class="form-select">
            <option value="en attente">En attente</option>
            <option value="publié">Publié</option>
        </select>
    </div>
    <br><br><br>
    <center><button type="submit" class="btn btn-primary">
        <i class="bi bi-send"></i><h3> Ajouter l'actu </h3>
    </button></center>
</form>
