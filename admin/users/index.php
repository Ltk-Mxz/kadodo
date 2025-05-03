<?php
session_start();
$pageTitle = "Gestion des Utilisateurs";
require_once '../../components/header/header.php';
require_once '../../utils/auth.php';
require_once '../../config/database.php';
require_once '../../utils/photo.php';
?>

<div id="notifications-container" class="position-fixed top-0 end-0 p-3 toast-container" style="z-index: 1050;"></div>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <?php require_once '../../components/sidebar/sidebar.php'; ?>

        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 settings-container">
            <div class="users-content">
                <!-- Titre Users -->
                <div class="users-header">
                    <h1 class="h2">Gestion des Utilisateurs</h1>
                </div>

                <div class="content-wrapper">
                    <!-- Titre et actions -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="d-flex gap-3">
                            <button class="btn btn-add" data-bs-toggle="modal" data-bs-target="#addUserModal">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-lg me-2" viewBox="0 0 16 16">
                                    <path d="M8 0a1 1 0 0 1 1 1v6h6a1 1 0 1 1 0 2H9v6a1 1 0 1 1-2 0V9H1a1 1 0 0 1 0-2h6V1a1 1 0 0 1 1-1z" />
                                </svg>
                                Ajouter un utilisateur
                            </button>
                        </div>
                    </div>

                    <!-- Filtres -->
                    <div class="filters-container">
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <input type="search" class="form-control search-input" placeholder="Rechercher par nom ou email">
                            </div>
                            <div class="col-6 col-md-3">
                                <select class="form-select filter-select" id="roleFilter">
                                    <option value="">Tous les rôles</option>
                                    <option value="etudiant">Étudiant</option>
                                    <option value="professeur">Professeur</option>
                                    <option value="admin">Administrateur</option>
                                    <option value="moderateur">Modérateur</option>
                                </select>
                            </div>
                            <div class="col-6 col-md-3">
                                <select class="form-select filter-select" id="statusFilter">
                                    <option value="">Tous les statuts</option>
                                    <option value="actif">Actif</option>
                                    <option value="inactif">Inactif</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="table-container">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nom complet</th>
                                        <th>Email</th>
                                        <th>Rôle</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $db = Database::getInstance()->getConnection();

                                    // Configuration de la pagination
                                    $usersPerPage = 10;
                                    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                                    $offset = ($page - 1) * $usersPerPage;

                                    // Nombre total d'utilisateurs pour la pagination
                                    $totalUsers = $db->query("SELECT COUNT(*) FROM utilisateur")->fetchColumn();
                                    $totalPages = ceil($totalUsers / $usersPerPage);

                                    // Requête avec LIMIT et OFFSET
                                    $stmt = $db->prepare("
                                        SELECT u.id_utilisateur, u.matricule, u.prenom, u.nom, u.email, 
                                               u.photo_profile, u.statut_compte, r.nom_role
                                        FROM utilisateur u
                                        INNER JOIN role r ON u.id_role = r.id_role 
                                        ORDER BY u.date_creation DESC
                                        LIMIT ? OFFSET ?
                                    ");
                                    $stmt->bindValue(1, $usersPerPage, PDO::PARAM_INT);
                                    $stmt->bindValue(2, $offset, PDO::PARAM_INT);
                                    $stmt->execute();
                                    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                    foreach ($users as $user):
                                        $roleClass = match ($user['nom_role']) {
                                            'etudiant' => 'bg-success bg-opacity-10 text-success',
                                            'professeur' => 'bg-primary bg-opacity-10 text-primary',
                                            'admin' => 'bg-danger bg-opacity-10 text-danger',
                                            'moderateur' => 'bg-warning bg-opacity-10 text-warning',
                                            default => ''
                                        };
                                    ?>
                                        <tr data-user-id="<?= $user['id_utilisateur'] ?>">
                                            <td class="text-muted"><?= htmlspecialchars($user['matricule']) ?></td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <img src="/myschoolface/uploads/avatar/<?= htmlspecialchars(getAvatarPath($user['photo_profile'])) ?>"
                                                        alt="<?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?>"
                                                        class="user-avatar">
                                                    <span class="user-name"><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></span>
                                                </div>
                                            </td>
                                            <td class="user-email"><?= htmlspecialchars($user['email']) ?></td>
                                            <td><span class="role-badge <?= $roleClass ?>"><?= ucfirst($user['nom_role']) ?></span></td>
                                            <td>
                                                <span class="status-badge <?= $user['statut_compte'] === 'actif' ? 'status-active' : 'status-inactive' ?>">
                                                    <?= ucfirst($user['statut_compte']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <button class="btn btn-sm btn-primary" title="Modifier"
                                                        data-id="<?= $user['id_utilisateur'] ?>"
                                                        onclick="editUser(<?= $user['id_utilisateur'] ?>)">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger" title="Supprimer"
                                                        data-id="<?= $user['id_utilisateur'] ?>"
                                                        onclick="deleteUser(<?= $user['id_utilisateur'] ?>)">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div class="text-muted user-count">
                            <?php
                            $start = $offset + 1;
                            $end = min($offset + $usersPerPage, $totalUsers);
                            echo "Affichage de $start-$end sur $totalUsers utilisateurs";
                            ?>
                        </div>
                        <nav aria-label="Page navigation">
                            <ul class="pagination mb-0">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $page - 1 ?>" aria-label="Previous">
                                            <i class="bi bi-chevron-left"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php
                                // Afficher au maximum 5 pages
                                $startPage = max(1, min($page - 2, $totalPages - 4));
                                $endPage = min($totalPages, max(5, $page + 2));

                                // Afficher la première page si on n'y est pas
                                if ($startPage > 1) {
                                    echo '<li class="page-item"><a class="page-link" href="?page=1">1</a></li>';
                                    if ($startPage > 2) {
                                        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                    }
                                }

                                // Afficher les pages
                                for ($i = $startPage; $i <= $endPage; $i++) {
                                    echo '<li class="page-item ' . ($i === $page ? 'active' : '') . '">
                                            <a class="page-link" href="?page=' . $i . '">' . $i . '</a>
                                          </li>';
                                }

                                // Afficher la dernière page si on n'y est pas
                                if ($endPage < $totalPages) {
                                    if ($endPage < $totalPages - 1) {
                                        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                    }
                                    echo '<li class="page-item"><a class="page-link" href="?page=' . $totalPages . '">' . $totalPages . '</a></li>';
                                }

                                if ($page < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $page + 1 ?>" aria-label="Next">
                                            <i class="bi bi-chevron-right"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Modal Ajout Utilisateur -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title" id="addUserModalLabel">Ajouter un utilisateur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <!-- Onglets -->
                <ul class="nav nav-tabs mb-3" id="userTypeTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="student-tab" data-bs-toggle="tab" data-bs-target="#student-tab-pane" type="button" role="tab">
                            Étudiant
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="teacher-tab" data-bs-toggle="tab" data-bs-target="#teacher-tab-pane" type="button" role="tab">
                            Professeur
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="admin-tab" data-bs-toggle="tab" data-bs-target="#admin-tab-pane" type="button" role="tab">
                            Administration
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="mod-tab" data-bs-toggle="tab" data-bs-target="#mod-tab-pane" type="button" role="tab">
                            Modérateur
                        </button>
                    </li>
                </ul>

                <!-- Contenu des onglets -->
                <div class="tab-content" id="userTypeTabContent">
                    <!-- Onglet Étudiant -->
                    <div class="tab-pane fade show active" id="student-tab-pane" role="tabpanel" tabindex="0">
                        <form id="addStudentForm" class="needs-validation" novalidate action="handlers/add_student.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="role" value="etudiant">
                            <?php $formType = 'student';
                            include 'forms/student_fields.php'; ?>
                        </form>
                    </div>

                    <!-- Onglet Professeur -->
                    <div class="tab-pane fade" id="teacher-tab-pane" role="tabpanel" tabindex="0">
                        <form id="addTeacherForm" class="needs-validation" novalidate action="handlers/add_teacher.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="role" value="professeur">
                            <?php $formType = 'teacher';
                            include 'forms/teacher_fields.php'; ?>
                        </form>
                    </div>

                    <!-- Onglet Administration -->
                    <div class="tab-pane fade" id="admin-tab-pane" role="tabpanel" tabindex="0">
                        <form id="addAdminForm" action="handlers/add_admin.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="role" value="administrateur">
                            <?php $formType = 'admin';
                            include 'forms/admin_fields.php'; ?>
                        </form>
                    </div>

                    <!-- Onglet Modérateur -->
                    <div class="tab-pane fade" id="mod-tab-pane" role="tabpanel" tabindex="0">
                        <form id="addModForm" action="handlers/add_moderator.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="role" value="moderateur">
                            <?php $formType = 'mod';
                            include 'forms/mod_fields.php'; ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour la modification -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title" id="editUserModalLabel">Modifier l'utilisateur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <form id="editUserForm" action="handlers/update_user.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="user_id" id="edit_user_id">
                    <input type="hidden" name="user_type" id="edit_user_type">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="edit_firstname" class="form-label">Prénom</label>
                            <input type="text" class="form-control" id="edit_firstname" name="firstname" required>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_lastname" class="form-label">Nom</label>
                            <input type="text" class="form-control" id="edit_lastname" name="lastname" required>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="edit_email" name="email" required>
                        </div>

                        <!-- Champs dynamiques selon le type d'utilisateur -->
                        <div id="edit_specific_fields" class="col-12"></div>

                        <div class="col-12">
                            <label for="edit_photo" class="form-label">Photo de profil</label>
                            <input type="file" class="form-control" id="edit_photo" name="photo" accept="image/*">
                            <div id="current_photo" class="mt-2">
                                <img src="" alt="Photo actuelle" style="max-width: 100px;" class="d-none">
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="js/main.js"></script>
<script src="js/add_student.js"></script>
<script src="js/add_teacher.js"></script>
<script src="js/add_admin.js"></script>
<script src="js/add_moderator.js"></script>

<?php require_once '../../components/footer/footer.php'; ?>