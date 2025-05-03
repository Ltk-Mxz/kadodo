<?php
require_once '../../utils/auth.php';

// Vérifier si l'utilisateur est déjà connecté
if (isAuthenticated()) {
    $redirectUrl = match ($_SESSION['user']['type']) {
        'etudiant' => '/myschoolface/students/dashboard/',
        'professeur' => '/myschoolface/professors/dashboard/',
        'admin' => '/myschoolface/admin/dashboard/',
        'moderateur' => '/myschoolface/moderators/dashboard/',
        default => '/myschoolface/'
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
    <title>MySchoolFace - Connexion</title>
    <!-- Favicon -->
    <link rel="icon" type="image/jpg" href="../../assets/images/logoesa.jpg">
    <!-- Bootstrap CSS V5.3.0 -->
    <link href="../../assets/bootstrap/bootstrap.min-5.2.3.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
    <link href="../../assets/css/notifications.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body>
    <!-- Container pour les notifications -->
    <div id="notifications-container" class="position-fixed top-0 end-0 p-3" style="z-index: 1050;"></div>

    <div class="container">
        <div class="login-container">
            <div class="text-center mb-4">
                <img src="../../assets/images/logo.png" alt="Kadodo Logo" class="logo">
                <h1 class="h4 mb-2">Connexion</h1>
                <p class="text-muted">Accédez à votre espace intranet</p>
            </div>

            <form id="loginForm">
                <div class="mb-3">
                    <label for="identifiant" class="form-label">Matricule</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border border-end-0">
                            <i class="bi bi-person"></i>
                        </span>
                        <input type="text" name="identifiant" class="form-control" id="identifiant" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Mot de passe</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border border-end-0">
                            <i class="bi bi-lock"></i>
                        </span>
                        <input type="password" name="password" class="form-control" id="password" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-connect mb-3">Se connecter</button>
            </form>

            <div class="auth-links-container mt-4 p-3 rounded-3">
                <h6 class="text-center mb-3">Options de connexion</h6>
                <div class="row row-cols-1 row-cols-md-3 g-3 text-center">
                    <div class="col">
                        <button onclick="window.location.href='/myschoolface/auth/register/'" class="btn h-100 w-100 btn-outline-primary auth-button">
                            <i class="bi bi-person-plus me-2"></i>
                            Créer un compte
                        </button>
                    </div>
                    <div class="col">
                        <button onclick="window.location.href='/myschoolface/guest/'" class="btn auth-button btn-outline-primary h-100 w-100">
                            <i class="bi bi-box-arrow-in-right me-2"></i>
                            Mode invité
                        </button>
                    </div>
                    <div class="col">
                        <button class="btn btn-outline-primary auth-button h-100 w-100" data-bs-toggle="modal" data-bs-target="#supportModal">
                            <i class="bi bi-question-circle me-2"></i>
                            Support technique
                        </button>
                    </div>
                </div>
            </div>

            <p class="text-center text-muted mt-4 small">© 2025 Kadodo. Tous droits réservés.</p>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="../../assets/bootstrap/popper-2.11.6.min.js"></script>
    <script src="../../assets/bootstrap/bootstrap.min-5.2.3.js"></script>

    <script>
        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            try {
                const formData = new FormData(e.target);
                const response = await fetch('login.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                // Afficher la notification
                const notification = `
                    <div class="toast align-items-center border-0 bg-${data.success ? 'success' : 'danger'}" role="alert">
                        <div class="d-flex">
                            <div class="toast-body text-white">${data.message}</div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                        </div>
                    </div>
                `;

                const container = document.getElementById('notifications-container');
                container.innerHTML = notification;
                const toast = new bootstrap.Toast(container.querySelector('.toast'));
                toast.show();

                if (data.success && data.redirect) {
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 2000);
                }
            } catch (error) {
                console.error('Erreur:', error);
            }
        });
    </script>
</body>

</html>