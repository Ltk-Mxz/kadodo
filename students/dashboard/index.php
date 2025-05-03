<?php
require_once '../../utils/auth.php';
requireRole('etudiant');
require_once "../../utils/photo.php";
require_once "../../components/header/header.php";

$db = Database::getInstance()->getConnection();
$id = $_SESSION['user']['id'];

try {
    // Get student notes
    $sql = "SELECT note.note, cours.nom_cours, utilisateur.id_utilisateur
            FROM utilisateur
            JOIN etudiant ON utilisateur.id_utilisateur = etudiant.id_utilisateur 
            JOIN note ON note.id_etudiant = etudiant.id_etudiant  
            JOIN cours ON cours.id_cours = note.id_cours 
            WHERE utilisateur.id_utilisateur = :id";

    $stmt = $db->prepare($sql);
    $stmt->execute(['id' => $id]);
    $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get notifications
    $sql2 = "SELECT * FROM notifications WHERE id_utilisateur = :id";
    $stmt = $db->prepare($sql2);
    $stmt->execute(['id' => $id]);
    $notifs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get filière info
    $sqlFiliere = "SELECT f.* 
                   FROM filiere f
                   JOIN etudiant e ON e.id_filiere = f.id_filiere 
                   WHERE e.id_utilisateur = :id";

    $stmt = $db->prepare($sqlFiliere);
    $stmt->execute(['id' => $id]);
    $filiereInfo = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($filiereInfo) {
        $filiere = $filiereInfo['id_filiere'];

        // Get documents only if filière exists
        $requeteDoc = "SELECT d.* 
                       FROM document d
                       WHERE d.id_filiere = :filiere";

        $stmt = $db->prepare($requeteDoc);
        $stmt->execute(['filiere' => $filiere]);
        $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $documents = [];
    }

    // Get blog publications
    $sql3 = "SELECT pb.*, u.prenom, u.nom, u.photo_profile
             FROM publication_blog pb
             JOIN moderateur m ON m.id_moderateur = pb.id_moderateur
             JOIN utilisateur u ON u.id_utilisateur = m.id_utilisateur";

    $stmt = $db->prepare($sql3);
    $stmt->execute();
    $publications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer les annonces récentes
    $sql_annonces = "SELECT annonce.*, DATE_FORMAT(date_pub_annonce, '%d/%m/%Y à %H:%i') as date_formattee 
                     FROM annonce 
                     WHERE etat_annonce = 'publié' 
                     ORDER BY date_pub_annonce DESC 
                     LIMIT 5";
    $stmt_annonces = $db->prepare($sql_annonces);
    $stmt_annonces->execute();
    $annonces_recentes = $stmt_annonces->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Handle exception
}

?>
<link rel="stylesheet" href="/myschoolface/assets/css/notes.css">

