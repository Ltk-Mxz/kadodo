<?php
require_once "../components/header/header.php";
require_once('../config/database.php');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = (int)$_GET['id'];

// Récupérer l'article et les infos de l'auteur
$stmt = $dbase_connected->prepare("
    SELECT publication_blog.*, utilisateur.nom, utilisateur.prenom, utilisateur.photo_profile
    FROM publication_blog
    JOIN moderateur ON publication_blog.id_moderateur = moderateur.id_moderateur
    JOIN utilisateur ON moderateur.id_utilisateur = utilisateur.id_utilisateur
    WHERE publication_blog.id_publication_blog = ?
");

$stmt->execute([$id]);
$article = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$article) {
    header('Location: index.php');
    exit;
}
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once "../components/sidebar/sidebar.php"; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 blog-main">
            <div class="blog-content">
                <div class="article-header mb-4">
                    <a href="index.php" class="btn btn-outline-primary mb-4">
                        <i class="bi bi-arrow-left"></i> Retour aux articles
                    </a>
                    <h1 class="display-4"><?= htmlspecialchars($article['titre']) ?></h1>

                    <div class="meta-info d-flex align-items-center gap-4 mt-3">
                        <span class="badge bg-primary px-3 py-2">
                            <?= htmlspecialchars($article['categorie_blog']) ?>
                        </span>
                        <div class="author d-flex align-items-center gap-2">
                            <img src="<?= UPLOADS_PATH ?>/avatar/<?= htmlspecialchars(getAvatarPath($article['photo_profile'])) ?>"
                                alt="Photo de <?= htmlspecialchars($article['prenom'] . ' ' . $article['nom']) ?>"
                                class="rounded-circle"
                                style="width: 40px; height: 40px; object-fit: cover;">
                            <div>
                                <div class="fw-bold"><?= htmlspecialchars($article['prenom'] . ' ' . $article['nom']) ?></div>
                                <div class="text-muted"><?= date('d M Y', strtotime($article['date_creation'])) ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($article['photo']): ?>
                    <img src="<?= UPLOADS_PATH ?>/blog/<?= htmlspecialchars(getBlogImagePath($article['photo'])) ?>"
                        alt="<?= htmlspecialchars($article['titre']) ?>"
                        class="img-fluid rounded mb-4"
                        style="max-height: 500px; width: 100%; object-fit: cover;">
                <?php endif; ?>

                <div class="article-content">
                    <div class="fs-5 lh-lg">
                        <?= nl2br(htmlspecialchars($article['contenu'])) ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once "../components/footer/footer.php"; ?>