<?php
require_once '../../utils/auth.php';
require_once '../../config/database.php';

$db = Database::getInstance()->getConnection();

// Récupérer les filières
$stmt = $db->query("SELECT id_filiere, libelle_filiere FROM filiere ORDER BY libelle_filiere");
$filieres = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les niveaux 
$stmt = $db->query("SELECT id_niveau, libelle_niveau FROM niveau ORDER BY libelle_niveau");
$niveaux = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Vérifier si l'utilisateur est déjà connecté
if (isAuthenticated()) {
    $redirectUrl = match ($_SESSION['user']['type']) {
        'etudiant' => '/myschoolface/students/dashboard/',
        'professeur' => '/myschoolface/professors/dashboard/',
        'admin' => '/myschoolface/admin/dashboard/',
        'moderateur' => '/myschoolface/moderators/dashboard/',
        default => '/myschoolface/404.php'
    };
    header("Location: $redirectUrl");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MySchoolFace - Inscription</title>
    <link href="/myschoolface/assets/bootstrap/bootstrap.min-5.2.3.css" rel="stylesheet">
    <link href="/myschoolface/assets/css/styles.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
</head>

<body>
    <div id="notifications-container" class="position-fixed top-0 end-0 p-3" style="z-index: 1050;"></div>

    <div class="register-container">
        <div class="register-box">
            <div class="text-center mb-4">
                <img src="/myschoolface/assets/images/logo.png" alt="Kadodo Logo" class="logo">
            </div>

            <div class="register-header">
                <h1>Créer un compte</h1>
                <p>Rejoignez MySchoolFace dès aujourd'hui</p>
            </div>

            <!-- Modification des tabs -->
            <div class="register-tabs">
                <ul class="nav nav-tabs nav-fill" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="student-tab-btn" data-bs-toggle="tab" data-bs-target="#student-tab" type="button" role="tab" aria-controls="student-tab" aria-selected="true">
                            Étudiant
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="teacher-tab-btn" data-bs-toggle="tab" data-bs-target="#teacher-tab" type="button" role="tab" aria-controls="teacher-tab" aria-selected="false">
                            Professeur
                        </button>
                    </li>
                </ul>

                <div class="tab-content mt-4">
                    <div class="tab-pane fade show active" id="student-tab">
                        <form id="studentRegisterForm" action="handlers/register_student.php" method="POST" enctype="multipart/form-data">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Prénom</label>
                                    <input type="text" name="firstname" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Nom</label>
                                    <input type="text" name="lastname" class="form-control" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="filiere" class="form-label">Filière</label>
                                    <select class="form-select" id="filiere" name="filiere" required>
                                        <option value="">Choisir une filière...</option>
                                        <?php foreach ($filieres as $filiere): ?>
                                            <option value="<?= htmlspecialchars($filiere['id_filiere']) ?>">
                                                <?= htmlspecialchars($filiere['libelle_filiere']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback">
                                        Veuillez choisir une filière
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="niveau" class="form-label">Niveau</label>
                                    <select class="form-select" id="niveau" name="niveau" required>
                                        <option value="">Choisir un niveau...</option>
                                        <?php foreach ($niveaux as $niveau): ?>
                                            <option value="<?= htmlspecialchars($niveau['id_niveau']) ?>">
                                                <?= htmlspecialchars($niveau['libelle_niveau']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback">
                                        Veuillez choisir un niveau
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Mot de passe</label>
                                    <input type="password" name="password" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Confirmer le mot de passe</label>
                                    <input type="password" name="confirm_password" class="form-control" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Photo de profil</label>
                                    <input type="file" name="photo" class="form-control" accept="image/*">
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary w-100">S'inscrire</button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="tab-pane fade" id="teacher-tab">
                        <form id="teacherRegisterForm" action="handlers/register_teacher.php" method="POST" enctype="multipart/form-data">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Prénom</label>
                                    <input type="text" name="firstname" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Nom</label>
                                    <input type="text" name="lastname" class="form-control" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Spécialité</label>
                                    <input type="text" name="specialite" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Mot de passe</label>
                                    <input type="password" name="password" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Confirmer le mot de passe</label>
                                    <input type="password" name="confirm_password" class="form-control" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Photo de profil</label>
                                    <input type="file" name="photo" class="form-control" accept="image/*">
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary w-100">S'inscrire</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="register-footer mt-4 text-center">
                <p>Déjà membre ? <a href="/myschoolface/auth/login">Se connecter</a></p>
            </div>
        </div>
    </div>

    <script src="/myschoolface/assets/bootstrap/popper-2.11.6.min.js"></script>
    <script src="/myschoolface/assets/bootstrap/bootstrap.min-5.2.3.js"></script>
    <script src="js/register.js"></script>

    <!-- Initialisation des tabs directement avec le sélecteur -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const firstTabTrigger = document.querySelector('#student-tab-btn');
            if (firstTabTrigger) {
                const tab = new bootstrap.Tab(firstTabTrigger);
                tab.show();
            }
        });
    </script>
</body>

</html>