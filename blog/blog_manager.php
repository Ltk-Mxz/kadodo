<?php
require_once("../components/header/header.php");
require_once("../config/database.php");

$annonces_par_page = 5;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $annonces_par_page;

$where = [];
$params = [];

// Filtrage par date
if (!empty($_GET['date'])) {
    $valid_dates = ['today', 'week', 'month', 'year'];
    if (in_array($_GET['date'], $valid_dates)) {
        switch ($_GET['date']) {
            case 'today':
                $where[] = "DATE(publication_blog.date_creation) = CURDATE()";
                break;
            case 'week':
                $where[] = "YEARWEEK(publication_blog.date_creation) = YEARWEEK(NOW())";
                break;
            case 'month':
                $where[] = "MONTH(publication_blog.date_creation) = MONTH(NOW()) AND YEAR(publication_blog.date_creation) = YEAR(NOW())";
                break;
            case 'year':
                $where[] = "YEAR(publication_blog.date_creation) = YEAR(NOW())";
                break;
        }
    }
}

// Filtrage par recherche 
if (!empty($_GET['search'])) {
    $where[] = "(publication_blog.titre LIKE :search OR publication_blog.contenu LIKE :search)";
    $params[':search'] = '%' . trim($_GET['search']) . '%';
}

$sql = "SELECT publication_blog.*, utilisateur.prenom, utilisateur.nom 
            FROM publication_blog 
            JOIN moderateur ON publication_blog.id_moderateur = moderateur.id_moderateur 
            JOIN utilisateur ON moderateur.id_utilisateur = utilisateur.id_utilisateur";

if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$count_sql = "SELECT COUNT(*) FROM publication_blog";
if (!empty($where)) {
    $count_sql .= " WHERE " . implode(" AND ", $where);
}

$count_query = $dbase_connected->prepare($count_sql);
$count_query->execute($params);
$total_annonces = $count_query->fetchColumn();
$total_pages = ceil($total_annonces / $annonces_par_page);

$sql .= " ORDER BY publication_blog.date_creation DESC LIMIT :limit OFFSET :offset";
$params[':limit'] = $annonces_par_page;
$params[':offset'] = $offset;

$stmt = $dbase_connected->prepare($sql);

foreach ($params as $key => $value) {
    $param_type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
    $stmt->bindValue($key, $value, $param_type);
}

$stmt->execute();
$annonces = $stmt->fetchAll(PDO::FETCH_ASSOC);

$query_params = $_GET;
unset($query_params['page']);
$query_string = http_build_query($query_params);


//<!-------------------------------------------- Petites requêtes pour compter les publications de blog -------------------------------------------->

//pub total
$pourcompter = $dbase_connected->query("SELECT COUNT(*) FROM publication_blog");
$total_pubs = $pourcompter->fetchColumn();

//annonces
$pourcompter_annonce = $dbase_connected->query("SELECT COUNT(*) FROM publication_blog WHERE categorie_blog LIKE '%Annonces%'");
$total_annonces = $pourcompter_annonce->fetchColumn();

//conseils
$pourcompter_conseil = $dbase_connected->query("SELECT COUNT(*) FROM publication_blog WHERE categorie_blog LIKE '%Conseils d%'");
$total_conseils = $pourcompter_conseil->fetchColumn();

//evenements
$pourcompter_even = $dbase_connected->query("SELECT COUNT(*) FROM publication_blog WHERE categorie_blog LIKE '%nement%'");
$total_even = $pourcompter_even->fetchColumn();

?>

<style>
    .blog-manager-container {
        padding: 2rem;
        margin-left: var(--sidebar-width);
    }

    .dashboard-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: linear-gradient(to right, #ffffff, #f8f9fa);
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        transition: transform 0.2s;
    }

    .stat-card:hover {
        transform: translateY(-5px);
    }

    .blog-content {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        padding: 2rem;
    }

    .filters-bar {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 2rem;
    }

    .action-buttons {
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        z-index: 1000;
    }
</style>

