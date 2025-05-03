<?php
require_once "../../utils/auth.php";
requireRole('professeur');
require_once "../../components/header/header.php";

$id = $_SESSION['user']['id'];
$db = Database::getInstance()->getConnection();

// Récupérer les cours du professeur
$sql = "SELECT DISTINCT c.id_cours, c.nom_cours, f.libelle_filiere
        FROM cours c
        JOIN attribution_cours ac ON c.id_cours = ac.id_cours
        JOIN filiere f ON f.id_filiere = ac.id_filiere
        JOIN professeur p ON c.id_professeur = p.id_professeur
        WHERE p.id_utilisateur = ?";

$stmt = $db->prepare($sql);
$stmt->execute([$id]);
$cours = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les étudiants et leurs notes pour chaque cours
$coursData = [];
foreach ($cours as $c) {
    $sql = "SELECT e.id_etudiant, u.nom, u.prenom, n.note, n.Semestre
            FROM etudiant e
            JOIN etudiant_cours ec ON e.id_etudiant = ec.id_etudiant  
            JOIN utilisateur u ON e.id_utilisateur = u.id_utilisateur
            LEFT JOIN note n ON n.id_etudiant = e.id_etudiant 
                AND n.id_cours = ?
            WHERE ec.id_cours = ?
            ORDER BY u.nom, u.prenom";

    $stmt = $db->prepare($sql);
    $stmt->execute([$c['id_cours'], $c['id_cours']]);
    $coursData[$c['id_cours']] = [
        'info' => $c,
        'etudiants' => $stmt->fetchAll(PDO::FETCH_ASSOC)
    ];
}
?>

<link rel="stylesheet" href="/myschoolface/assets/css/notes.css">

<div class="main-wrapper">
    <?php require_once "../../components/sidebar/sidebar.php" ?>

    <main class="main-content">
        <div class="container-fluid px-4">
            <?php foreach ($coursData as $data): ?>
                <div class="semester-card">
                    <div class="semester-header d-flex justify-content-between align-items-center">
                        <h2 class="h5 mb-0">
                            <?= htmlspecialchars($data['info']['nom_cours']) ?> -
                            <?= htmlspecialchars($data['info']['libelle_filiere']) ?>
                        </h2>
                    </div>
                    <div class="p-4">
                        <div class="table-responsive">
                            <table class="table grade-table">
                                <thead>
                                    <tr>
                                        <th>Étudiant</th>
                                        <th>Note</th>
                                        <th>Semestre</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data['etudiants'] as $etudiant): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($etudiant['nom'] . ' ' . $etudiant['prenom']) ?></td>
                                            <td class="text-center">
                                                <?php if ($etudiant['note']): ?>
                                                    <span class="grade-badge <?= $etudiant['note'] >= 10 ? 'passing' : 'failing' ?>">
                                                        <?= $etudiant['note'] ?>/20
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($etudiant['Semestre'] ?? '-') ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-primary edit-note"
                                                    data-student="<?= $etudiant['id_etudiant'] ?>"
                                                    data-course="<?= $data['info']['id_cours'] ?>">
                                                    Modifier
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
</div>

<?php require_once "../../components/footer/footer.php" ?>