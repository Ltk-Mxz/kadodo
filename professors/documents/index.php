<?php
require_once '../../utils/auth.php';
requireRole('professeur');
require_once '../../components/header/header.php';
require_once '../../config/database.php';

$id_user = $_SESSION['user']['id'];
$db = Database::getInstance()->getConnection();

// Récupérer l'ID du professeur
$stmt = $db->prepare("SELECT id_professeur FROM professeur WHERE id_utilisateur = ?");
$stmt->execute([$id_user]);
$prof = $stmt->fetch(PDO::FETCH_ASSOC);

// Récupérer tous les documents
$stmt = $db->prepare("
    SELECT d.*, f.libelle_filiere, c.nom_cours, d.date_upload 
    FROM document d
    JOIN filiere f ON d.id_filiere = f.id_filiere
    JOIN cours c ON d.id_cours = c.id_cours
    WHERE d.id_professeur = ?
    ORDER BY d.date_upload DESC
");
$stmt->execute([$prof['id_professeur']]);
$documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once "../../components/sidebar/sidebar.php"; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4" style="margin-top: 70px;">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Tous les documents</h1>
                <a href="../dashboard" class="btn btn-outline-primary">
                    <i class="bi bi-arrow-left"></i> Retour
                </a>
            </div>

            <?php
            // Vérifier s'il y a des documents manquants
            $hasMissingFiles = false;
            foreach ($documents as $doc) {
                $filePath = __DIR__ . '/../../uploads/docs/' . $doc['chemin_doc'];
                if (!empty($doc['chemin_doc']) && !file_exists($filePath)) {
                    $hasMissingFiles = true;
                    break;
                }
            }

            // Afficher l'alerte seulement s'il y a des documents manquants
            if ($hasMissingFiles): ?>
                <div class="alert alert-warning" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    Les documents manquants physiquement sont marqués d'un triangle d'avertissement.
                </div>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>État</th>
                            <th>Libellé</th>
                            <th>Filière</th>
                            <th>Cours</th>
                            <th>Date d'upload</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $hasDocuments = false;
                        foreach ($documents as $doc):
                            $filePath = __DIR__ . '/../../uploads/docs/' . $doc['chemin_doc'];
                            if (!empty($doc['chemin_doc'])):
                                $hasDocuments = true;
                        ?>
                                <tr data-doc-id="<?= $doc['id_doc'] ?>">
                                    <td>
                                        <?php if (file_exists($filePath)): ?>
                                            <i class="bi bi-file-earmark-text text-success" title="Document disponible"></i>
                                        <?php else: ?>
                                            <i class="bi bi-exclamation-triangle text-warning" title="Fichier physique manquant"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($doc['lib_doc']) ?></td>
                                    <td><?= htmlspecialchars($doc['libelle_filiere']) ?></td>
                                    <td><?= htmlspecialchars($doc['nom_cours']) ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($doc['date_upload'])) ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <?php if (file_exists($filePath)): ?>
                                                <a href="/myschoolface/uploads/docs/<?= htmlspecialchars($doc['chemin_doc']) ?>"
                                                    class="btn btn-sm btn-outline-primary"
                                                    target="_blank"
                                                    title="Télécharger">
                                                    <i class="bi bi-download"></i>
                                                </a>
                                            <?php endif; ?>
                                            <button class="btn btn-sm btn-outline-danger"
                                                onclick="deleteDocument(<?= $doc['id_doc'] ?>, '<?= htmlspecialchars($doc['lib_doc']) ?>')"
                                                title="Supprimer">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php
                            endif;
                        endforeach;

                        if (!$hasDocuments):
                            ?>
                            <tr>
                                <td colspan="6" class="text-center">Aucun document disponible</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>

<div id="notifications-container" class="position-fixed top-0 end-0 p-3" style="z-index: 1050;"></div>

<script>
    function deleteDocument(docId, docName) {
        if (confirm(`Voulez-vous vraiment supprimer le document "${docName}" ?`)) {
            fetch('delete_document.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id: docId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const row = document.querySelector(`tr[data-doc-id="${docId}"]`);
                        if (row) {
                            row.remove();
                        }

                        // Vérifier s'il reste des documents
                        const tbody = document.querySelector('tbody');
                        if (!tbody.hasChildNodes()) {
                            tbody.innerHTML = '<tr><td colspan="6" class="text-center">Aucun document disponible</td></tr>';
                        }
                        showToast('Document supprimé avec succès', 'success');
                    } else {
                        showToast(data.message || 'Erreur lors de la suppression', 'danger');
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    showToast('Erreur lors de la suppression', 'danger');
                });
        }
    }

    function showToast(message, type = 'success') {
        const container = document.getElementById('notifications-container');
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type} border-0`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');

        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        `;

        container.appendChild(toast);
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();

        toast.addEventListener('hidden.bs.toast', () => {
            container.removeChild(toast);
        });
    }
</script>

<?php require_once "../../components/footer/footer.php"; ?>