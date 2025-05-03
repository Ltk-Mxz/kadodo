<?php
require_once '../../components/header/header.php';
require_once('../annoncements/index.php');

// Initialisation des variables de pagination
$annonces = [];
$annonces_par_page = 5;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

// Calculer le nombre total de pages
try {
    $count_query = $dbase_connected->query("SELECT COUNT(*) FROM annonce");
    $total_annonces = $count_query->fetchColumn();
    $total_pages = ceil($total_annonces / $annonces_par_page);
    $offset = ($page - 1) * $annonces_par_page;
} catch (PDOException $e) {
    error_log("Erreur de pagination: " . $e->getMessage());
    $total_pages = 1;
    $offset = 0;
}
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once '../../components/sidebar/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3">Tableau de bord</h1>
                    <div class="d-flex gap-3">
                        <a href="../annoncements/addnews.php" class="btn btn-primary d-inline-flex align-items-center">
                            <i class="bi bi-plus-circle me-2"></i> Nouvelle actualité
                        </a>
                        <a href="../../blog/blog_manager.php" class="btn btn-outline-primary d-inline-flex align-items-center">
                            <i class="bi bi-pencil-square me-2"></i> Gérer le blog
                        </a>
                    </div>
                </div>

                <!-- Filtres -->
                <div class="section mb-4">
                    <form method="POST" action="" class="row g-3">
                        <div class="col-md-3">
                            <label for="etat" class="form-label">État</label>
                            <select name="etat" id="etat" class="form-select">
                                <option value="">Tous</option>
                                <option value="publié" <?= isset($_POST['etat']) && $_POST['etat'] === 'publié' ? 'selected' : '' ?>>Publiés</option>
                                <option value="en attente" <?= isset($_POST['etat']) && $_POST['etat'] === 'en attente' ? 'selected' : '' ?>>En attente</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="date" class="form-label">Date</label>
                            <select name="date" id="date" class="form-select">
                                <option value="">Toutes</option>
                                <option value="today" <?= isset($_POST['date']) && $_POST['date'] === 'today' ? 'selected' : '' ?>>Aujourd'hui</option>
                                <option value="week" <?= isset($_POST['date']) && $_POST['date'] === 'week' ? 'selected' : '' ?>>Cette semaine</option>
                                <option value="month" <?= isset($_POST['date']) && $_POST['date'] === 'month' ? 'selected' : '' ?>>Ce mois</option>
                                <option value="year" <?= isset($_POST['date']) && $_POST['date'] === 'year' ? 'selected' : '' ?>>Cette année</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="search" class="form-label">Recherche</label>
                            <input type="text" name="search" id="search" class="form-control"
                                value="<?= htmlspecialchars($_POST['search'] ?? '') ?>"
                                placeholder="Rechercher...">
                        </div>

                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-funnel"></i> Filtrer
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Stats Cards -->
                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <div class="stats-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted mb-0">Actualités en attente</p>
                                    <h3 class="mb-0"><?php echo $number_pending_news; ?></h3>
                                </div>
                                <i class="bi bi-clock text-warning bg-warning-subtle"></i>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="stats-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted mb-0">Actualités publiées</p>
                                    <h3 class="mb-0"><?php echo $number_published_news; ?></h3>
                                </div>
                                <i class="bi bi-check-circle text-success bg-success-subtle"><?php  ?></i>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="stats-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted mb-0">Total annonces</p>
                                    <h3 class="mb-0"><?php echo $total_news; ?></h3>
                                </div>
                                <i class="bi bi-megaphone text-primary bg-primary-subtle"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table Section -->
                <div class="section">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Titre</th>
                                    <th>Auteur</th>
                                    <th>Contenu</th>
                                    <th>Date</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Affichage des données accédées sur le dashboard-->
                                <?php foreach ($get_annonce as $actu): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($actu['titre_annonce']) ?></td>
                                        <td><?= htmlspecialchars($actu['prenom']) . ' ' . htmlspecialchars($actu['nom']) ?></td>
                                        <td><?= htmlspecialchars($actu['contenu_annonce']) ?></td>
                                        <td><?= date('d/m/Y', strtotime($actu['date_pub_annonce'])) ?></td>
                                        <td><span class="badge <?= strtolower($actu['etat_annonce']) == 'publié' ? 'bg-success' : 'bg-warning' ?>"><?= htmlspecialchars($actu['etat_annonce']) ?></span></td>
                                        <td class="action-icons">

                                            <!-------------------------------------------- FORMULAIRE POUR GERER L'EDITION DES  ACTUALITES ------------------------------------------>
                                            <form action="../annoncements/updatenews.php" method="GET" style="display: inline;">
                                                <input type="hidden" name="id_annonce" value="<?= $actu['id_annonce']; ?>">
                                                <button type="submit" onclick="return confirm('Modifier cette actualité ?')">
                                                    <i class="bi bi-pencil-square text-primary"></i>
                                                </button>
                                            </form>


                                            <!---------------------------------------------------------------------------------------------------------------------------------------->

                                            <!-------------------------------------------- FORMULAIRE POUR GERER PUBLICATION DES ACTUALITES ------------------------------------------>
                                            <form method="POST" action="../annoncements/validatenews.php" style="display: inline;">
                                                <input type="hidden" name="id_annonce" value="<?= $actu['id_annonce']; ?>">
                                                <button type="submit" class="btn btn-link p-0"
                                                    onclick="return confirm ('Actualité validée !')">
                                                    <i class="bi bi-check-circle text-success"></i>
                                                </button>
                                            </form>
                                            <!---------------------------------------------------------------------------------------------------------------------------------------->


                                            <!---------------------------------------------- FORMULAIRE POUR GERER LA SUPPRESSION DES ACTUALITES ------------------------------------->
                                            <form method="POST" action="../annoncements/deletenews.php" style="display: inline;">
                                                <input type="hidden" name="id_annonce" value="<?= $actu['id_annonce'] ?>">
                                                <button type="submit" class="btn btn-link p-0"
                                                    onclick="return confirm('Vous êtes sur le point de supprimer une actualité, voulez-vous poursuivre ?')">
                                                    <i class="bi bi-trash text-danger"></i>
                                                </button>
                                            </form>
                                            <!--------------------------------------------------------------------------------------------------------------------------------------->
                                            </form>

                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>

                        </table>
                    </div>

                    <!-- Pagination -->
                    <nav aria-label="Navigation des pages" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <!-- Précédent -->
                            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= $page - 1 ?>" aria-label="Previous">
                                    <span aria-hidden="true">&laquo; Précédent</span>
                                </a>
                            </li>

                            <!-- Numéros de page -->
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>

                            <!-- Suivant -->
                            <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= $page + 1 ?>" aria-label="Next">
                                    <span aria-hidden="true">Suivant &raquo;</span>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </main>
    </div>
