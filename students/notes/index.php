<?php
$pageTitle = "Mes Notes";
require_once "../../components/header/header.php";


$id = $_SESSION['user']['id'];
$db = Database::getInstance()->getConnection();

// Check if student exists and get their ID
$checkStudent = "SELECT e.id_etudiant 
                FROM etudiant e 
                WHERE e.id_utilisateur = ?";
$stmt = $db->prepare($checkStudent);
$stmt->execute([$id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    die('<div class="alert alert-danger">Accès non autorisé. Utilisateur non trouvé.</div>');
}

// Then proceed with notes query haha
$sql = "SELECT note.note, 
               cours.nom_cours, 
               utilisateur.id_utilisateur,
               COALESCE(note.Semestre, 'Semestre 1') as Semestre
        FROM note
        JOIN cours ON cours.id_cours = note.id_cours
        JOIN etudiant ON etudiant.id_etudiant = note.id_etudiant
        JOIN utilisateur ON utilisateur.id_utilisateur = etudiant.id_utilisateur
        WHERE etudiant.id_etudiant = ?
        ORDER BY note.Semestre";

$stmt = $db->prepare($sql);
$stmt->execute([$student['id_etudiant']]);
$notes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Second query using prepared statement
$sqlFiliere = "SELECT * FROM filiere 
               JOIN etudiant ON etudiant.id_filiere = filiere.id_filiere 
               WHERE id_utilisateur = ?";

$stmt = $db->prepare($sqlFiliere);
$stmt->execute([$id]);
$filiereT = $stmt->fetch(PDO::FETCH_ASSOC);

// Only proceed with next query if we have filiere info
if ($filiereT) {
    $filiere = $filiereT['id_filiere'];

    $sql2 = "SELECT * FROM cours 
             JOIN attribution_cours ON cours.id_cours = attribution_cours.id_cours 
             JOIN filiere ON filiere.id_filiere = attribution_cours.id_filiere
             WHERE filiere.id_filiere = ?";

    $stmt = $db->prepare($sql2);
    $stmt->execute([$filiere]);
    $matieres = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $matieres = [];
}
?>
<link rel="stylesheet" href="/myschoolface/assets/css/notes.css">

<div class="main-wrapper">
    <?php require_once "../../components/sidebar/sidebar.php" ?>

    <main class="main-content">
        <div class="container-fluid px-3 px-md-4">
            <div class="row">
                <div class="col-12">
                    <div class="header-section my-4">
                        <h1 class="content-title h3 mb-3">Mes Notes</h1>
                        <div class="filters-section">
                            <div class="input-group">
                                <span class="input-group-text border-end-0 bg-white">
                                    <i class="bi bi-search text-muted"></i>
                                </span>
                                <input type="text" class="form-control border-start-0 ps-0" placeholder="Rechercher une matière..." id="searchInput">
                            </div>
                        </div>
                    </div>

                    <?php if (!$filiereT): ?>
                        <div class="alert alert-warning">
                            Aucune filière trouvée pour cet étudiant.
                        </div>
                    <?php else: ?>
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="bg-light">
                                            <tr>
                                                <th class="py-3">Matière</th>
                                                <th class="py-3 text-center" style="width: 120px;">Note</th>
                                                <th class="py-3" style="width: 150px;">Semestre</th>
                                                <th class="py-3 text-center" style="width: 150px;">Statut</th>
                                            </tr>
                                        </thead>
                                        <tbody id="notesTableBody">
                                            <?php foreach ($notes as $note): ?>
                                                <tr>
                                                    <td class="py-3 align-middle"><?= htmlspecialchars($note['nom_cours']) ?></td>
                                                    <td class="py-3 align-middle text-center fw-bold"><?= $note['note'] ?>/20</td>
                                                    <td class="py-3 align-middle"><?= $note['Semestre'] ?></td>
                                                    <td class="py-3 align-middle text-center">
                                                        <?php if ($note['note'] >= 10): ?>
                                                            <span class="badge bg-success-subtle text-success px-3 py-2">Validé</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-danger-subtle text-danger px-3 py-2">Non validé</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</div>
<?php require_once "../../components/footer/footer.php" ?>