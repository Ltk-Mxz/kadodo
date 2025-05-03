<?php
$pageTitle = "Mes Cours";
require_once "../../utils/auth.php";
requireRole('professeur');
require_once "handlers/get_professor_courses.php";
require_once "../../components/header/header.php";

try {
  if (!isset($_SESSION['user']['id'])) {
    throw new Exception("Session utilisateur non trouvée");
  }

  $courses = get_professor_courses($_SESSION['user']['id']);

  if (empty($courses)) {
    $message = '<div class="alert alert-info">Aucun cours n\'a été attribué à ce professeur.' . $courses . '</div>';
  }
} catch (Exception $e) {
  $message = '<div class="alert alert-danger">Une erreur est survenue: ' . $e->getMessage() . '</div>';
}
?>

<div class="main-wrapper">
  <?php require_once "../../components/sidebar/sidebar.php" ?>

  <main class="main-content">
    <div class="content-wrapper">
      <?php if (isset($message)) echo $message; ?>

      <div class="d-flex align-items-center justify-content-between mb-4">
        <h1 class="content-title mb-0">Mes Cours</h1>
        <div class="filters-section">
          <form class="d-flex gap-3">
            <div class="search-container">
              <input class="form-control" type="search" placeholder="Rechercher un cours..." id="searchInput">
              <i class="bi bi-search search-icon"></i>
            </div>
            <select class="form-select" id="filterNiveau">
              <option value="">Tous les niveaux</option>
              <option>L1</option>
              <option>L2</option>
              <option>L3</option>
            </select>
          </form>
        </div>
      </div>

      <div class="row g-4">
        <?php if (!empty($courses)): ?>
          <?php foreach ($courses as $course): ?>
            <div class="col-md-4 course-item">
              <div class="course-card">
                <div class="course-header">
                  <h2 class="course-title"><?= htmlspecialchars($course['nom_cours']) ?></h2>
                  <span class="course-status">En cours</span>
                </div>
                <div class="course-info">
                  <p>
                    <i class="bi bi-people"></i>
                    <?= $course['nombre_etudiants'] ?? 0 ?> étudiants
                  </p>
                  <p>
                    <i class="bi bi-building"></i>
                    Salle: <?= htmlspecialchars($course['salle'] ?? 'Non assignée') ?>
                  </p>
                  <p>
                    <i class="bi bi-clock"></i>
                    <?= $course['nombre_heures'] ?? 0 ?> heures par semaine
                  </p>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </main>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const filterNiveau = document.getElementById('filterNiveau');
    const courseItems = document.querySelectorAll('.course-item');

    function filterCourses() {
      const searchTerm = searchInput.value.toLowerCase();
      const selectedNiveau = filterNiveau.value.toLowerCase();

      courseItems.forEach(item => {
        const courseName = item.querySelector('.course-title').textContent.toLowerCase();
        const niveau = item.querySelector('.bi-bookmark').parentNode.textContent.toLowerCase();

        const matchesSearch = courseName.includes(searchTerm);
        const matchesNiveau = selectedNiveau === '' || niveau.includes(selectedNiveau);

        item.style.display = (matchesSearch && matchesNiveau) ? '' : 'none';
      });
    }

    searchInput.addEventListener('input', filterCourses);
    filterNiveau.addEventListener('change', filterCourses);
  });
</script>

<?php require_once "../../components/footer/footer.php" ?>