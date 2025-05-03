<?php
$pageTitle = "Liste des Notes";
require_once "../../components/header/header.php";

$id = $_SESSION['user']['id'];
$db = Database::getInstance()->getConnection();

// Vérifier si l'étudiant existe et récupérer son ID
$stmt = $db->prepare("SELECT id_etudiant FROM etudiant WHERE id_utilisateur = ?");
$stmt->execute([$id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    die('<div class="alert alert-danger">Accès non autorisé. Utilisateur non trouvé.</div>');
}

// Récupérer les notes par semestre
$sql = "SELECT n.note, n.Semestre, c.nom_cours 
        FROM note n
        JOIN cours c ON c.id_cours = n.id_cours 
        JOIN etudiant e ON e.id_etudiant = n.id_etudiant
        WHERE e.id_utilisateur = ?
        ORDER BY n.Semestre, c.nom_cours";

$stmt = $db->prepare($sql);
$stmt->execute([$id]);
$notes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Organiser les notes par semestre
$notesBySemester = [];
foreach ($notes as $note) {
    $notesBySemester[$note['Semestre']][] = $note;
}
?>

<link rel="stylesheet" href="/myschoolface/assets/css/notes.css">

<div class="main-wrapper">
    <?php require_once "../../components/sidebar/sidebar.php" ?>

    <main class="main-content">
        <div class="container-fluid px-4">
            <div class="row">
                <div class="col-12">
                    <h1 class="content-title mb-4">Liste des Notes</h1>

                    <?php if (empty($notes)): ?>
                        <div class="alert alert-info">
                            Aucune note n'est disponible pour le moment.
                        </div>
                    <?php else: ?>
                        <?php foreach ($notesBySemester as $semester => $semesterNotes): ?>
                            <div class="semester-card">
                                <div class="semester-header">
                                    <h2 class="semester-title"><?= htmlspecialchars($semester) ?></h2>
                                </div>
                                <div class="grades-container">
                                    <div class="table-responsive">
                                        <table class="table table-grades">
                                            <thead>
                                                <tr>
                                                    <th>Matière</th>
                                                    <th class="text-center">Note/20</th>
                                                    <th class="text-center">Statut</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($semesterNotes as $note): ?>
                                                    <tr class="grade-row">
                                                        <td class="course-name"><?= htmlspecialchars($note['nom_cours']) ?></td>
                                                        <td class="text-center grade-value"><?= $note['note'] ?>/20</td>
                                                        <td class="text-center">
                                                            <span class="status-badge <?= $note['note'] >= 10 ? 'validated' : 'not-validated' ?>">
                                                                <?= $note['note'] >= 10 ? 'Validé' : 'Non validé' ?>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</div>

<?php require_once "../../components/footer/footer.php"; ?>