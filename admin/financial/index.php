<?php

$pageTitle = "Finance";
require_once '../../components/header/header.php';

?>

<link href="css/styles.css" rel="stylesheet">

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <?php require_once '../../components/sidebar/sidebar.php'; ?>

        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 financial-main">
            <div class="financial-content">
                <!-- Titre Financial -->
                <div class="financial-header">
                    <h1 class="h2">Finance</h1>
                </div>

                <!-- Notifications container -->
                <div id="notifications-container" class="position-fixed top-0 end-0 p-3" style="z-index: 1050;"></div>

                <div class="container">
                    <div class="container overflow-hidden text-center">
                        <div class="row gx-5">
                            <div class="col">
                                <div class="p-3">
                                    <p class="text-start">Montant collecté</p>
                                    <p class="text-start" id="montantCollecte">FCFA 0</p>
                                    <p class="text-start text-success"><i class="bi bi-arrow-up"></i> <span id="pourcentageAugmentation">0%</span> cette année</p>
                                    <i class="bi bi-cash-stack financial-icon-money"></i>
                                </div>
                            </div>
                            <div class="col">
                                <div class="p-3">
                                    <p class="text-start">Montant en attente</p>
                                    <p class="text-start" id="montantAttente">FCFA 0</p>
                                    <p class="text-start text-warning"><span id="nbPaiementsAttente">0 paiements en attente</span></p>
                                    <i class="bi bi-hourglass-split financial-icon-pending"></i>
                                </div>
                            </div>
                            <div class="col">
                                <div class="p-3">
                                    <p class="text-start">Etudiant en Retard</p>
                                    <p class="text-start" id="nbEtudiantsRetard">0</p>
                                    <p class="text-start text-danger">Action Requise</p>
                                    <i class="bi bi-exclamation-circle financial-icon-alert"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <section class="only">
                        <h3>Ajout de Paiement</h3>
                        <form id="addPaymentForm" class="needs-validation" novalidate>
                            <div class="container overflow-hidden text-center">
                                <div class="row gx-5">
                                    <div class="col">
                                        <div class="p-3">
                                            <p class="text-start">Étudiant</p>
                                            <div class="position-relative">
                                                <input type="text"
                                                    class="form-control mb-2"
                                                    id="studentSearch"
                                                    placeholder="Rechercher un étudiant...">
                                                <select class="form-select"
                                                    name="student_id"
                                                    id="studentSelect"
                                                    size="5"
                                                    required>
                                                    <option value="">Sélectionnez un étudiant</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <p class="text-start">Montant</p>
                                        <div class="p-3">
                                            <div class="input-group mb-3">
                                                <span class="input-group-text">FCFA</span>
                                                <input type="number" class="form-control" name="amount"
                                                    placeholder="0" min="1000" step="500" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="p-3">
                                            <p class="text-start">Mode de paiement</p>
                                            <select class="form-select" name="payment_type" required>
                                                <option value="espece">Espèce</option>
                                                <option value="banque">Banque</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-2"></i>Enregistrer le paiement
                                </button>
                            </div>
                        </form>
                    </section>
                    <section>
                        <h3>Transactions récentes</h3>
                        <div class="table-responsive">
                            <table class="table text-center" id="transactionsTable">
                                <thead>
                                    <tr class="table-light">
                                        <th scope="col">ID</th>
                                        <th scope="col">Etudiant</th>
                                        <th scope="col">Montant</th>
                                        <th scope="col">Type</th>
                                        <th scope="col">Date</th>
                                        <th scope="col">Statut</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </section>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once '../../components/footer/footer.php'; ?>
<script src="js/financial.js"></script>