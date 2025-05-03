<?php
require_once '../utils/auth.php';
require_once '../config/database.php';

$pageTitle = "Annonces";
require_once '../components/header/header.php';

// Récupération des annonces avec pagination
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$annonces_par_page = 10;
$offset = ($page - 1) * $annonces_par_page;

try {
    $db = Database::getInstance()->getConnection();

    // Compte total des annonces
    $total = $db->query("SELECT COUNT(*) FROM annonce WHERE etat_annonce = 'publié'")->fetchColumn();
    $total_pages = ceil($total / $annonces_par_page);

    // Récupération des annonces
    $sql = "SELECT annonce.*, DATE_FORMAT(date_pub_annonce, '%d/%m/%Y à %H:%i') as date_formattee,
            utilisateur.nom, utilisateur.prenom 
            FROM annonce 
            JOIN moderateur ON annonce.id_moderateur = moderateur.id_moderateur
            JOIN utilisateur ON moderateur.id_utilisateur = utilisateur.id_utilisateur
            WHERE etat_annonce = 'publié'
            ORDER BY date_pub_annonce DESC 
            LIMIT :limit OFFSET :offset";

    $stmt = $db->prepare($sql);
    $stmt->bindValue(':limit', $annonces_par_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $annonces = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log($e->getMessage());
    $annonces = [];
    $total_pages = 1;
}
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once '../components/sidebar/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4" style="margin-top: 50px">
            <div class="container mt-4">
                <h1 class="mb-4">Annonces</h1>

                <?php if (empty($annonces)): ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Aucune annonce disponible pour le moment.
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($annonces as $annonce): ?>
                            <div class="col-12 mb-4">
                                <div class="card announcement-card">
                                    <div class="card-body">
                                        <h5 class="card-title"><?= htmlspecialchars($annonce['titre_annonce']) ?></h5>
                                        <p class="card-text"><?= nl2br(htmlspecialchars($annonce['contenu_annonce'])) ?></p>
                                        <div class="announcement-meta">
                                            <small class="text-muted">
                                                <i class="bi bi-person me-1"></i>
                                                <?= htmlspecialchars($annonce['prenom'] . ' ' . $annonce['nom']) ?>
                                            </small>
                                            <small class="text-muted">
                                                <i class="bi bi-clock me-1"></i>
                                                <?= $annonce['date_formattee'] ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($total_pages > 1): ?>
                        <nav aria-label="Pagination des annonces">
                            <ul class="pagination justify-content-center">
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<?php require_once '../components/footer/footer.php'; ?>