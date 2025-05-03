<?php

$pageTitle = "Mes Salles de Classe";

require_once "../../utils/auth.php";

requireRole('professeur');

if (!isset($_SESSION['user']['id'])) {
    header('Location: /myschoolface/auth/login');
    exit();
}

require_once "handlers/get_professor_classes.php";
require_once "../../components/header/header.php";

try {
    if (!isset($_SESSION['user']['id'])) {
        throw new Exception("Session utilisateur non trouvée");
    }

    $classes = get_professor_classes($_SESSION['user']['id']);

    if (empty($classes)) {
        $message = '<div class="alert alert-info">Aucune classe n\'a été attribuée à ce professeur.</div>';
    }
} catch (Exception $e) {
    $message = '<div class="alert alert-danger">Une erreur est survenue lors du chargement des classes: ' . $e->getMessage() . '</div>';
    error_log("Error in professor classes page: " . $e->getMessage());
}
?>

<div class="main-wrapper">
    <?php require_once "../../components/sidebar/sidebar.php" ?>

    <main class="main-content">
        <div class="content-wrapper">
            <!-- Afficher les messages d'erreur/info -->
            <?php if (isset($message)) echo $message; ?>

            <!-- Header -->
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h1 class="content-title mb-0">Mes Classes</h1>
                <div class="filters-section">
                    <form class="d-flex gap-3">
                        <div class="search-container">
                            <input class="form-control" type="search" placeholder="Rechercher une classe..." id="searchInput">
                            <i class="bi bi-search search-icon"></i>
                        </div>
                        <select class="form-select" id="levelFilter">
                            <option selected value="">Tous les niveaux</option>
                            <option>Licence 1</option>
                            <option>Licence 2</option>
                            <option>Licence 3</option>
                        </select>
                    </form>
                </div>
            </div>

            <!-- Classes Grid -->
            <div class="row g-4" id="classesGrid">
                <?php if (!empty($classes)): ?>
                    <?php foreach ($classes as $classe): ?>
                        <div class="col-md-4 class-item">
                            <div class="class-card">
                                <div class="class-header">
                                    <h2 class="class-title"><?= htmlspecialchars($classe['salle']) ?></h2>
                                    <span class="class-status">En cours</span>
                                </div>
                                <div class="class-info">
                                    <p><i class="bi bi-book"></i><?= htmlspecialchars($classe['nom_cours']) ?></p>
                                    <p><i class="bi bi-clock"></i><?= htmlspecialchars($classe['nombre_heures'] ?? 2) ?> Heures par semaine</p>
                                    <?php if (isset($classe['date_debut']) && isset($classe['date_fin'])): ?>
                                        <p><i class="bi bi-calendar"></i>Du <?= date('d/m/Y', strtotime($classe['date_debut'])) ?>
                                            au <?= date('d/m/Y', strtotime($classe['date_fin'])) ?></p>
                                    <?php endif; ?>
                                    <?php if (isset($classe['capacite'])): ?>
                                        <p><i class="bi bi-people"></i>Capacité: <?= htmlspecialchars($classe['capacite']) ?> places</p>
                                    <?php endif; ?>
                                    <?php if (isset($classe['equipements']) && !empty($classe['equipements'])): ?>
                                        <p><i class="bi bi-tools"></i>Équipements: <?= htmlspecialchars($classe['equipements']) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<!-- Ajout du JavaScript pour la recherche et le filtrage -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const levelFilter = document.getElementById('levelFilter');
        const classItems = document.querySelectorAll('.class-item');

        function filterClasses() {
            const searchTerm = searchInput.value.toLowerCase();
            const selectedLevel = levelFilter.value.toLowerCase();

            classItems.forEach(item => {
                const className = item.querySelector('.class-title').textContent.toLowerCase();
                const shouldShow = className.includes(searchTerm) &&
                    (selectedLevel === '' || className.includes(selectedLevel));
                item.style.display = shouldShow ? '' : 'none';
            });
        }

        searchInput.addEventListener('input', filterClasses);
        levelFilter.addEventListener('change', filterClasses);
    });
</script>

<?php require_once "../../components/footer/footer.php" ?>