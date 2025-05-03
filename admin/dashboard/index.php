<?php
require_once '../../utils/auth.php';
require_once '../../config/database.php';
require_once '../../utils/photo.php';

$pageTitle = "Tableau de bord";
require_once '../../components/header/header.php';
requireRole('admin');
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <?php require_once '../../components/sidebar/sidebar.php'; ?>

        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 dashboard-main">
            <div class="dashboard-content">
                <!-- Titre Dashboard -->
                <div class="dashboard-header">
                    <h1 class="h2">Dashboard</h1>
                </div>

                <div class="container">
                    <!-- Cartes de statistiques en haut -->
                    <div class="container overflow-hidden text-center mb-4">
                        <div class="row gx-4">
                            <div class="col-md-3">
                                <div class="p-3 stats-card">
                                    <p class="text-start fw-bold">Total Étudiants</p>
                                    <p class="text-start" id="total-etudiants">0</p>
                                    <p class="text-start">
                                        <span id="evolution-etudiants" class="text-success">
                                            <i class="bi bi-arrow-up"></i> +0% cette année
                                        </span>
                                    </p>
                                    <i class="bi bi-mortarboard dashboard-icon-student"></i>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="p-3 stats-card">
                                    <p class="text-start fw-bold">Total Professeurs</p>
                                    <p class="text-start" id="total-profs">0</p>
                                    <p class="text-start text-primary">
                                        <i class="bi bi-person-workspace"></i>
                                        <span id="profs-actifs">0</span> actifs
                                    </p>
                                    <i class="bi bi-person-video3 dashboard-icon-professor"></i>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="p-3 stats-card">
                                    <p class="text-start fw-bold">Total Salles</p>
                                    <p class="text-start" id="total-salles">0</p>
                                    <p class="text-start text-success">
                                        <i class="bi bi-door-open"></i>
                                        <span id="salles-dispo">0</span> disponibles
                                    </p>
                                    <i class="bi bi-building dashboard-icon-room"></i>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="p-3 stats-card">
                                    <p class="text-start fw-bold">Total Filières</p>
                                    <p class="text-start" id="total-filieres">0</p>
                                    <p class="text-start text-info">
                                        <i class="bi bi-diagram-3"></i>
                                        <span id="classes-actives">0</span> classes
                                    </p>
                                    <i class="bi bi-journals dashboard-icon-filiere"></i>
                                </div>
                            </div>
                        </div>

                        <div class="row gx-4 mt-4">
                            <div class="col-md-4">
                                <div class="p-3 stats-card">
                                    <p class="text-start fw-bold">Revenus Annuels</p>
                                    <p class="text-start prix" id="revenu-total">FCFA 0</p>
                                    <p class="text-start text-success">
                                        <i class="bi bi-arrow-up"></i>
                                        <span id="evolution-revenus">0</span>% ce mois
                                    </p>
                                    <i class="bi bi-graph-up-arrow dashboard-icon-revenue"></i>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 stats-card">
                                    <p class="text-start fw-bold">Paiements en Retard</p>
                                    <p class="text-start prix" id="paiements-retard">0</p>
                                    <p class="text-start text-danger">
                                        <i class="bi bi-exclamation-triangle"></i>
                                        <span id="montant-retard">FCFA 0</span>
                                    </p>
                                    <i class="bi bi-clock dashboard-icon-timer"></i>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 stats-card">
                                    <p class="text-start fw-bold">Taux de Réussite</p>
                                    <p class="text-start" id="taux-reussite">0%</p>
                                    <p class="text-start text-success">
                                        <i class="bi bi-graph-up"></i>
                                        <span id="evolution-reussite">0</span>% vs année précédente
                                    </p>
                                    <i class="bi bi-award dashboard-icon-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Navigation des onglets -->
                    <ul class="nav nav-tabs mb-4" id="dashboardTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="users-tab" data-bs-toggle="tab" data-bs-target="#users" type="button" role="tab">
                                <i class="bi bi-people me-2"></i>Utilisateurs
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="finance-tab" data-bs-toggle="tab" data-bs-target="#finance" type="button" role="tab">
                                <i class="bi bi-cash-coin me-2"></i>Finances
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="academic-tab" data-bs-toggle="tab" data-bs-target="#academic" type="button" role="tab">
                                <i class="bi bi-mortarboard me-2"></i>Académique
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="planning-tab" data-bs-toggle="tab" data-bs-target="#planning" type="button" role="tab">
                                <i class="bi bi-calendar3 me-2"></i>Planning
                            </button>
                        </li>
                    </ul>

                    <!-- Contenu des onglets -->
                    <div class="tab-content" id="dashboardTabsContent">
                        <!-- Onglet Utilisateurs -->
                        <div class="tab-pane fade show active" id="users" role="tabpanel">
                            <div class="section">
                                <p class="text-start">Gestion Des utilisateur</p>
                                <p class="text-end">
                                    <a href="/myschoolface/admin/users/" class="btn btn-primary">
                                        <i class="bi bi-arrow-right"></i> Gerer
                                    </a>
                                </p>
                            </div>
                            <div class="section1">
                                <div class="table-responsive">
                                    <table class="table text-center">
                                        <thead>
                                            <tr class="table-light">
                                                <th scope="col">Matricule</th>
                                                <th scope="col">Nom</th>
                                                <th scope="col">Email</th>
                                                <th scope="col">Role</th>
                                                <th scope="col">Statut</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $db = Database::getInstance()->getConnection();
                                            $stmt = $db->query("
                                                SELECT u.id_utilisateur, u.matricule, u.prenom, u.nom, u.email, 
                                                    u.photo_profile, u.statut_compte, r.nom_role
                                                FROM utilisateur u
                                                INNER JOIN role r ON u.id_role = r.id_role 
                                                ORDER BY u.date_creation DESC 
                                                LIMIT 5
                                            ");
                                            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                            foreach ($users as $user): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($user['matricule']) ?></td>
                                                    <td>
                                                        <div class="d-flex align-items-center gap-2">
                                                            <img src="/myschoolface/uploads/avatar/<?= htmlspecialchars(getAvatarPath($user['photo_profile'])) ?>"
                                                                alt="<?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?>"
                                                                class="user-avatar">
                                                            <span><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></span>
                                                        </div>
                                                    </td>
                                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                                    <td>
                                                        <span class="role-badge <?= match ($user['nom_role']) {
                                                                                    'etudiant' => 'role-etudiant',
                                                                                    'professeur' => 'role-professeur',
                                                                                    'admin' => 'role-admin',
                                                                                    'moderateur' => 'role-moderateur',
                                                                                    default => ''
                                                                                } ?>">
                                                            <?= ucfirst($user['nom_role']) ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="status-badge <?= $user['statut_compte'] === 'actif'
                                                                                        ? 'status-actif'
                                                                                        : 'status-inactif' ?>">
                                                            <?= ucfirst($user['statut_compte']) ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Onglet Finances -->
                        <div class="tab-pane fade" id="finance" role="tabpanel">
                            <div class="section">
                                <p class="text-start fw-bold">Suivi financier</p>
                                <p class="text-end">
                                    <a href="/myschoolface/admin/financial/" class="btn btn-success">
                                        <i class="bi bi-arrow-right"></i> Gerer
                                    </a>
                                </p>
                            </div>
                            <div class="section1">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr class="table-light">
                                                <th scope="col">Id</th>
                                                <th scope="col">Etudiants</th>
                                                <th scope="col">Montant</th>
                                                <th scope="col">Date</th>
                                                <th scope="col">Statut</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $stmt = $db->query("
                                                SELECT t.*, u.matricule, u.prenom, u.nom 
                                                FROM transactions t
                                                INNER JOIN utilisateur u ON t.id_etudiant = u.id_utilisateur
                                                ORDER BY t.date_paiement DESC
                                                LIMIT 5
                                            ");
                                            $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                            foreach ($transactions as $transaction): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($transaction['matricule']) ?></td>
                                                    <td><?= htmlspecialchars($transaction['prenom'] . ' ' . $transaction['nom']) ?></td>
                                                    <td>FCFA <?= number_format($transaction['montant'], 0, ',', ' ') ?></td>
                                                    <td><?= date('d/m/Y', strtotime($transaction['date_paiement'])) ?></td>
                                                    <td>
                                                        <span class="payment-badge <?= $transaction['statut_paiement'] === 'paye'
                                                                                        ? 'payment-success'
                                                                                        : 'payment-late' ?>">
                                                            <?= ucfirst($transaction['statut_paiement']) ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Onglet Académique -->
                        <div class="tab-pane fade" id="academic" role="tabpanel">
                            <div class="row gx-4 gy-4">
                                <div class="col-md-6">
                                    <div class="section1">
                                        <h5><i class="bi bi-mortarboard"></i> Distribution par Filière</h5>
                                        <div id="filiereChart"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="section1">
                                        <h5><i class="bi bi-book"></i> Vue d'ensemble des cours</h5>
                                        <div class="stats-grid">
                                            <div class="stat-item">
                                                <span class="stat-label">Total des cours</span>
                                                <span class="stat-value" id="total-cours">24</span>
                                            </div>
                                            <div class="stat-item">
                                                <span class="stat-label">Cours actifs</span>
                                                <span class="stat-value" id="cours-actifs">18</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Onglet Planning -->
                        <div class="tab-pane fade" id="planning" role="tabpanel">
                            <div class="row gx-4 gy-4">
                                <div class="col-md-6">
                                    <div class="section1">
                                        <h5><i class="bi bi-building"></i> État des salles</h5>
                                        <div class="room-status">
                                            <div class="status-item">
                                                <span class="status-label">Salles disponibles</span>
                                                <span class="status-value text-success" id="salles-disponibles">8</span>
                                            </div>
                                            <div class="status-item">
                                                <span class="status-label">Salles occupées</span>
                                                <span class="status-value text-warning" id="salles-occupees">12</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<style>
    .role-badge {
        padding: 0.25rem 0.5rem;
        border-radius: 1rem;
        display: inline-block;
    }

    .role-etudiant {
        background-color: rgba(25, 135, 84, 0.1);
        color: #198754;
    }

    .role-professeur {
        background-color: rgba(13, 110, 253, 0.1);
        color: #0d6efd;
    }

    .role-admin {
        background-color: rgba(220, 53, 69, 0.1);
        color: #dc3545;
    }

    .role-moderateur {
        background-color: rgba(255, 193, 7, 0.1);
        color: #ffc107;
    }

    .status-badge {
        padding: 0.25rem 0.5rem;
        border-radius: 1rem;
        display: inline-block;
    }

    .status-actif {
        background-color: rgba(25, 135, 84, 0.1);
        color: #198754;
    }

    .status-inactif {
        background-color: rgba(220, 53, 69, 0.1);
        color: #dc3545;
    }

    .payment-badge {
        padding: 0.25rem 0.5rem;
        border-radius: 1rem;
        display: inline-block;
    }

    .payment-success {
        background-color: rgba(25, 135, 84, 0.1);
        color: #198754;
    }

    .payment-late {
        background-color: rgba(220, 53, 69, 0.1);
        color: #dc3545;
    }
</style>

<script>
    async function loadDashboardStats() {
        try {
            const response = await fetch('handlers/get_stats.php');
            const data = await response.json();

            if (data.success) {
                // Stats des effectifs
                document.getElementById('total-etudiants').textContent =
                    parseInt(data.data.effectifs.total_etudiants).toLocaleString('fr-FR');
                document.getElementById('total-profs').textContent =
                    data.data.effectifs.total_professeurs;
                document.getElementById('profs-actifs').textContent =
                    data.data.effectifs.professeurs_actifs;
                document.getElementById('total-salles').textContent =
                    data.data.effectifs.total_salles;
                document.getElementById('salles-dispo').textContent =
                    data.data.effectifs.salles_disponibles;
                document.getElementById('total-filieres').textContent =
                    data.data.effectifs.total_filieres;
                document.getElementById('classes-actives').textContent =
                    data.data.effectifs.total_classes;

                // Stats financières
                document.getElementById('revenu-total').textContent =
                    `FCFA ${parseInt(data.data.finances.revenu_annuel).toLocaleString('fr-FR')}`;
                document.getElementById('paiements-retard').textContent =
                    data.data.finances.paiements_retard;
                document.getElementById('evolution-revenus').textContent =
                    data.data.finances.evolution_revenu;
                document.getElementById('montant-retard').textContent =
                    `FCFA ${parseInt(data.data.finances.montant_retard).toLocaleString('fr-FR')}`;

                // Stats académiques
                const evolutionPercentage = data.data.evolution_etudiants;
                document.getElementById('evolution-etudiants').innerHTML =
                    `<i class="bi bi-arrow-${evolutionPercentage >= 0 ? 'up' : 'down'}"></i>
                     ${evolutionPercentage > 0 ? '+' : ''}${evolutionPercentage}% cette année`;

                document.getElementById('taux-reussite').textContent =
                    `${data.data.academic?.taux_reussite || 0}%`;
                document.getElementById('evolution-reussite').textContent =
                    data.data.academic?.evolution_reussite || 0;
            }
        } catch (error) {
            console.error('Erreur:', error);
            // En cas d'erreur, afficher 0%
            document.getElementById('taux-reussite').textContent = '0%';
            document.getElementById('evolution-reussite').textContent = '0';
        }
    }

    // Actualiser les données toutes les 5 minutes
    document.addEventListener('DOMContentLoaded', () => {
        loadDashboardStats();
        setInterval(loadDashboardStats, 300000);
    });
</script>

<?php require_once '../../components/footer/footer.php'; ?>