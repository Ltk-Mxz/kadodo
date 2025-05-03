<?php require_once "../../components/header/header.php";

require_once '../../config/database.php';

$id_user = $_SESSION['user']['id'];

$db = Database::getInstance()->getConnection();

$sql = "SELECT 
            e.jour,
            e.heure_debut,
            e.heure_fin,
            c.nom_cours,
            e.salle
        FROM 
            professeur p
            JOIN utilisateur u ON u.id_utilisateur = p.id_utilisateur
            JOIN emploi_du_temps e ON p.id_professeur = e.id_professeur
            JOIN cours c ON e.id_cours = c.id_cours
        WHERE 
            p.id_utilisateur = :id_user
            AND e.statut = 'actif'
        ORDER BY 
            e.jour, e.heure_debut";

$stmt = $db->prepare($sql);
$stmt->execute(['id_user' => $id_user]);
$emplois = $stmt->fetchAll(PDO::FETCH_ASSOC);

$emplois_par_jour = array(
    'Lundi' => array(),
    'Mardi' => array(),
    'Mercredi' => array(),
    'Jeudi' => array(),
    'Vendredi' => array(),
    'Samedi' => array()
);

foreach ($emplois as $emploi) {
    $emplois_par_jour[$emploi['jour']][] = $emploi;
}

function generateRandomColor()
{
    $colors = array(
        'bg-primary-subtle',
        'bg-secondary-subtle',
        'bg-success-subtle',
        'bg-danger-subtle',
        'bg-warning-subtle',
        'bg-info-subtle',
        'bg-light-subtle',
        'bg-dark-subtle'
    );
    return $colors[array_rand($colors)];
}
?>

<div class="main-wrapper">
    <?php require_once "../../components/sidebar/sidebar.php" ?>

    <main class="main-content">
        <div class="content-wrapper">
            <div class="d-flex align-items-center justify-content-between mb-4 calendar-header">
                <h1 class="content-title mb-0">Calendrier</h1>
                <div class="d-flex align-items-center gap-4">
                    <div class="date-navigation d-flex align-items-center">
                        <button class="btn">
                            <i class="bi bi-chevron-left"></i>
                        </button>
                        <span class="date-period mx-3">15 - 20 JAN 2025</span>
                        <button class="btn">
                            <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>
                    <div class="view-controls d-flex align-items-center gap-2">
                        <button class="btn btn-outline-secondary">Jour</button>
                        <button class="btn btn-primary">Semaine</button>
                        <button class="btn btn-outline-secondary">Mois</button>
                    </div>
                </div>
            </div>

            <div class="filters-bar mb-4">
                <div class="d-flex gap-3 flex-wrap">
                    <div class="search-container">
                        <input class="form-control" type="search" placeholder="Rechercher un cours...">
                        <i class="bi bi-search"></i>
                    </div>
                    <div class="filters-group d-flex gap-3">
                        <select class="form-select">
                            <option>Toutes les classes</option>
                            <option>1C1</option>
                            <option>1C2</option>
                        </select>
                        <select class="form-select">
                            <option>Toutes les matières</option>
                            <option>ATO</option>
                            <option>PASCAL</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="calendar-container">
                <div class="table-responsive">
                    <table class="table calendar-table">
                        <thead>
                            <tr>
                                <th>Lundi</th>
                                <th>Mardi</th>
                                <th>Mercredi</th>
                                <th>Jeudi</th>
                                <th>Vendredi</th>
                                <th>Samedi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <?php
                                $jours = array('Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi');
                                foreach ($jours as $jour) {
                                    echo '<td class="day-' . strtolower($jour) . '">';
                                    if (isset($emplois_par_jour[$jour])) {
                                        foreach ($emplois_par_jour[$jour] as $cours) {
                                            $randomColor = generateRandomColor();
                                            echo '<div class="course-block ' . $randomColor . '">';
                                            echo '<h6 class="course-title"><i class="bi bi-book me-2"></i>' . $cours['nom_cours'] . '</h6>';
                                            echo '<div class="course-info">';
                                            echo '<p><i class="bi bi-door-open me-2"></i>Salle: ' . $cours['salle'] . '</p>';
                                            echo '<p class="course-time"><i class="bi bi-clock me-2"></i>' . $cours['heure_debut'] . ' - ' . $cours['heure_fin'] . '</p>';
                                            echo '</div></div>';
                                        }
                                    }
                                    echo '</td>';
                                }
                                ?>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>
<?php require_once "../../components/footer/footer.php" ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>