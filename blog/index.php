<?php
require_once "../components/header/header.php";
require_once('../config/database.php');

$stmt = $dbase_connected->query("SELECT id_publication_blog, titre, contenu, categorie_blog, date_creation, photo FROM publication_blog ORDER BY date_creation DESC");

//récupération des infos sur le modérateur
$check = $dbase_connected->query("SELECT publication_blog.*, utilisateur.*
    FROM publication_blog
    JOIN moderateur ON publication_blog.id_moderateur = moderateur.id_moderateur
    JOIN utilisateur ON moderateur.id_utilisateur = utilisateur.id_utilisateur
    ORDER BY publication_blog.date_creation DESC;");
$get_mod = $check->fetchAll(PDO::FETCH_ASSOC);

//récupération des infos sur l'author
$id_utilisateur = $_SESSION['user']['id'];

$requete = $dbase_connected->prepare("SELECT nom, prenom, photo_profile FROM utilisateur WHERE id_utilisateur=?;");
$requete->execute([$id_utilisateur]);

$get_author = $requete->fetch(PDO::FETCH_ASSOC);
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once "../components/sidebar/sidebar.php"; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 blog-main">
            <div class="blog-content">
                <div class="blog-header">
                    <h1 class="h2">Blog Kadodo 😋</h1>
                </div>

                <!-- <a href="addblog.php" class="btn btn-primary d-flex justify-content-center align-items-center">
                    <i class="bi bi-plus me-2"></i> Nouvelle publication
                </a> -->

                <div class="content-wrapper">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="d-flex gap-3">
                            <select class="form-select">
                                <option>Toutes les catégories</option>
                            </select>
                            <select class="form-select">
                                <option>Tous les auteurs</option>
                            </select>
                        </div>
                    </div>

                    <div class="search-container mb-4">
                        <input type="text" class="form-control" placeholder="Rechercher un article...">
                        <svg xmlns="http://www.w3.org/2000/svg" class="search-icon" viewBox="0 0 16 16">
                            <path d="M11.742 10.344a6.5 6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z" />
                        </svg>
                    </div>

                    <div class="blog-grid">
                        <?php if ($stmt->rowCount() > 0): ?>
                            <?php while ($row = $stmt->fetch()): ?>
                                <article class="blog-card">
                                    <img src="<?= UPLOADS_PATH ?>/blog/<?= htmlspecialchars(getBlogImagePath($row['photo'])) ?>"
                                        alt="<?= htmlspecialchars($row['titre']) ?>"
                                        class="blog-image">
                                    <div class="blog-content">
                                        <span class="category-badge <?= strtolower(str_replace(' ', '-', htmlspecialchars($row['categorie_blog']))); ?>">
                                            <?= htmlspecialchars($row['categorie_blog'], ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                        <h2><?= htmlspecialchars($row['titre'], ENT_QUOTES, 'UTF-8'); ?></h2>
                                        <p><?= htmlspecialchars(substr($row['contenu'], 0, 100), ENT_QUOTES, 'UTF-8') . '...'; ?></p>
                                        <div class="blog-footer">
                                            <div class="author">
                                                <img src="<?= UPLOADS_PATH ?>/avatar/<?= htmlspecialchars(getAvatarPath($get_author['photo_profile'])) ?>"
                                                    alt="<?= htmlspecialchars($get_author['prenom'] . ' ' . $get_author['nom']) ?>"
                                                    class="author-avatar">
                                                <div>
                                                    <span class="author-name"><?= htmlspecialchars($get_author['prenom'] . ' ' . $get_author['nom'], ENT_QUOTES, 'UTF-8'); ?></span>
                                                    <span class="date">
                                                        <?= date('d M Y', strtotime($row['date_creation'])); ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <a href="view_blog.php?id=<?= $row['id_publication_blog'] ?>" class="read-more">
                                                Lire plus
                                            </a>
                                        </div>
                                    </div>
                                </article>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p>Aucun article trouvé.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
<?php require_once "../components/footer/footer.php"; ?>