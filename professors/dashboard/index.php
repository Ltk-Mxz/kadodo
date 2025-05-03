<?php
require_once '../../utils/auth.php';
requireRole('professeur');
require_once '../../utils/photo.php';
require_once '../../components/header/header.php';
require_once '../../config/database.php';

$id_user = $_SESSION['user']['id'];

$db = Database::getInstance()->getConnection();

try {
    // Vérifier d'abord si le professeur existe et récupérer son ID
    $checkProf = "SELECT id_professeur FROM professeur WHERE id_utilisateur = ?";
    $stmt = $db->prepare($checkProf);
    $stmt->execute([$id_user]);
    $prof = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$prof) {
        throw new Exception("Aucun professeur trouvé avec cet identifiant.");
    }

    // Debug: Afficher les cours disponibles pour ce professeur
    $debugQuery = "SELECT * FROM cours WHERE id_professeur = ?";
    $stmt = $db->prepare($debugQuery);
    $stmt->execute([$prof['id_professeur']]);
    $coursCount = $stmt->rowCount();

    if ($coursCount === 0) {
        $_SESSION['warning'] = "Aucun cours n'est assigné à ce professeur.";
    }

    // Modifier la requête pour permettre de voir les cours même sans attribution
    $sql = "
        SELECT DISTINCT 
            f.libelle_filiere, 
            c.nom_cours,
            COALESCE(ac.id_attribution, c.id_cours) as id_attribution,
            c.id_cours
        FROM professeur p
        INNER JOIN cours c ON p.id_professeur = c.id_professeur 
        LEFT JOIN attribution_cours ac ON c.id_cours = ac.id_cours 
        LEFT JOIN filiere f ON ac.id_filiere = f.id_filiere
        WHERE p.id_utilisateur = ? 
        ORDER BY f.libelle_filiere, c.nom_cours
    ";

    $stmt = $db->prepare($sql);
    $stmt->execute([$id_user]);
    $profCours = $stmt->fetchAll(PDO::FETCH_ASSOC);

    error_log("Requête prof cours: " . print_r([
        'id_user' => $id_user,
        'count' => count($profCours),
        'sql' => $sql
    ], true));

    if (empty($profCours)) {
        $_SESSION['warning'] = "Aucun cours n'est attribué à une filière pour ce professeur.";
    }

    // Récupérer les annonces récentes
    $sql_annonces = "SELECT annonce.*, DATE_FORMAT(date_pub_annonce, '%d/%m/%Y à %H:%i') as date_formattee 
                     FROM annonce 
                     WHERE etat_annonce = 'publié' 
                     ORDER BY date_pub_annonce DESC 
                     LIMIT 5";
    $stmt_annonces = $db->prepare($sql_annonces);
    $stmt_annonces->execute();
    $annonces_recentes = $stmt_annonces->fetchAll(PDO::FETCH_ASSOC);

    // Get blog publications
    $sql3 = "SELECT pb.*, u.prenom, u.nom, u.photo_profile
             FROM publication_blog pb
             JOIN moderateur m ON m.id_moderateur = pb.id_moderateur
             JOIN utilisateur u ON u.id_utilisateur = m.id_utilisateur
             ORDER BY pb.date_creation DESC
             LIMIT 6";

    $stmt = $db->prepare($sql3);
    $stmt->execute();
    $publications = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    error_log("Erreur SQL: " . $e->getMessage());
    $profCours = [];
    $annonces_recentes = [];
    $publications = [];
}

