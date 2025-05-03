<?php
$pageTitle = "Forum";
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../utils/forum.php';
require_once __DIR__ . '/../components/header/header.php';
?>

<body data-user-id="<?= $_SESSION['user']['id'] ?>" data-user-role="<?= $_SESSION['user']['type'] ?>">

  <div class="container-fluid">
    <div class="row">
      <!-- Sidebar -->
      <?php require_once __DIR__ . '/../components/sidebar/sidebar.php'; ?>

      <!-- Main content -->
      <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
        <div class="forum-container">
          <div class="forum-header d-flex justify-content-between align-items-center">
            <div>
              <h1>Forum Kadodo 😋</h1>
              <p class="text-muted mb-0">Discutez, partagez et apprenez ensemble</p>
            </div>
            <button type="button" class="btn create-topic-btn">
              <i class="bi bi-plus-lg"></i>
              <span>Nouveau Sujet</span>
            </button>
          </div>

          <div class="forum-content">
            <div class="categories-section">
              <h2 class="mb-4">Catégories</h2>
              <?php
              $db = Database::getInstance()->getConnection();
              $stmt = $db->prepare("
                SELECT 
                    c.*,
                    COALESCE(t.nb_topics, 0) as nb_topics,
                    COALESCE(r.nb_comments, 0) as nb_comments
                FROM categorie_forum c
                LEFT JOIN (
                    SELECT 
                        id_categorie_forum,
                        COUNT(*) as nb_topics
                    FROM publication_forum 
                    GROUP BY id_categorie_forum
                ) t ON c.id_categorie_forum = t.id_categorie_forum
                LEFT JOIN (
                    SELECT 
                        pf.id_categorie_forum,
                        COUNT(cm.id_commentaire) as nb_comments
                    FROM commentaire cm
                    JOIN publication_forum pf ON cm.id_publication_forum = pf.id_publication_forum
                    GROUP BY pf.id_categorie_forum
                ) r ON c.id_categorie_forum = r.id_categorie_forum
                ORDER BY c.nom_categorie_forum
              ");
              $stmt->execute();
              $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

              foreach ($categories as $category):
                $totalActivity = (int)$category['nb_topics'] + (int)$category['nb_comments'];
              ?>
                <div class="category-item" data-category-id="<?= $category['id_categorie_forum'] ?>">
                  <div class="category-info">
                    <h3 class="category-title"><?= htmlspecialchars($category['nom_categorie_forum']) ?></h3>
                    <p class="category-count"><?= $totalActivity ?> activité<?= $totalActivity > 1 ? 's' : '' ?></p>
                  </div>
                  <div class="category-icon">
                    <i class="fas fa-<?= getCategoryIcon($category['nom_categorie_forum']) ?>"></i>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>

            <div class="recent-topics-section">
              <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 id="topics-title">Sélectionnez une catégorie</h2>
                <div class="dropdown">
                  <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    Trier par
                  </button>
                  <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#">Plus récents</a></li>
                    <li><a class="dropdown-item" href="#">Plus actifs</a></li>
                    <li><a class="dropdown-item" href="#">Plus populaires</a></li>
                  </ul>
                </div>
              </div>
              <div class="initial-message">
                <p class="text-center text-muted mt-5">
                  <i class="fas fa-hand-point-left fa-2x mb-3"></i><br>
                  Cliquez sur une catégorie pour voir les sujets
                </p>
              </div>
              <div class="topics-loading d-none">Chargement des sujets...</div>
              <div class="topics-list"></div>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>

  <?php require_once __DIR__ . '/../components/footer/footer.php'; ?>

  <!-- Modal Nouveau Sujet -->
  <div class="modal fade" id="newTopicModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Nouveau Sujet</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form id="newTopicForm">
          <div class="modal-body">
            <div id="submitError" class="alert alert-danger d-none"></div>
            <div class="mb-3">
              <label class="form-label">Catégorie</label>
              <select class="form-select" name="category" required>
                <?php foreach ($categories as $category): ?>
                  <option value="<?= $category['id_categorie_forum'] ?>">
                    <?= htmlspecialchars($category['nom_categorie_forum']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Titre</label>
              <input type="text" class="form-control" name="title" required maxlength="100">
            </div>
            <div class="mb-3">
              <label class="form-label">Contenu</label>
              <div class="editor-container">
                <textarea id="topicContent" name="content"></textarea>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
            <button type="submit" class="btn btn-primary" id="submitTopic">Publier</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="modal fade" id="viewTopicModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title topic-title"></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="topic-details mb-4">
            <div class="topic-author-info mb-3"></div>
            <div class="topic-content-text"></div>
          </div>
          <hr>
          <h6 class="mb-3">Réponses <span class="replies-count"></span></h6>
          <div class="replies-list mb-4"></div>
          <form id="replyForm" class="mt-4">
            <input type="hidden" name="topic_id">
            <div class="mb-3">
              <label class="form-label">Votre réponse</label>
              <textarea id="replyContent" name="content" class="form-control" rows="4" required></textarea>
            </div>
            <div class="text-end">
              <button type="submit" class="btn btn-primary">Répondre</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script src="js/forum.js"></script>

  <script>
    // Ajouter des animations smooth au scroll
    document.querySelectorAll('.topic-item, .category-item').forEach(item => {
      item.addEventListener('click', () => {
        item.style.transform = 'scale(0.98)';
        setTimeout(() => item.style.transform = '', 150);
      });
    });
  </script>
</body>