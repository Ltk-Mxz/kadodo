<?php
require_once '../../utils/auth.php';
requireRole('admin');
require_once '../../components/header/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once '../../components/sidebar/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            <!-- Add this container for notifications -->
            <div id="notifications-container" class="position-fixed bottom-0 end-0 p-3" style="z-index: 1050;"></div>

            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <!-- Toast container -->
                <div class="toast-container position-fixed bottom-0 end-0 p-3">
                    <div id="notificationToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="toast-header">
                            <strong class="me-auto">Notification</strong>
                            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                        <div class="toast-body"></div>
                    </div>
                </div>

                <h1 class="h2">Gestion des Profs</h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#assignClassModal">
                    <i class="bi bi-plus"></i>Ajouter
                </button>
            </div>

            <!-- Table des attributions -->
            <div class="table-responsive">
                <table class="table" id="coursesTable">
                    <thead>
                        <tr>
                            <th>Professeur</th>
                            <th>Matière</th>
                            <th>Salle</th>
                            <th>Filière</th>
                            <th>Date début</th>
                            <th>Date fin</th>
                            <th>Heures/semaine</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            <!-- Modal d'attribution -->
            <div class="modal fade" id="assignClassModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Attribuer un cours</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form id="assignClassForm">
                                <input type="hidden" name="course_id" id="courseId">
                                <div class="mb-3">
                                    <label class="form-label">Professeur</label>
                                    <select class="form-select" name="professor_id" required>
                                        <option value="">Sélectionner un professeur</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Matière</label>
                                    <select class="form-select" name="subject" required>
                                        <option value="">Sélectionner une matière</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Salle</label>
                                    <select class="form-select" name="room" required>
                                        <option value="">Sélectionner une salle</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Date début</label>
                                    <input type="date" class="form-control" name="date_debut" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Date fin</label>
                                    <input type="date" class="form-control" name="date_fin" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Heures par semaine</label>
                                    <input type="number" class="form-control" name="nombre_heures" min="1" value="2" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Filière</label>
                                    <select class="form-select" name="filiere_id" required>
                                        <option value="">Sélectionner une filière</option>
                                        <?php
                                        $filieres = $db->query("SELECT id_filiere, libelle_filiere FROM filiere")->fetchAll();
                                        foreach ($filieres as $filiere): ?>
                                            <option value="<?= $filiere['id_filiere'] ?>"><?= htmlspecialchars($filiere['libelle_filiere']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mt-4 text-end">
                                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="js/professor.js"></script>
<?php require_once '../../components/footer/footer.php'; ?>