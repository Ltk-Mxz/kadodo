<?php

session_start();

$pageTitle = "Paramètres";
require_once '../components/header/header.php';
require_once '../config/database.php';

$id_user = $_SESSION['user']['id'];
echo $id_user;
$db = Database::getInstance()->getConnection();
$sql = "SELECT * FROM utilisateur WHERE id_utilisateur= $id_user";
$reponse = $db->query($sql);
$utilisateur = $reponse->fetch(PDO::FETCH_ASSOC);

?>

<head>
</head>

<div class="container-fluid">
    <div class="row">
        <?php require_once '../components/sidebar/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div id="notifications-container" class="position-fixed top-0 end-0 p-3" style="z-index: 1050;"></div>
            <div class="settings-container">
                <h1 class="settings-title">Paramètres du compte</h1>
                <p class="settings-subtitle">Gérez vos informations personnelles et la sécurité de votre compte</p>

                <div class="profile-section">
                    <div class="row">

                        <div class="col-md-12">
                            <div class="col-md-12">

                            </div>
                            <form class="settings-form" id="loginForm" action="update_sett.php" method="POST" enctype="multipart/form-data">
                                <div class="profile-photo col-md-12">
                                    <div class="profile-wrapper">
                                        <div class="avatar-qr-container">
                                            <div class="avatar-section">
                                                <div class="avatar-container">
                                                    <img src="../uploads/avatar/<?= getAvatarPath($utilisateur['photo_profile']) ?>" alt="Photo de profil" id="profile-image">
                                                    <input type="file" id="photo-upload" name="avatar" hidden accept="image/jpeg,image/png,image/gif">
                                                    <svg class="camera-icon bi bi-camera" onclick="document.getElementById('photo-upload').click()" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                                        <path d="M15 12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1h1.172a3 3 0 0 0 2.12-.879l.83-.828A1 1 0 0 1 6.827 3h2.344a1 1 0 0 1 .707.293l.828.828A3 3 0 0 0 12.828 5H14a1 1 0 0 1 1 1v6zM2 4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-1.172a2 2 0 0 1-1.414-.586l-.828-.828A2 2 0 0 0 9.172 2H6.828a2 2 0 0 0-1.414.586l-.828.828A2 2 0 0 1 3.172 4H2z" />
                                                        <path d="M8 11a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5zm0 1a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7zM3 6.5a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0z" />
                                                    </svg>
                                                </div>
                                                <small class="text-muted d-block mt-2">JPG, GIF, PNG ou JFIF. Max 2MB</small>
                                            </div>
                                            <div class="qr-section">
                                                <div id="qrcode" class="qr-code-container"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="firstname">Prénom</label>
                                        <input type="text" class="form-control" id="firstname" value="<?= $utilisateur['prenom'] ?>" name="firstname">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="lastname">Nom</label>
                                        <input type="text" class="form-control" id="lastname" value="<?= $utilisateur['nom'] ?>" name="lastname">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="email">Email</label>
                                        <input type="email" class="form-control" id="email" value="<?= $utilisateur['email'] ?>" name="email">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="phone">Téléphone</label>
                                        <input type="tel" class="form-control" id="phone" value="<?= $utilisateur['tel'] ?>" name="tel">
                                    </div>
                                </div>

                                <div class="form-actions mt-4">
                                    <button type="submit" class="btn btn-primary" name="modif">Enregistrer les modifications</button>
                                </div>
                            </form>
                            <form class="settings-form" id="loginForm" action="update_pass.php" method="POST">

                                <div class="password-section mt-5">
                                    <h2>Changer le mot de passe</h2>
                                    <div class="mb-3">
                                        <label for="new-password">Nouveau mot de passe</label>
                                        <input type="password" class="form-control" id="motDePasse" name="new-password" required>
                                    </div>
                                    <div class="mb-3">
                                        <p id="message"></p>
                                        <label for="confirm-password">Confirmer le nouveau mot de passe</label>
                                        <input type="password" class="form-control" id="confirmationMotDePasse" name="confirm-password" required>
                                    </div>
                                    <div class="form-actions mt-4">
                                        <button type="button" class="btn btn-secondary" id="monBoutonAnnuler">Annuler</button>
                                        <button type="submit" class="btn btn-primary" id="monBoutonSoumettre" name="modif1">Enregistrer les modifications</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
    const userId = "<?php echo htmlspecialchars($utilisateur['matricule'], ENT_QUOTES, 'UTF-8'); ?>";
</script>
<script src="js/qrcode.js"></script>

<script>
    document.getElementById('photo-upload').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('profile-image').src = e.target.result;
            }
            reader.readAsDataURL(file);
        }
    });

    const motDePasse = document.getElementById('motDePasse');
    const monBoutonAnnuler = document.getElementById('monBoutonAnnuler');
    const confirmationMotDePasse = document.getElementById('confirmationMotDePasse');
    const message = document.getElementById('message');
    const boutonSoumettre = document.getElementById('monBoutonSoumettre');

    monBoutonAnnuler.addEventListener('click', function() {
        confirmationMotDePasse.value = "";
        motDePasse.value = "";
        message.textContent = "";
    });

    confirmationMotDePasse.addEventListener('keyup', function() {
        if (motDePasse.value === confirmationMotDePasse.value || confirmationMotDePasse.value === motDePasse.value) {
            message.textContent = 'Les mots de passe correspondent.';
            message.style.color = 'green';
            boutonSoumettre.disabled = false;
        } else {
            message.textContent = 'Les mots de passe ne correspondent pas.';
            message.style.color = 'red';
            boutonSoumettre.disabled = true;
        }
    });
</script>
<?php require_once '../components/footer/footer.php'; ?>