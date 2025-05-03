<?php
$pageTitle = "Mes Cours";
require_once "../../components/header/header.php";
require_once '../../config/database.php';

$db = Database::getInstance()->getConnection();
$id = $_SESSION['user']['id'];

// Check if user is student
$checkStudent = "SELECT e.id_etudiant 
                FROM etudiant e 
                WHERE e.id_utilisateur = ?";
$stmt = $db->prepare($checkStudent);
$stmt->execute([$id]);
$isStudent = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$isStudent) {
    die('<div class="alert alert-danger">Accès non autorisé. Utilisateur non trouvé.</div>');
}

// Get student's filière
$sqlFiliere = "SELECT f.*, e.id_etudiant 
               FROM filiere f
               JOIN etudiant e ON e.id_filiere = f.id_filiere 
               WHERE e.id_utilisateur = ?";

$stmt = $db->prepare($sqlFiliere);
$stmt->execute([$id]);
$filiereT = $stmt->fetch(PDO::FETCH_ASSOC);

// Get courses if filière exists
if ($filiereT) {
    $filiere = $filiereT['id_filiere'];

    // Get student's courses
    $requeteCours = "SELECT c.*, u.nom, u.prenom 
                     FROM cours c
                     JOIN attribution_cours ac ON ac.id_cours = c.id_cours
                     JOIN professeur p ON p.id_professeur = c.id_professeur
                     JOIN utilisateur u ON u.id_utilisateur = p.id_utilisateur
                     WHERE ac.id_filiere = ?";

    $stmt = $db->prepare($requeteCours);
    $stmt->execute([$filiere]);
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $courses = [];
}
?>

<div class="overlay"></div>
<div class="main-wrapper">
    <?php require_once "../../components/sidebar/sidebar.php" ?>

    <main class="main-content">
        <div class="content-wrapper">
            <div class="header-section mb-4">
                <h1 class="content-title mb-3">Mes Cours</h1>
                <?php if (!$filiereT): ?>
                    <div class="alert alert-warning">
                        Aucune filière trouvée pour cet étudiant.
                    </div>
                <?php else: ?>
                    <div class="row g-4">
                        <?php foreach ($courses as $course) { ?>
                            <div class="col-md-4">
                                <div class="document-card">
                                    <div class="document-type course">Cours</div>
                                    <div class="document-info">
                                        <h3><?= htmlspecialchars($course['nom_cours']) ?></h3>
                                        <p class="professor">Prof: <?= htmlspecialchars($course['prenom'] . ' ' . $course['nom']) ?></p>
                                        <div class="course-details">
                                            <p><i class="bi bi-geo-alt"></i> Salle: <?= htmlspecialchars($course['salle'] ?? 'Non définie') ?></p>
                                            <p><i class="bi bi-calendar-range"></i> Du: <?= $course['date_debut'] ? date('d/m/Y', strtotime($course['date_debut'])) : 'Non défini' ?></p>
                                            <p><i class="bi bi-calendar-range"></i> Au: <?= $course['date_fin'] ? date('d/m/Y', strtotime($course['date_fin'])) : 'Non défini' ?></p>
                                            <p><i class="bi bi-clock"></i> <?= htmlspecialchars($course['nombre_heures'] ?? '0') ?> heures</p>
                                            <p><i class="bi bi-file-earmark"></i> <?= htmlspecialchars($course['nombre_document'] ?? '0') ?> documents</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<?php require_once "../../components/footer/footer.php" ?>