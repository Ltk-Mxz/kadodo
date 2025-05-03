<?php
ob_start();
session_start();
require_once('../../config/database.php');

$annonce = null;

// ----- SI REQUÊTE GET : afficher le formulaire -----
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id_annonce']) && is_numeric($_GET['id_annonce'])) {
    $id = (int)$_GET['id_annonce'];

    $stmt = $dbase_connected->prepare("SELECT annonce.*, utilisateur.*
        FROM annonce
        JOIN moderateur ON annonce.id_moderateur = moderateur.id_moderateur
        JOIN utilisateur ON moderateur.id_utilisateur = utilisateur.id_utilisateur
        WHERE annonce.id_annonce = :id");
    $stmt->execute([':id' => $id]);
    $annonce = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$annonce) {
        $_SESSION['error'] = "Actualité non trouvée";
        header('Location: ../dashboard/index.php');
        exit;
    }
}

// ----- SI REQUÊTE POST : traiter la mise à jour -----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (
        isset($_POST['titre'], $_POST['contenu'], $_POST['etat'], $_POST['id_annonce']) &&
        !empty($_POST['titre']) && !empty($_POST['contenu']) && !empty($_POST['etat']) && !empty($_POST['id_annonce'])
    ) {
        $id = (int)$_POST['id_annonce'];
        $titre = htmlspecialchars($_POST['titre'], ENT_QUOTES, 'UTF-8');
        $contenu = htmlspecialchars($_POST['contenu'], ENT_QUOTES, 'UTF-8');
        $etat = htmlspecialchars($_POST['etat'], ENT_QUOTES, 'UTF-8');

        try {
            $sql = "UPDATE annonce
                    SET titre_annonce = :titre,
                        contenu_annonce = :contenu,
                        etat_annonce = :etat,
                        date_pub_annonce = NOW()
                    WHERE id_annonce = :id";

            $stmt = $dbase_connected->prepare($sql);
            $stmt->execute([
                ':titre' => $titre,
                ':contenu' => $contenu,
                ':etat' => $etat,
                ':id' => $id
            ]);

            if ($stmt->rowCount() > 0) {
                $_SESSION['success'] = "Publication mise à jour avec succès";
            } else {
                $_SESSION['error'] = "Aucune modification effectuée ou publication non trouvée";
            }
        } catch (PDOException $e) {
            error_log("Erreur lors de la mise à jour : " . $e->getMessage());
            $_SESSION['error'] = "Erreur technique lors de la mise à jour";
        }

        header('Location: ../dashboard/index.php');
        exit;
    } else {
        $_SESSION['error'] = "Champs invalides ou manquants";
        header('Location: ../dashboard/index.php');
        exit;
    }
}
?>

<?php if ($annonce): ?>
<?php require_once('../../components/header/header.php'); ?>

<div class="container mt-5">
    <center><h1 class="mb-4">MODIFIER UNE PUBLICATION</h1></center>
    <form method="POST" class="container mt-4">
        <input type="hidden" name="id_annonce" value="<?= htmlspecialchars($annonce['id_annonce']) ?>">

        <div class="mb-3">
            <label class="form-label"><h4>Titre*</h4></label>
            <input type="text" name="titre" class="form-control" maxlength="100"
                   value="<?= htmlspecialchars($annonce['titre_annonce']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label"><h4>Contenu*</h4></label>
            <textarea name="contenu" class="form-control" rows="5" required><?= htmlspecialchars($annonce['contenu_annonce']) ?></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">État*</label>
            <select name="etat" class="form-select">
                <option value="en attente" <?= $annonce['etat_annonce'] === 'en attente' ? 'selected' : '' ?>>En attente</option>
                <option value="publié" <?= $annonce['etat_annonce'] === 'publié' ? 'selected' : '' ?>>Publié</option>
            </select>
        </div>

        <center><button type="submit" class="btn btn-primary"><h4>Modifier</h4></button></center>
    </form>
</div>

<?php require_once('../../components/footer/footer.php'); ?>
<?php endif; ?>
