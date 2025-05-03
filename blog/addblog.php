<?php
session_start();
require_once('../../components/header/header.php');
require_once('../../config/database.php');

if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id'])) {
    die("Erreur : Utilisateur non connecté");
}

$id_utilisateur = $_SESSION['user']['id'];

// Debug: Afficher l'ID utilisateur
//echo "Debug - ID Utilisateur: " . $id_utilisateur . "<br>";

try {
    //Pour récupérer l'ID du modérateur
    $check_mod = $dbase_connected->prepare("SELECT id_moderateur FROM moderateur WHERE id_utilisateur = :id_utilisateur");
    $check_mod->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);
    var_dump($id_utilisateur);
    $check_mod->execute();

    if ($check_mod->rowCount() > 0) {
        $moderateur = $check_mod->fetch(PDO::FETCH_ASSOC);
        $id_mod = $moderateur['id_moderateur'];

        //Debug: Afficher l'ID modérateur trouvé
        //echo "Debug - ID Modérateur trouvé: " . $id_mod . "<br>";
    } else {
        // Debug plus complet
        // echo "<br><br><br><br>Debug - Requête SQL: " . $check_mod->queryString . "<br>";
        // die("Erreur : Aucun modérateur trouvé pour cet utilisateur (ID: $id_utilisateur). Vérifiez la table 'moderateurs'.");
    }
} catch (PDOException $e) {
    die("Erreur de base de données : " . $e->getMessage());
}
?>


<div class="container mt-5">
    <center>
        <h1 class="mb-4">AJOUTER UNE PUBLICATION DE BLOG</h1>
    </center>
    <form method="POST" action="addblog_action.php" class="container mt-4" enctype="multipart/form-data">
        <input type="hidden" name="id_moderateur" value="<?= $id_mod; ?>">

        <div class="mb-3">
            <label class="form-label">
                <h4>Sujet du blog*</h4>
            </label>
            <input type="text" name="titre" class="form-control" maxlength="100" placeholder="Titre..." required>
        </div>

        <div class="mb-3">
            <label class="form-label">
                <h4>Contenu*</h4>
            </label>
            <textarea name="contenu" class="form-control" rows="5" required></textarea>
        </div>

        <div class="mb-3">
            <select name="categorie" class="form-select" required>
                <option value="" disabled selected>Choisir une catégorie</option>
                <option value="Conseils d'études">Conseils d'études</option>
                <option value="Événements">Événements</option>
                <option value="Annonces">Annonces</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="unetof" class="form-label">
                <h4>Ajouter une photo*</h4>
            </label>
            <input type="file" class="form-control" id="unetof" name="unetof" />
        </div>
        <br><br><br>
        <center><button type="submit" class="btn btn-primary">
                <i class="bi bi-send"></i>
                <h4> _Publier_ </h4>
            </button></center>
    </form>

</div>
<?php require_once '../../components/footer/footer.php'; ?>