<div class="layout-wrapper">
    <!-- Sidebar -->
    <?php require_once '../components/sidebar/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="blog-manager-container">
        <div class="content-header">
            <h1 class="h3 mb-4">Gestion du Blog</h1>

            <!-- Actions principales -->
            <div class="d-flex gap-3 mb-4">
                <a href="addblog.php" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-2"></i>Nouvel Article
                </a>
                <a href="../moderators/dashboard/index.php" class="btn btn-outline-primary">
                    <i class="bi bi-pencil-square me-2"></i>Gérer les actualités
                </a>
            </div>
        </div>

        <!-- Statistiques -->
        <div class="dashboard-stats">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-0">Annonces</p>
                        <h3 class="mb-0"><?= $total_annonces; ?></h3>
                    </div>
                    <i class="bi bi-megaphone text-primary bg-primary-subtle"></i>
                </div>
            </div>

            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-0">Conseils</p>
                        <h3 class="mb-0"><?= $total_conseils; ?></h3>
                    </div>
                    <i class="bi bi-lightbulb text-warning bg-warning-subtle p-2 rounded-circle"></i>
                </div>
            </div>

            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-0">Évènements</p>
                        <h3 class="mb-0"><?= $total_even; ?></h3>
                    </div>
                    <i class="bi bi-calendar-event"></i>
                </div>
            </div>

            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-0">Blog's pubs</p>
                        <h3 class="mb-0"><?= $total_pubs; ?></h3>
                    </div>
                    <i class="bi bi-journal-text text-primary bg-primary-subtle"></i>
                </div>
            </div>
        </div>

        <!-- Filtres -->
        <div class="filters-bar">
            <form method="GET" action="" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="date" class="form-label">Période</label>
                    <select name="date" id="date" class="form-select">
                        <option value="">Toutes dates</option>
                        <option value="today" <?= ($_GET['date'] ?? '') === 'today' ? 'selected' : '' ?>>Aujourd'hui</option>
                        <option value="week" <?= ($_GET['date'] ?? '') === 'week' ? 'selected' : '' ?>>Cette semaine</option>
                        <option value="month" <?= ($_GET['date'] ?? '') === 'month' ? 'selected' : '' ?>>Ce mois</option>
                        <option value="year" <?= ($_GET['date'] ?? '') === 'year' ? 'selected' : '' ?>>Cette année</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="search" class="form-label">Recherche</label>
                    <div class="input-group">
                        <input type="text" name="search" id="search" class="form-control"
                            value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"
                            placeholder="Rechercher...">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i> Filtrer
                        </button>
                    </div>
                </div>

                <div class="col-md-3 d-grid">
                    <a href="?" class="btn btn-outline-secondary">Réinitialiser</a>
                </div>
            </form>
        </div>

        <!-- Table des articles -->
        <div class="blog-content">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Titre</th>
                            <th>Catégorie</th>
                            <th>Auteur</th>
                            <th>Contenu</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($annonces as $info): ?>
                            <tr>
                                <td><?= htmlspecialchars($info['titre'], ENT_NOQUOTES) ?></td>
                                <td><?= htmlspecialchars($info['categorie_blog'], ENT_NOQUOTES) ?></td>
                                <td><?= htmlspecialchars($info['prenom'] . ' ' . $info['nom'], ENT_NOQUOTES) ?></td>
                                <td><?= htmlspecialchars(substr($info['contenu'], 0, 50)) ?>...</td>
                                <td><?= date('d/m/Y', strtotime($info['date_creation'])) ?></td>
                                <td class="action-icons">
                                    <form action="update_blog.php" method="POST" style="display: inline;">
                                        <input type="hidden" name="id_publication_blog" value="<?= $info['id_publication_blog']; ?>">
                                        <button type="submit" class="btn btn-link p-0">
                                            <i class="bi bi-pencil-square text-primary"></i>
                                        </button>
                                    </form>
                                    <form method="POST" action="delblog.php" style="display: inline;">
                                        <input type="hidden" name="id_publication_blog" value="<?= $info['id_publication_blog']; ?>">
                                        <button type="submit" class="btn btn-link p-0" onclick="return confirm('Voulez-vous supprimer cet article ?')">
                                            <i class="bi bi-trash text-danger"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <nav aria-label="Pagination">
                        <ul class="pagination justify-content-center mt-4">
                            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link" href="?<?= $query_string ?>&page=<?= $page - 1 ?>" aria-label="Précédent">
                                    &laquo;
                                </a>
                            </li>

                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                    <a class="page-link" href="?<?= $query_string ?>&page=<?= $i ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                                <a class="page-link" href="?<?= $query_string ?>&page=<?= $page + 1 ?>" aria-label="Suivant">
                                    &raquo;
                                </a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<?php require_once '../components/footer/footer.php'; ?>