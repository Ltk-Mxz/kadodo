<?php
// Démarre la session avant tout output
session_start();



require_once '../../config/database.php';
$db = Database::getInstance()->getConnection();

// Traitement du formulaire d'ajout/modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        try {
            $id_professeur = $_POST['id_professeur'];
            $id_cours = $_POST['id_cours'];
            $jour = $_POST['jour'];
            $heure_debut = $_POST['heure_debut'];
            $heure_fin = $_POST['heure_fin'];
            $salle = $_POST['salle'];
            $statut = 'actif';

            if ($_POST['action'] === 'ajouter') {
                $sql = "INSERT INTO emploi_du_temps (id_professeur, id_cours, jour, heure_debut, heure_fin, salle, statut) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
                $params = [$id_professeur, $id_cours, $jour, $heure_debut, $heure_fin, $salle, $statut];
            } else {
                $sql = "UPDATE emploi_du_temps SET 
                        id_professeur = ?, id_cours = ?, jour = ?, heure_debut = ?, heure_fin = ?, salle = ?, statut = ? 
                        WHERE id_emploi_de_temps = ?";
                $params = [$id_professeur, $id_cours, $jour, $heure_debut, $heure_fin, $salle, $statut, $_POST['id']];
            }

            $stmt = $db->prepare($sql);
            if ($stmt->execute($params)) {
                $_SESSION['success'] = "Opération réussie";
            } else {
                $_SESSION['error'] = "Erreur lors de l'opération";
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = "Erreur: " . $e->getMessage();
        }

        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Récupération des professeurs
$sql_profs = "SELECT p.id_professeur, u.nom, u.prenom 
              FROM professeur p 
              JOIN utilisateur u ON p.id_utilisateur = u.id_utilisateur";
$professeurs = $db->query($sql_profs)->fetchAll(PDO::FETCH_ASSOC);

// Récupération des cours
$sql_cours = "SELECT id_cours, nom_cours FROM cours";
$cours = $db->query($sql_cours)->fetchAll(PDO::FETCH_ASSOC);

// Récupération des données pour le tableau
$sql = "SELECT 
            e.*, 
            u.nom_utilisateur,
            u.prenom,
            u.nom,
            c.nom_cours
        FROM 
            emploi_du_temps e
            JOIN professeur p ON e.id_professeur = p.id_professeur
            JOIN utilisateur u ON p.id_utilisateur = u.id_utilisateur
            JOIN cours c ON e.id_cours = c.id_cours
        ORDER BY e.jour, e.heure_debut";

$emplois = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// Maintenant on peut inclure le header et afficher le contenu
require_once "../../components/header/header.php";
?>
<link rel="stylesheet" href="styles.css">
<?php
?>

<div class="main-wrapper">
    <?php require_once "../../components/sidebar/sidebar.php" ?>

    <main class="main-content">
        <div class="content-wrapper">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= $_SESSION['success'];
                                                    unset($_SESSION['success']); ?></div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['error'];
                                                unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h2">Gestion des Emplois du Temps</h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                    <i class="bi bi-plus-circle me-2"></i>Ajouter un cours
                </button>
            </div>

            <!-- Modal d'ajout -->
            <div class="modal fade" id="addModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="POST">
                            <input type="hidden" name="id" id="scheduleId">
                            <div class="modal-header">
                                <h5 class="modal-title">Ajouter un cours</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Professeur</label>
                                    <select name="id_professeur" class="form-select" required>
                                        <option value="">Sélectionner un professeur</option>
                                        <?php foreach ($professeurs as $prof): ?>
                                            <option value="<?= $prof['id_professeur'] ?>">
                                                <?= htmlspecialchars($prof['prenom'] . ' ' . $prof['nom']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Cours</label>
                                    <select name="id_cours" class="form-select" required>
                                        <option value="">Sélectionner un cours</option>
                                        <?php foreach ($cours as $c): ?>
                                            <option value="<?= $c['id_cours'] ?>">
                                                <?= htmlspecialchars($c['nom_cours']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Jour</label>
                                    <select name="jour" class="form-select" required>
                                        <option value="Lundi">Lundi</option>
                                        <option value="Mardi">Mardi</option>
                                        <option value="Mercredi">Mercredi</option>
                                        <option value="Jeudi">Jeudi</option>
                                        <option value="Vendredi">Vendredi</option>
                                        <option value="Samedi">Samedi</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Heure de début</label>
                                    <input type="time" name="heure_debut" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Heure de fin</label>
                                    <input type="time" name="heure_fin" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Salle</label>
                                    <input type="text" name="salle" class="form-control" placeholder="Salle" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                <button type="submit" name="action" value="ajouter" class="btn btn-primary">Ajouter</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Tableau amélioré -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Professeur</th>
                                    <th>Cours</th>
                                    <th>Jour</th>
                                    <th>Horaire</th>
                                    <th>Salle</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($emplois as $emploi): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($emploi['prenom'] . ' ' . $emploi['nom']) ?></td>
                                        <td><?= htmlspecialchars($emploi['nom_cours']) ?></td>
                                        <td><?= htmlspecialchars($emploi['jour']) ?></td>
                                        <td><?= $emploi['heure_debut'] ?> - <?= $emploi['heure_fin'] ?></td>
                                        <td><?= htmlspecialchars($emploi['salle']) ?></td>
                                        <td>
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-primary me-1" onclick="editSchedule(<?= $emploi['id_emploi_de_temps'] ?>)">
                                                    <i class="bi bi-pencil-fill"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger" onclick="deleteSchedule(<?= $emploi['id_emploi_de_temps'] ?>)">
                                                    <i class="bi bi-trash-fill"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php require_once "../../components/footer/footer.php" ?>
<script src="js/schedule.js"></script>