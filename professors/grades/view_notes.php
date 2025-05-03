<?php
session_start();
require_once "../../components/header/header.php";

if (!isset($_GET['attribution']) || !isset($_GET['semestre'])) {
    header("Location: ../dashboard");
    exit;
}

$id_attribution = $_GET['attribution'];
$semestre = $_GET['semestre'];
$id = $_SESSION['user']['id'];

try {
    $db = Database::getInstance()->getConnection();

    // Récupérer les informations du cours et les notes
    $sql = "SELECT 
                f.libelle_filiere,
                c.nom_cours,
                u.nom,
                u.prenom,
                e.id_etudiant,
                n.note,
                COALESCE(n.note, 'Non notée') as note_affichee
            FROM professeur p
            JOIN cours c ON p.id_professeur = c.id_professeur
            JOIN attribution_cours ac ON ac.id_cours = c.id_cours
            JOIN filiere f ON f.id_filiere = ac.id_filiere
            JOIN etudiant e ON e.id_filiere = ac.id_filiere
            JOIN etudiant_cours ec ON ec.id_etudiant = e.id_etudiant
            JOIN utilisateur u ON u.id_utilisateur = e.id_utilisateur
            LEFT JOIN note n ON n.id_etudiant = e.id_etudiant 
                AND n.id_cours = c.id_cours 
                AND n.Semestre = ?
            WHERE ac.id_attribution = ?
            AND p.id_utilisateur = ?
            ORDER BY u.nom, u.prenom";

    $stmt = $db->prepare($sql);
    $stmt->execute([$semestre, $id_attribution, $id]);
    $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($notes)) {
        throw new Exception("Aucune donnée trouvée");
    }

?>

    <div class="main-wrapper">
        <?php require_once "../../components/sidebar/sidebar.php" ?>

        <main class="main-content">
            <div class="container">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h2 class="text-center mb-0">Notes de <?= $notes[0]['nom_cours'] ?></h2>
                        <p class="text-center mb-0"><?= $notes[0]['libelle_filiere'] ?> - <?= $semestre ?></p>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Prénom</th>
                                    <th>Note</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($notes as $note): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($note['nom']) ?></td>
                                        <td><?= htmlspecialchars($note['prenom']) ?></td>
                                        <td><?= is_numeric($note['note']) ? $note['note'] . '/20' : 'Non notée' ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer text-center">
                        <a href="index.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Retour
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>

<?php
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header("Location: ../dashboard");
    exit;
}

require_once "../../components/footer/footer.php";
?>