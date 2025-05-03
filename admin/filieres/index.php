<?php
require_once '../../utils/auth.php';
requireRole('admin');

$pageTitle = "Gestion des Filières";
require_once '../../components/header/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once '../../components/sidebar/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content" style="margin-top: 80px;">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Gestion des Filières</h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#filiereModal">
                    <i class="bi bi-plus"></i> Ajouter une filière
                </button>
            </div>

            <!-- Table des filières -->
            <div class="table-responsive">
                <table class="table" id="filieresTable">
                    <thead>
                        <tr>
                            <th>Nom de la filière</th>
                            <th>Nombre d'étudiants</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            <!-- Modal d'ajout/modification -->
            <div class="modal fade" id="filiereModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Ajouter une filière</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form id="filiereForm">
                                <input type="hidden" name="id_filiere" id="filiereId">
                                <div class="mb-3">
                                    <label class="form-label">Nom de la filière</label>
                                    <input type="text" class="form-control" name="libelle_filiere" required>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" form="filiereForm" class="btn btn-primary">Enregistrer</button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="js/filieres.js"></script>
<?php require_once '../../components/footer/footer.php'; ?>