?>
<div id="notifications-container" class="position-fixed top-0 end-0 p-3" style="z-index: 1050;"></div>
<div class="container-fluid">
    <div class="row">
        <?php require_once '../../components/sidebar/sidebar.php'; ?>

        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            <!-- Titre -->
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Tableau de bord</h1>
            </div>

            <div class="container-fluid">
                <div class="row mt-4">
                    <!-- Saisir les notes -->
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="card-title">Saisir les notes</h5>
                                    <i class="bi bi-star-fill text-warning"></i>
                                </div>
                                <form action="saisie.php" method="POST">
                                    <?php if (isset($_SESSION['error'])): ?>
                                        <div class="alert alert-danger">
                                            <?= htmlspecialchars($_SESSION['error']) ?>
                                            <?php unset($_SESSION['error']); ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (isset($_SESSION['warning'])): ?>
                                        <div class="alert alert-warning">
                                            <?= htmlspecialchars($_SESSION['warning']) ?>
                                            <?php unset($_SESSION['warning']); ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (empty($profCours)): ?>
                                        <div class="text-center text-muted p-3">
                                            <i class="bi bi-book-x text-muted fs-4"></i>
                                            <p class="mb-0">Aucun cours n'est assigné</p>
                                        </div>
                                    <?php else: ?>
                                        <select class="form-select" name="selectF" required>
                                            <option value="">Sélectionner la filière & matière</option>
                                            <?php if (!empty($profCours)): ?>
                                                <?php foreach ($profCours as $cours): ?>
                                                    <option value="<?= htmlspecialchars($cours['id_attribution']) ?>">
                                                        <?= htmlspecialchars($cours['libelle_filiere']) ?> - <?= htmlspecialchars($cours['nom_cours']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <option disabled>Aucun cours disponible</option>
                                            <?php endif; ?>
                                        </select>
                                    <?php endif; ?>
                                    <div class="mb-3">
                                        <select class="form-select" name="selectS" required>
                                            <option value="">Sélectionner le semestre</option>
                                            <option value="Semestre1">Semestre 1</option>
                                            <option value="Semestre2">Semestre 2</option>
                                        </select>
                                    </div>
                                    <button class="btn btn-primary w-100">Commencer la saisie</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Documents -->
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="card-title">Documents</h5>
                                    <i class="bi bi-file-earmark-text text-primary"></i>
                                </div>
                                <form action="doc.php" method="POST" id="loginForm" enctype="multipart/form-data">
                                    <select class="form-select" name="cour" required>
                                        <option value="">Sélectionner la filière & Matière</option>
                                        <?php foreach ($profCours as $cours): ?>
                                            <option value="<?= htmlspecialchars($cours['id_attribution']) ?>">
                                                <?= htmlspecialchars($cours['libelle_filiere']) ?> - <?= htmlspecialchars($cours['nom_cours']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="text" class="form-control mt-3" id="inputGroupFile01" name="lib" placeholder="Entrer le libellé du document" required>
                                    <div class="input-group mt-3">
                                        <input type="file" class="form-control" name="document" required>
                                    </div>
                                    <button class="btn btn-primary w-100 mt-3" type="submit">Soumettre le document</button>
                                </form>

                                <!-- Section pour afficher les 5 derniers documents -->
                                <div class="documents-list mt-4">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="mb-0">Documents récents</h6>
                                        <!-- <a href="all_documents.php" class="btn btn-sm btn-outline-primary">
                                            Voir tous les documents
                                        </a> -->
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>État</th>
                                                    <th>Document</th>
                                                    <th>Filière/Cours</th>
                                                    <th>Date</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $stmt = $db->prepare("
                                                    SELECT d.*, f.libelle_filiere, c.nom_cours, d.date_upload 
                                                    FROM document d
                                                    JOIN filiere f ON d.id_filiere = f.id_filiere
                                                    JOIN cours c ON d.id_cours = c.id_cours
                                                    WHERE d.id_professeur = ?
                                                    ORDER BY d.date_upload DESC
                                                    LIMIT 5
                                                ");
                                                $stmt->execute([$prof['id_professeur']]);
                                                $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                                $hasValidDocuments = false;

                                                foreach ($documents as $doc):
                                                    $filePath = __DIR__ . '/../../uploads/docs/' . $doc['chemin_doc'];
                                                    $fileExists = !empty($doc['chemin_doc']) && file_exists($filePath);
                                                    if (!empty($doc['chemin_doc'])):
                                                        $hasValidDocuments = true;
                                                ?>
                                                        <tr>
                                                            <td>
                                                                <?php if ($fileExists): ?>
                                                                    <i class="bi bi-file-earmark-text text-success" title="Document disponible"></i>
                                                                <?php else: ?>
                                                                    <i class="bi bi-exclamation-triangle text-warning" title="Fichier manquant"></i>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td><?= htmlspecialchars($doc['lib_doc']) ?></td>
                                                            <td><?= htmlspecialchars($doc['libelle_filiere']) ?> - <?= htmlspecialchars($doc['nom_cours']) ?></td>
                                                            <td><?= date('d/m/Y', strtotime($doc['date_upload'])) ?></td>
                                                            <td>
                                                                <div class="btn-group">
                                                                    <?php if ($fileExists): ?>
                                                                        <a href="/myschoolface/uploads/docs/<?= htmlspecialchars($doc['chemin_doc']) ?>"
                                                                            class="btn btn-sm btn-outline-primary"
                                                                            target="_blank">
                                                                            <i class="bi bi-download"></i>
                                                                        </a>
                                                                    <?php endif; ?>
                                                                    <button class="btn btn-sm btn-outline-danger"
                                                                        onclick="deleteDocument(<?= $doc['id_doc'] ?>, '<?= htmlspecialchars($doc['lib_doc']) ?>')"
                                                                        title="Supprimer">
                                                                        <i class="bi bi-trash"></i>
                                                                    </button>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php
                                                    endif;
                                                endforeach;

                                                if (!$hasValidDocuments):
                                                    ?>
                                                    <tr>
                                                        <td colspan="5" class="text-center">
                                                            <div class="p-3">
                                                                <i class="bi bi-file-earmark-x text-muted fs-4"></i>
                                                                <p class="text-muted mb-0">Aucun document récent</p>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="text-end mt-3">
                                        <a href="../documents" class="btn btn-outline-primary btn-sm">
                                            Voir tous les documents
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Annonces récentes -->
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="card-title">Annonces récentes</h5>
                                    <i class="bi bi-megaphone text-purple"></i>
                                </div>
                                <?php
                                // Ajouter ce tableau de couleurs juste avant la boucle d'affichage des annonces
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
                                        <a href="/myschoolface/professors/announcements/" class="btn btn-outline-primary btn-sm">
                                            Voir toutes les annonces
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Blog Section -->
                <div class="row mt-4">
                    <div class="col-12">
                        <?php if (empty($publications)): ?>
                            <div class="text-center text-muted p-3">
                                <i class="bi bi-journal-x text-muted fs-4"></i>
                                <p class="mb-0">Aucune publication disponible</p>
                            </div>
                        <?php else: ?>
                            <div class="row g-4">
                                <?php foreach ($publications as $publication): ?>
                                    <div class="col-md-4">
                                        <div class="blog-card">
                                            <img src="<?= UPLOADS_PATH ?>/blog/<?= htmlspecialchars(getBlogImagePath($publication['photo'])) ?>"
                                                alt="<?= htmlspecialchars($publication['titre']) ?>"
                                                class="blog-image">
                                            <div class="blog-content">
                                                <h5 class="mb-2"><?= htmlspecialchars($publication['titre']) ?></h5>
                                                <div class="blog-meta mb-2">
                                                    <div class="blog-author">
                                                        <img src="/myschoolface/uploads/avatar/<?= htmlspecialchars(getAvatarPath($publication['photo_profile'])) ?>"
                                                            alt="<?= htmlspecialchars($publication['prenom'] . ' ' . $publication['nom']) ?>"
                                                            class="blog-author-avatar">
                                                        <span><?= htmlspecialchars($publication['prenom'] . ' ' . $publication['nom']) ?></span>
                                                    </div>
                                                    <span>•</span>
                                                    <span><?= date('d M Y', strtotime($publication['date_creation'])) ?></span>
                                                </div>
                                                <p class="text-muted mb-2"><?= htmlspecialchars(substr($publication['contenu'], 0, 100)) ?>...</p>
                                                <a href="/myschoolface/blog/view_blog.php?id=<?= $publication['id_publication_blog'] ?>"
                                                    class="btn btn-link px-0">Lire plus</a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </main>
    </div>
</div>

<?php require_once '../../components/footer/footer.php'; ?>

<script>
    document.getElementById('loginForm').addEventListener('submit', async (e) => {
        e.preventDefault();

        try {
            const formData = new FormData(e.target);
            const response = await fetch('doc.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            // Afficher la notification
            const notification = `
                    <div class="toast align-items-center border-0 bg-${data.success ? 'success' : 'danger'}" role="alert">
                        <div class="d-flex">
                            <div class="toast-body text-white">${data.message}</div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                        </div>
                    </div>
                `;

            const container = document.getElementById('notifications-container');
            container.innerHTML = notification;
            const toast = new bootstrap.Toast(container.querySelector('.toast'));
            toast.show();

            if (data.success && data.redirect) {
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 2000);
            }
        } catch (error) {
            console.error('Erreur:', error);
        }
    });

    function deleteDocument(docId, docName) {
        if (confirm(`Voulez-vous vraiment supprimer le document "${docName}" ?`)) {
            fetch('delete_document.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        id: docId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Supprimer la ligne du tableau
                        const row = document.querySelector(`tr[data-doc-id="${docId}"]`);
                        if (row) {
                            row.remove();
                        }
                        showToast('Document supprimé avec succès', 'success');
                    } else {
                        showToast(data.message || 'Erreur lors de la suppression', 'danger');
                    }
                })
                .catch(error => {
                    showToast('Erreur lors de la suppression', 'danger');
                    console.error('Erreur:', error);
                });
        }
    }
</script>