<div class="main-wrapper">
    <?php require_once "../../components/sidebar/sidebar.php" ?>

    <main class="main-content">
        <div class="container-fluid px-3 px-md-4">
            <!-- En-tête avec titre et recherche -->
            <div class="header-section my-4">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                    <h1 class="content-title h3 mb-0">Tableau de bord</h1>
                    <div class="search-container w-100 w-md-auto">
                        <div class="input-group">
                            <span class="input-group-text border-end-0 bg-white">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="text" class="form-control border-start-0 ps-0" placeholder="Rechercher...">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cartes d'informations -->
            <div class="row g-4">
                <!-- Dernières Notes -->
                <div class="col-12 col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <h2 class="section-title h5 mb-4">Dernières Notes</h2>
                            <div class="notes-list">
                                <?php if (empty($notes)): ?>
                                    <div class="text-center text-muted p-3">
                                        <i class="bi bi-star text-muted fs-4"></i>
                                        <p class="mb-0">Aucune note disponible</p>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($notes as $note): ?>
                                        <div class="note-item py-2 border-bottom">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="note-subject"><?= htmlspecialchars($note['nom_cours']) ?></span>
                                                <span class="badge <?= $note['note'] >= 10 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' ?> px-3 py-2">
                                                    <?= $note['note'] ?>/20
                                                </span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Documents Récents -->
                <div class="col-12 col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <h2 class="section-title h5 mb-4">Documents Récents</h2>
                            <div class="list-group list-group-flush">
                                <?php if (empty($documents)): ?>
                                    <div class="text-center text-muted p-3">
                                        <i class="bi bi-file-earmark-x text-muted fs-4"></i>
                                        <p class="mb-0">Aucun document disponible</p>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($documents as $document): ?>
                                        <a href="<?= htmlspecialchars($document['chemin_doc']) ?>"
                                            class="document-item list-group-item list-group-item-action border-0 py-3"
                                            download="<?= htmlspecialchars($document['lib_doc']) ?>">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="document-icon pdf rounded">
                                                    <i class="bi bi-file-pdf"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-medium text-truncate"><?= htmlspecialchars($document['lib_doc']) ?></div>
                                                    <small class="text-muted">PDF • <?= date('d/m/Y', strtotime($document['date_upload'])) ?></small>
                                                </div>
                                            </div>
                                        </a>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Annonces Récentes -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="card-title">Annonces récentes</h5>
                                <i class="bi bi-megaphone text-purple"></i>
                            </div>
                            <?php
                            $colors = ['primary', 'success', 'info', 'warning', 'danger', 'purple'];
                            ?>
                            <?php if (empty($annonces_recentes)): ?>
                                <div class="text-center text-muted p-3">
                                    <i class="bi bi-megaphone-fill fs-4 mb-2 d-block"></i>
                                    <p class="mb-0">Aucune annonce n'est disponible pour le moment</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($annonces_recentes as $index => $annonce):
                                    $color = $colors[$index % count($colors)];
                                ?>
                                    <div class="announcement-item border-<?= $color ?> bg-<?= $color ?>-subtle mb-3">
                                        <h6 class="text-<?= $color ?>"><?= htmlspecialchars($annonce['titre_annonce']) ?></h6>
                                        <p class="text-muted mb-0">
                                            <i class="bi bi-clock me-1"></i>
                                            <?= $annonce['date_formattee'] ?>
                                        </p>
                                    </div>
                                <?php endforeach; ?>
                                <div class="text-end mt-3">
                                    <a href="/myschoolface/students/announcements/" class="btn btn-outline-primary btn-sm">
                                        Voir toutes les annonces
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Blog Étudiant avec barre de recherche -->
            <div class="d-flex justify-content-between align-items-center mt-4 mb-3">
                <h2 class="section-title mb-0">Blog Étudiant</h2>
                <div class="search-container position-relative">
                    <input type="text" class="form-control search-input" placeholder="Rechercher...">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search search-icon" viewBox="0 0 16 16">
                        <path d="M11.742 10.344a6.5 6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 5.5 0 1 1-11 0 5.5 5.5 5.5 0 0 1 11 0z" />
                    </svg>
                </div>
            </div>

            <div class="row g-4">
                <?php if (empty($publications)): ?>
                    <div class="col-12">
                        <div class="text-center text-muted p-3">
                            <i class="bi bi-journal-x text-muted fs-4"></i>
                            <p class="mb-0">Aucune publication disponible</p>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($publications as $publication) { ?>
                        <div class="col-md-4">
                            <div class="blog-card">
                                <img src="<?= UPLOADS_PATH ?>/blog/<?= htmlspecialchars(getBlogImagePath($publication['photo'])) ?>"
                                    alt="<?= htmlspecialchars($publication['titre']) ?>"
                                    class="blog-image">
                                <div class="blog-content">
                                    <h5 class="mb-2"><?= $publication['titre'] ?></h5>
                                    <div class="blog-meta mb-2">
                                        <div class="blog-author">
                                            <img src="/myschoolface/uploads/avatar/<?= htmlspecialchars(getAvatarPath($publication['photo_profile'])) ?>"
                                                alt="<?= htmlspecialchars($publication['prenom'] . ' ' . $publication['nom']) ?>"
                                                class="blog-author-avatar">
                                            <span><?= htmlspecialchars($publication['prenom'] . ' ' . $publication['nom']) ?></span>
                                        </div>
                                        <span>•</span>
                                        <span><?= $publication['date_creation'] ?></span>
                                    </div>
                                    <p class="text-muted mb-2"><?= htmlspecialchars(substr($publication['contenu'], 0, 100)) ?>...</p>
                                    <a href="/myschoolface/blog/view_blog.php?id=<?= $publication['id_publication_blog'] ?>"
                                        class="btn btn-link px-0">Lire plus</a>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>
<?php require_once "../../components/footer/footer.php" ?>