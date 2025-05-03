<?php
ob_start();
require_once('../components/header/header.php');
require_once('../config/database.php');


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (
        isset($_POST['titre'], $_POST['contenu'], $_POST['categorie'], $_POST['id_publication_blog']) &&
        !empty($_POST['titre']) && !empty($_POST['contenu']) && !empty($_POST['categorie']) && !empty($_POST['id_publication_blog'])
    ) {
        $id_publication = (int)$_POST['id_publication_blog'];
        $titre = htmlspecialchars($_POST['titre'], ENT_QUOTES, 'UTF-8');
        $contenu = htmlspecialchars($_POST['contenu'], ENT_QUOTES, 'UTF-8');
        $categorie = htmlspecialchars($_POST['categorie'], ENT_QUOTES, 'UTF-8');

        // Gestion du fichier
        $photo = null;
        $errors = [];

        if (isset($_FILES['unetof'])) {
            $matof = $_FILES['unetof'];
            $filename = $matof['name'];

            $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            if ($matof['error'] == 0) {
                if ($matof['size'] > 5000000) {
                    $errors[] = "Image trop volumineuse (>5Mo)";
                } else {
                    $autorized_extensions = ['png', 'jpg', 'jpeg', 'jfif'];
                    if (empty($extension) || !in_array($extension, $autorized_extensions)) {
                        $errors[] = $extension ? "Extension .$extension non autorisée" : "Fichier sans extension";
                    } else {
                        $upload_dir = __DIR__ . '/../../uploads/blog/';

                        // Créer le dossier s'il n'existe pas avec les bonnes permissions
                        if (!is_dir($upload_dir)) {
                            mkdir($upload_dir, 0777, true);
                            chmod($upload_dir, 0777);
                        }

                        // Génération du nom de fichier
                        $prefix = 'blog_';
                        $unique = bin2hex(random_bytes(8));
                        $new_filename = $prefix . $unique . '.' . $extension;
                        $destination = $upload_dir . $new_filename;

                        if (move_uploaded_file($matof['tmp_name'], $destination)) {
                            $photo = $new_filename;
                            chmod($destination, 0644);
                        } else {
                            $errors[] = "Échec de l'upload. Vérifiez les permissions du dossier.";
                            error_log("Upload error: " . print_r(error_get_last(), true));
                        }
                    }
                }
            } else {
                $errors[] = "Erreur upload: " . $this->uploadErrorCodeToString($matof['error']);
            }
        } else {
            $errors[] = "Aucune image envoyée";
        }

        if (empty($errors)) {
            try {
                if ($photo) {
                    $sql = "UPDATE publication_blog 
                            SET titre = :titre, 
                                contenu = :contenu, 
                                categorie_blog = :categorie, 
                                photo = :image_path,
                                date_mise_a_jour = NOW()
                            WHERE id_publication_blog = :id";
                    $params = [
                        ':titre' => $titre,
                        ':contenu' => $contenu,
                        ':categorie' => $categorie,
                        ':image_path' => $photo,
                        ':id' => $id_publication
                    ];
                } else {
                    $sql = "UPDATE publication_blog 
                            SET titre = :titre, 
                                contenu = :contenu, 
                                categorie_blog = :categorie,
                                date_mise_a_jour = NOW()
                            WHERE id_publication_blog = :id";
                    $params = [
                        ':titre' => $titre,
                        ':contenu' => $contenu,
                        ':categorie' => $categorie,
                        ':id' => $id_publication
                    ];
                }

                $requete = $dbase_connected->prepare($sql);
                $requete->execute($params);

                if ($requete->rowCount() > 0) {
                    $_SESSION['success'] = "Publication mise à jour avec succès";
                } else {
                    $_SESSION['error'] = "Aucune modification effectuée ou publication non trouvée";
                }
            } catch (PDOException $e) {
                error_log("Erreur mise à jour publication #$id_publication: " . $e->getMessage());
                $_SESSION['error'] = "Erreur technique lors de la mise à jour";
            }
        } else {
            $_SESSION['error'] = implode('<br>', $errors);
        }
        header('Location: blog_manager.php');
        ob_end_flush();
        exit;
    }
}

if (isset($_POST['id_publication_blog']) && is_numeric($_POST['id_publication_blog'])) {
    $id = (int)$_POST['id_publication_blog'];

    $stmt = $dbase_connected->prepare("SELECT publication_blog.*, utilisateur.*
        FROM publication_blog
        JOIN moderateur ON publication_blog.id_moderateur = moderateur.id_moderateur
        JOIN utilisateur ON moderateur.id_utilisateur = utilisateur.id_utilisateur
        WHERE publication_blog.id_publication_blog = :id");
    $stmt->execute([':id' => $id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$data) {
        $_SESSION['error'] = "Publication non trouvée";
        header('Location: blog_manager.php');
        exit;
    }
} else {
    $_SESSION['error'] = "ID de publication manquant";
    header('Location: blog_manager.php');
    exit;
}

require_once('../components/header/header.php');


?>

<div class="container mt-5">
    <center>
        <h1 class="mb-4">MODIFIER UNE PUBLICATION DE BLOG</h1>
    </center>

    <form method="POST" action="" class="container mt-4" enctype="multipart/form-data">
        <input type="hidden" name="id_publication_blog" value="<?= htmlspecialchars($data['id_publication_blog'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

        <div class="mb-3">
            <label class="form-label">
                <h4>Mettre à jour le titre*</h4>
            </label>
            <input type="text" name="titre" class="form-control" maxlength="100"
                value="<?= htmlspecialchars($data['titre'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">
                <h4>Mettre à jour le contenu*</h4>
            </label>
            <textarea name="contenu" class="form-control" rows="5" required><?=
                                                                            htmlspecialchars($data['contenu'] ?? '', ENT_QUOTES, 'UTF-8')
                                                                            ?></textarea>
        </div>

        <div class="mb-3">
            <select name="categorie" class="form-select" required>
                <option value="" disabled selected>Choisir une catégorie</option>
                <option value="Conseils d'études" <?=
                                                    ($data['categorie_blog'] ?? '') === "Conseils d'études" ? 'selected' : ''
                                                    ?>>Conseils d'études</option>
                <option value="Événements" <?=
                                            ($data['categorie_blog'] ?? '') === "Événements" ? 'selected' : ''
                                            ?>>Événements</option>
                <option value="Annonces" <?=
                                            ($data['categorie_blog'] ?? '') === "Annonces" ? 'selected' : ''
                                            ?>>Annonces</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="unetof" class="form-label">
                <h4>Changer d'image*</h4>
            </label>
            <input type="file" class="form-control" id="unetof" name="unetof" />
            <?php if (!empty($data['photo'])): ?>
                <p>Image actuelle: <?= htmlspecialchars($data['photo']) ?></p>
            <?php endif; ?>
        </div>

        <center><button type="submit" class="btn btn-primary">
                <h4>_Modifier_</h4>
            </button></center>
    </form>

</div>

<?php require_once '../components/footer/footer.php'; ?>