</div>

<?php
//--------------------------------------------------------------POUR LE FILTRAGE ULTRA COOL------------------------------------

$where = [];
$params = [];
$annonces = [];


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Filtre par état
    if (!empty($_POST['etat'])) {
        $where[] = "etat_annonce = ?";
        $params[] = $_POST['etat'];
    }

    // Filtre par date
    if (!empty($_POST['date'])) {
        switch ($_POST['date']) {
            case 'today':
                $where[] = "DATE(date_pub_annonce) = CURDATE()";
                break;
            case 'week':
                $where[] = "YEARWEEK(date_pub_annonce) = YEARWEEK(NOW())";
                break;
            case 'month':
                $where[] = "MONTH(date_pub_annonce) = MONTH(NOW()) AND YEAR(date_pub_annonce) = YEAR(NOW())";
                break;
            case 'year':
                $where[] = "YEAR(date_pub_annonce) = YEAR(NOW())";
                break;
        }
    }

    // Filtre par recherche
    if (!empty($_POST['search'])) {
        $where[] = "(titre_annonce LIKE ? OR contenu_annonce LIKE ?)";
        $searchTerm = '%' . $_POST['search'] . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    // requête
    $sql = "SELECT * FROM annonce";
    if (!empty($where)) {
        $sql .= " WHERE " . implode(" AND ", $where);
    }
    $sql .= " ORDER BY date_pub_annonce DESC";

    try {
        $requete = $dbase_connected->prepare($sql);
        $requete->execute($params);
        $annonces = $requete->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erreur de filtrage : " . $e->getMessage());
        $error_message = "Erreur lors de l'application des filtres";
    }
}

try {
    // REQUETE principale
    $sql = "SELECT annonce.*, utilisateur.* 
           FROM annonce 
           JOIN moderateur ON annonce.id_moderateur = moderateur.id_moderateur 
           JOIN utilisateur ON moderateur.id_utilisateur = utilisateur.id_utilisateur 
           ORDER BY annonce.date_pub_annonce DESC 
           LIMIT :limit OFFSET :offset";
    $stmt = $dbase_connected->prepare($sql);
    $stmt->bindValue(':limit', $annonces_par_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $get_annonce = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Erreur de base de données: " . $e->getMessage());
    $get_annonce = [];
    $total_pages = 1;
    $page = 1;
}
?>

<div class="container mt-4">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Titre</th>
                <th>Auteur</th>
                <th>Contenu</th>
                <th>Date</th>
                <th>État</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($annonces)): ?>
                <?php foreach ($annonces as $annonce): ?>
                    <tr>
                        <td><?= htmlspecialchars($annonce['titre_annonce']) ?></td>
                        <td><?= htmlspecialchars($actu['prenom']) . ' ' . htmlspecialchars($actu['nom']) ?></td>
                        <td><?= htmlspecialchars($annonce['contenu_annonce']) ?></td>
                        <td><?= date('d/m/Y', strtotime($annonce['date_pub_annonce'])) ?></td>
                        <td>
                            <span class="badge bg-<?= $annonce['etat_annonce'] === 'publié' ? 'success' : 'warning' ?>">
                                <?= htmlspecialchars($annonce['etat_annonce']) ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="text-center text-muted">
                        Aucune annonce trouvée avec ces critères
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php require_once '../../components/footer/footer.php'; ?>