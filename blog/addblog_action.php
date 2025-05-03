<?php

require_once('../../config/database.php');
//------------------------------------Traitement du formulaire-----------------------------------------

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $gottitle = isset($_POST['titre']) ? htmlspecialchars(trim($_POST['titre'])) : '';
    $gotcontent = isset($_POST['contenu']) ? htmlspecialchars(trim($_POST['contenu'])) : '';
    $gotcategorie = isset($_POST['categorie']) ? htmlspecialchars(trim($_POST['categorie'])) : '';
    $id_moderateur = htmlspecialchars($_POST['id_moderateur']);

    // Vérification des champs obligatoires
    $errors = [];

    if (empty($gottitle) || strlen($gottitle) > 100) {
        $errors[] = "Le titre doit contenir entre 1 et 100 caractères.";
    }
    if (empty($gotcontent)) {
        $errors[] = "Le contenu ne peut pas être vide.";
    }
    if (empty($gotcategorie)) {
        $errors[] = "Veuillez sélectionner une catégorie.";
    }

    $photo = null;

    if (isset($_FILES['unetof'])) {
        $matof = $_FILES['unetof'];
        $filename = $matof['name'];

        // Vérification plus robuste de l'extension
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if ($matof['error'] == 0) {
            if ($matof['size'] > 5000000) {
                $errors[] = "Image trop volumineuse (>5Mo)";
            } else {
                $autorized_extensions = ['png', 'jpg', 'jpeg', 'jfif'];
                if (empty($extension) || !in_array($extension, $autorized_extensions)) {
                    $errors[] = $extension ? "Extension .$extension non autorisée" : "Fichier sans extension";
                } else {
                    // Chemin absolu pour le stockage
                    $upload_dir = __DIR__ . '/../../uploads/blog/';

                    // Créer le dossier s'il n'existe pas
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }

                    // Génération du nom de fichier
                    $prefix = 'blog_';
                    $unique = bin2hex(random_bytes(8));
                    $new_filename = $prefix . $unique . '.' . $extension;
                    $destination = $upload_dir . $new_filename;

                    if (move_uploaded_file($matof['tmp_name'], $destination)) {
                        $photo = $new_filename; // Sauvegarder seulement le nom du fichier
                        chmod($destination, 0644); // Permissions correctes
                    } else {
                        $upload_error = error_get_last();
                        $errors[] = "Échec de l'upload: " . ($upload_error ? $upload_error['message'] : 'Erreur inconnue');
                    }
                }
            }
        } else {
            $errors[] = "Erreur upload: " . $this->uploadErrorCodeToString($matof['error']);
        }
    } else {
        $errors[] = "Aucune image envoyée";
    }


    //die($photo);

    //-----------------------------------------------------------------INSERTION DANS LA BASE DE DONNEES-------------------------------------------------
    if (empty($errors)) {
        try {
            $dateNow = date('Y-m-d H:i:s');

            if ($stmt = $dbase_connected->prepare("INSERT INTO publication_blog 
            (id_moderateur, titre, contenu, categorie_blog, date_creation, date_mise_a_jour, photo) 
            VALUES (?, ?, ?, ?, ?, ?, ?)")) {

                $stmt->execute([$id_moderateur, $gottitle, $gotcontent, $gotcategorie, $dateNow, $dateNow, $photo]);

                if ($stmt->rowCount() > 0) {
                    echo "Insertion réussie !";
                    header('Location: index.php');
                    exit;
                } else {
                    echo "Échec de l'insertion !";
                    print_r($stmt->errorInfo()); // Affiche les erreurs SQL
                }
            } else {
                echo "Échec de l'insertion !";
                print_r($stmt->errorInfo()); // Pour affiche les erreurs SQL
            }
        } catch (PDOException $e) {
            echo "Erreur base de données: " . $e->getMessage();
        }
    } else {
        print_r($errors);
    }
}
