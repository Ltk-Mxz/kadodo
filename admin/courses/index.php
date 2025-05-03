<?php
require_once '../../utils/auth.php';
requireRole('admin');
$pageTitle = "Gestion des Cours";
require_once '../../components/header/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once '../../components/sidebar/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content" style="margin-top: 70px;">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Gestion des Cours</h1>
                <div class="d-flex gap-2">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#courseModal">
                        <i class="bi bi-plus"></i> Ajouter un cours
                    </button>
                    <!-- <button class="btn btn-success" id="autoAssignBtn">
                        <i class="bi bi-magic"></i> Attribution automatique
                    </button> -->
                </div>
            </div>

            <div class="table-responsive">
                <table class="table" id="coursesTable">
                    <thead>
                        <tr>
                            <th>Nom du cours</th>
                            <th>Professeur</th>
                            <th>Salle</th>
                            <th>Heures/semaine</th>
                            <th>Filieres</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            <!-- Modal d'ajout/modification -->
            <div class="modal fade" id="courseModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Ajouter un cours</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form id="courseForm">
                                <input type="hidden" name="id_cours" id="courseId">
                                <div class="mb-3">
                                    <label class="form-label">Nom du cours</label>
                                    <input type="text" class="form-control" name="nom_cours" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Professeur</label>
                                    <select class="form-select" name="id_professeur" required>
                                        <option value="">Sélectionner un professeur</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Salle</label>
                                    <select class="form-select" name="salle" required>
                                        <option value="">Sélectionner une salle</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Date de début</label>
                                    <input type="date" class="form-control" name="date_debut" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Date de fin</label>
                                    <input type="date" class="form-control" name="date_fin" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Nombre d'heures par semaine</label>
                                    <input type="number" class="form-control" name="nombre_heures" min="1" value="2" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Filières</label>
                                    <select class="form-select" name="filieres[]" multiple required>
                                        <!-- Les options seront ajoutées par JavaScript -->
                                    </select>
                                    <div class="form-text">Maintenez Ctrl (ou Cmd sur Mac) pour sélectionner plusieurs filières</div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" form="courseForm" class="btn btn-primary">Enregistrer</button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<div id="notifications-container" class="position-fixed bottom-0 end-0 p-3"></div>

<script src="js/courses.js"></script>
<?php require_once '../../components/footer/footer.php'; ?>