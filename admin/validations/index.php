<?php
session_start();
$pageTitle = "Validation des comptes";
require_once '../../components/header/header.php';
require_once '../../utils/auth.php';
requireRole('admin');
?>

<style>
    @keyframes fadeOut {
        from {
            opacity: 1;
            transform: translateX(0);
        }

        to {
            opacity: 0;
            transform: translateX(100%);
        }
    }

    .user-row {
        transition: all 0.3s ease;
    }

    .user-row:hover {
        background-color: rgba(0, 0, 0, 0.02);
    }

    .empty-state {
        text-align: center;
        padding: 2rem;
    }

    .empty-state i {
        opacity: 0.5;
    }

    .empty-state p {
        font-size: 1.1rem;
        margin-top: 1rem;
    }

    .btn-group {
        opacity: 0.9;
        transition: opacity 0.3s ease;
    }

    .user-row:hover .btn-group {
        opacity: 1;
    }

    .badge {
        padding: 0.5em 0.75em;
        font-weight: 500;
    }
</style>

<div class="container-fluid">
    <div class="row">
        <?php require_once '../../components/sidebar/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4" style="margin-top: 70px;">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Validation des comptes</h1>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Matricule</th>
                                    <th>Nom complet</th>
                                    <th>Email</th>
                                    <th>Type</th>
                                    <th>Date d'inscription</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="pendingUsers">
                                <!-- Les utilisateurs en attente seront chargés ici -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="js/validations.js"></script>
<?php require_once '../../components/footer/footer.php'; ?>