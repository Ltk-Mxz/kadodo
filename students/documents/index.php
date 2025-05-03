<?php
$pageTitle = "Mes Documents";
require_once "../../components/header/header.php";
require_once '../../config/database.php';

$db = Database::getInstance()->getConnection();
$id = $_SESSION['user']['id'];

// Use prepared statement for filière query
$sqlFiliere = "SELECT f.* 
               FROM filiere f
               JOIN etudiant e ON e.id_filiere = f.id_filiere 
               WHERE e.id_utilisateur = ?";

$stmt = $db->prepare($sqlFiliere);
$stmt->execute([$id]);
$filiereT = $stmt->fetch(PDO::FETCH_ASSOC);

// Initialize empty arrays
$documents = [];
$Profs = [];

if ($filiereT) {
    $filiere = $filiereT['id_filiere'];

    // Get documents with prepared statement
    $requeteDoc = "SELECT d.*, p.id_professeur, u.nom, u.prenom 
                   FROM filiere f
                   JOIN document d ON d.id_filiere = f.id_filiere 
                   JOIN professeur p ON p.id_professeur = d.id_professeur 
                   JOIN utilisateur u ON u.id_utilisateur = p.id_utilisateur 
                   WHERE f.id_filiere = ?";

    $stmt = $db->prepare($requeteDoc);
    $stmt->execute([$filiere]);
    $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get professors with prepared statement
    $requeteProf = "SELECT DISTINCT u.nom, u.prenom 
                    FROM utilisateur u
                    JOIN professeur p ON u.id_utilisateur = p.id_utilisateur 
                    JOIN cours c ON c.id_professeur = p.id_professeur
                    JOIN attribution_cours ac ON ac.id_cours = c.id_cours
                    JOIN filiere f ON f.id_filiere = ac.id_filiere
                    WHERE f.id_filiere = ?";

    $stmt = $db->prepare($requeteProf);
    $stmt->execute([$filiere]);
    $Profs = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

?>

<div class="overlay"></div>
<div class="main-wrapper">
    <?php require_once "../../components/sidebar/sidebar.php" ?>

    <main class="main-content">
        <div class="content-wrapper">
            <div class="header-section mb-4">
                <h1 class="content-title mb-3">Mes Documents</h1>

                <?php if (!$filiereT): ?>
                    <div class="alert alert-warning">
                        Aucune filière trouvée pour cet étudiant.
                    </div>
                <?php else: ?>
                    <div class="row g-4">
                        <?php foreach ($documents as $document): ?>
                            <div class="col-md-4">
                                <div class="document-card">
                                    <div class="document-type pdf">PDF</div>
                                    <div class="document-info">
                                        <h3><?= $document['lib_doc'] ?></h3>
                                        <p class="professor"><?= $document['nom'] ?> <?= $document['prenom'] ?></p>
                                        <p class="date">Dernière modification : <?= $document['date_upload'] ?></p>
                                        <span class="document-size">2.5 MB</span>
                                    </div>
                                    <div class="document-actions">
                                        <a href="#" class="btn btn-icon" title="Voir le document">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16">
                                                <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z" />
                                                <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z" />
                                            </svg>
                                        </a>
                                        <a href="<?= $document['chemin_doc'] ?>" class="btn btn-icon" download="<?= $document['lib_doc'] ?>" title="Télécharger">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-download" viewBox="0 0 16 16">
                                                <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z" />
                                                <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z" />
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<?php require_once "../../components/footer/footer.php" ?>