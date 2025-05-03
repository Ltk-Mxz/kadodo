<?php
require_once '../../utils/auth.php';
requireRole('admin');
$pageTitle = "Gestion des Salles";
require_once '../../components/header/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once '../../components/sidebar/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content" style="margin-top: 70px">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Gestion des Salles</h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#roomModal">
                    <i class="bi bi-plus"></i> Ajouter une salle
                </button>
            </div>

            <!-- Table des salles -->
            <div class="table-responsive">
                <table class="table" id="roomsTable">
                    <thead>
                        <tr>
                            <th>Nom de la salle</th>
                            <th>Capacité</th>
                            <th>Équipements</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            <!-- Modal d'ajout/modification -->
            <div class="modal fade" id="roomModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Ajouter une salle</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form id="roomForm">
                                <input type="hidden" name="id_salle" id="roomId">
                                <div class="mb-3">
                                    <label class="form-label">Nom de la salle</label>
                                    <input type="text" class="form-control" name="nom_salle" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Capacité</label>
                                    <input type="number" class="form-control" name="capacite" min="1" value="30" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Équipements</label>
                                    <textarea class="form-control" name="equipements" rows="3"></textarea>
                                </div>
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="disponible" id="disponible" checked>
                                        <label class="form-check-label" for="disponible">Disponible</label>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" form="roomForm" class="btn btn-primary">Enregistrer</button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="js/rooms.js"></script>
<?php require_once '../../components/footer/footer.php'; ?>