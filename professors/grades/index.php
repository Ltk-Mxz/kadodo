<?php

ob_start(); // Démarrer la mise en tampon de sortie
session_start();
require_once '../../config/database.php';

// Vérifier si les données de session existent
if (
    !isset($_SESSION['SaisieNotes']) ||
    !isset($_SESSION['SaisieNotes']['Semestre']) ||
    !isset($_SESSION['SaisieNotes']['filiereCours'])
) {

    $_SESSION['error'] = "Veuillez sélectionner une filière et un semestre";
    header("Location: ../dashboard");
    ob_end_flush();
    exit;
}

require_once "../../components/header/header.php";

$id = $_SESSION['user']['id'];
$db = Database::getInstance()->getConnection();
$Semestre = $_SESSION['SaisieNotes']['Semestre'];
$id_attribution = $_SESSION['SaisieNotes']['filiereCours'];

// Vérifier si les notes existent déjà pour ce semestre
$checkNotes = $db->prepare("
    SELECT COUNT(*) 
    FROM note n
    JOIN attribution_cours ac ON n.id_cours = ac.id_cours
    WHERE ac.id_attribution = ? 
    AND n.Semestre = ?
");
$checkNotes->execute([$id_attribution, $Semestre]);
$notesExistent = $checkNotes->fetchColumn() > 0;

// Utiliser une requête préparée
try {
    $sql = "SELECT DISTINCT 
            f.libelle_filiere, 
            c.nom_cours,
            u.nom,
            u.prenom, 
            e.id_etudiant,
            e.matricule_etudiant
        FROM professeur p
        JOIN cours c ON p.id_professeur = c.id_professeur 
        JOIN attribution_cours ac ON ac.id_cours = c.id_cours 
        JOIN filiere f ON f.id_filiere = ac.id_filiere
        JOIN etudiant e ON e.id_filiere = ac.id_filiere
        JOIN etudiant_cours ec ON ec.id_etudiant = e.id_etudiant 
            AND ec.id_cours = c.id_cours
        JOIN utilisateur u ON u.id_utilisateur = e.id_utilisateur
        WHERE ac.id_attribution = ?
        AND p.id_utilisateur = ?
        AND e.id_niveau = (
            SELECT cl.id_niveau 
            FROM classe cl 
            WHERE cl.id_classe = e.id_classe
        )
        ORDER BY u.nom, u.prenom";

    $stmt = $db->prepare($sql);
    $stmt->execute([$id_attribution, $id]);
    $cours = $stmt->fetchAll(PDO::FETCH_ASSOC);

    error_log("Nombre d'étudiants trouvés: " . count($cours));
    error_log("SQL: " . $sql);
    error_log("Params: id_attribution=" . $id_attribution . ", id=" . $id);

    if (empty($cours)) {
        throw new Exception("Aucun étudiant trouvé pour ce cours");
    }
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header("Location: ../dashboard");
    ob_end_flush();
    exit;
}
?>

<div class="main-wrapper">
    <?php require_once "../../components/sidebar/sidebar.php" ?>

    <main class="main-content">
        <p style="text-align:center; font-size:20px; color:green; font-weight:400;" id="notification"></p>
        <?php if ($cours): ?>
            <p style="text-align:center; font-size:24px">NOTES DE <?= $cours[0]['nom_cours'] ?> <?= $Semestre ?> FILIERE <?= $cours[0]['libelle_filiere'] ?></p>
            <?php if ($notesExistent): ?>
                <div class="alert alert-warning text-center mb-3">
                    Les notes pour ce semestre ont déjà été saisies. La modification n'est pas autorisée.
                </div>
                <div class="text-center mb-4">
                    <a href="view_notes.php?attribution=<?= $id_attribution ?>&semestre=<?= urlencode($Semestre) ?>"
                        class="btn btn-primary">
                        <i class="bi bi-eye"></i> Voir les notes
                    </a>
                </div>
            <?php endif; ?>
            <form action="note.php" method="POST" id="loginForm" <?= $notesExistent ? 'class="disabled"' : '' ?>>
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">Filière</th>
                            <th scope="col">Nom</th>
                            <th scope="col">Prénom</th>
                            <th scope="col">Note / 20</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cours as $cour) { ?>
                            <tr>
                                <td><?= $cour['libelle_filiere'] ?></td>
                                <td><?= $cour['nom'] ?></td>
                                <td><?= $cour['prenom'] ?></td>
                                <td>
                                    <div class="col-6">
                                        <input type="number" id="inputPassword6" class="form-control search-input" min="0" max="20" name="note[<?= $cour['id_etudiant'] ?>]" required <?= $notesExistent ? 'disabled' : '' ?>>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>

                <button class="btn btn-primary" type="submit" <?= $notesExistent ? 'disabled' : '' ?>>Envoyer Les Notes</button>
            </form>
        <?php else: ?>
            <p style="text-align:center; font-size:24px; color:red;">Aucune donnée trouvée pour ce cours</p>
        <?php endif; ?>
    </main>
</div>

<?php require_once "../../components/footer/footer.php" ?>

<script>
    document.getElementById('loginForm').addEventListener('submit', async (e) => {
        e.preventDefault();

        try {
            const formData = new FormData(e.target);
            const response = await fetch('note.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            const notification = document.getElementById('notification');

            if (data.success) {
                notification.innerHTML = data.message;
                notification.style.color = "green";

                if (data.redirect) {
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 2400);
                }
            } else {
                notification.innerHTML = data.message;
                notification.style.color = "red";

                if (data.warning) {
                    data.warning.forEach(warning => {
                        notification.innerHTML += "<br>" + warning;
                    });
                }
            }

        } catch (error) {
            console.error('Erreur:', error);
            document.getElementById('notification').innerHTML = "Une erreur s'est produite.";
            document.getElementById('notification').style.color = "red";
        }
    });
</script>

<?php
// Fin du fichier
ob_end_flush();
?>