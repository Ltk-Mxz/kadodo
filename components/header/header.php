<?php require_once 'pre-header.php'; ?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kadodo - <?= htmlspecialchars($pageTitle) ?></title>
    <link rel="icon" type="image/png" href="<?= LOGO_PATH ?>">

    <!-- <link href="<?= ASSETS_PATH ?>/bootstrap/bootstrap.min-5.2.3.css" rel="stylesheet">
    <link href="<?= ASSETS_PATH ?>/icons/font/bootstrap-icons.min.css" rel="stylesheet"> -->

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <link href="<?= COMPONENTS_PATH ?>/header/styles.css" rel="stylesheet">
    <link href="<?= COMPONENTS_PATH ?>/sidebar/styles.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= CSS_PATH ?>/notes.css">
    <link href="<?= CSS_PATH ?>/notifications.css" rel="stylesheet">
    <script src="<?= JS_PATH ?>/notifications.js"></script>

    <?php if (str_contains($_SERVER['REQUEST_URI'], '/settings/')): ?>
        <script src="<?= BASE_PATH ?>/assets/js/qrcode.min.js"></script>
    <?php endif; ?>

    <script>
        // Demander la permission pour les notifications au chargement
        document.addEventListener('DOMContentLoaded', async () => {
            if ('Notification' in window && Notification.permission === 'default') {
                const result = await new Promise(resolve => {
                    const modal = new bootstrap.Modal(document.createElement('div'));
                    modal.element.innerHTML = `
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Activer les notifications</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p>Souhaitez-vous recevoir des notifications pour rester informé des nouveaux messages et activités ?</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" onclick="resolve(false)" data-bs-dismiss="modal">Non merci</button>
                                    <button type="button" class="btn btn-primary" onclick="resolve(true)" data-bs-dismiss="modal">Activer</button>
                                </div>
                            </div>
                        </div>
                    `;
                    modal.show();
                });

                if (result) {
                    window.notificationManager?.requestNotificationPermission();
                }
            }
        });
    </script>
</head>

<body>
    <!-- Desktop Header -->
    <header class="header">
        <div class="d-flex align-items-center justify-content-between w-100">
            <button class="menu-btn" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebar">
                <i class="bi bi-list"></i>
            </button>
            <a href="../dashboard">
                <img src="<?= ASSETS_PATH ?>/images/logo.png" alt="Kadodo Logo" class="header-logo" style="height: 200px;">
            </a>
            <div class="d-flex align-items-center gap-4">
                <div class="position-relative">
                    <?php
                    // Récupérer le nombre de notifications non lues
                    $db = Database::getInstance()->getConnection();
                    $stmt = $db->prepare("
                        SELECT COUNT(*) FROM (
                            SELECT id_notification FROM notifications 
                            WHERE id_destinataire = ? AND lu = 0
                            UNION
                            SELECT id_utilisateur FROM utilisateur 
                            WHERE statut_compte = 'inactif'
                        ) total
                    ");
                    $stmt->execute([$_SESSION['user']['id']]);
                    $notifCount = $stmt->fetchColumn();
                    ?>
                    <button type="button" class="notification-btn" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-bell"></i>
                        <?php if ($notifCount > 0): ?>
                            <div class="notification-badge"><?= $notifCount ?></div>
                        <?php endif; ?>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end notifications-dropdown">
                        <div class="dropdown-header">
                            <span>Notifications</span>
                        </div>
                        <div id="notifications-container" class="notifications-list">
                        </div>
                        <a href="<?= BASE_PATH ?>/notifications/" class="view-all" style="text-decoration: none;">
                            Voir toutes les notifications
                            <i class="bi bi-arrow-right-short"></i>
                        </a>
                    </div>
                </div>
                <div class="dropdown">
                    <div class="d-flex align-items-center gap-2" role="button" data-bs-toggle="dropdown">
                        <img src="<?= htmlspecialchars(AVATAR_PATH . '/' . getAvatarPath($userInfo['photo_profile'])) ?>"
                            alt="<?= htmlspecialchars($userInfo['prenom'] . ' ' . $userInfo['nom']) ?>"
                            class="user-avatar">
                        <span class="user-name d-none d-md-block">
                            <?= htmlspecialchars(($userInfo['prenom'] ?? '') . ' ' . ($userInfo['nom'] ?? '')) ?>
                        </span>
                        <i class="bi bi-chevron-down"></i>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <div class="px-4 py-3 border-bottom">
                                <div class="d-flex align-items-center">
                                    <img src="<?= htmlspecialchars(AVATAR_PATH . '/' . getAvatarPath($userInfo['photo_profile'])) ?>"
                                        alt="<?= htmlspecialchars($userInfo['prenom'] . ' ' . $userInfo['nom']) ?>"
                                        class="rounded-circle"
                                        width="50">
                                    <div class="ms-3">
                                        <h6 class="mb-1"><?= htmlspecialchars(($userInfo['prenom'] ?? '') . ' ' . ($userInfo['nom'] ?? '')) ?></h6>
                                        <small class="text-muted d-block"><?= htmlspecialchars($userInfo['email'] ?? '') ?></small>
                                        <small class="text-muted d-block"><?= htmlspecialchars($userInfo['matricule'] ?? '') ?></small>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li><a class="dropdown-item" href="<?= BASE_PATH ?>/settings/"><i class="bi bi-gear me-2"></i>Paramètres</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item text-danger" href="<?= BASE_PATH ?>/auth/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Déconnexion</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </header>

    <!-- Mobile Header -->
    <div class="mobile-header d-md-none">
        <button class="mobile-menu-btn" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebar">
            <i class="bi bi-list"></i>
        </button>

        <img src="<?= ASSETS_PATH ?>/images/logo.png" alt="Kadodo Logo" class="header-logo" style="height: 150px;">

        <div class="mobile-actions">
            <!-- Notifications sur mobile -->
            <div class="dropdown position-static">
                <button type="button" class="notification-btn" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-bell"></i>
                    <div class="notification-badge" style="display: <?= ($unreadNotifications > 0 ? 'flex' : 'none') ?>">
                        <?= $unreadNotifications ?? 0 ?>
                    </div>
                </button>
                <div class="dropdown-menu">
                    <div class="dropdown-header">
                        <span>Notifications</span>
                    </div>
                    <div id="notifications-container-mobile" class="notifications-list">
                    </div>
                    <a href="<?= BASE_PATH ?>/notifications/" class="view-all">
                        Voir toutes les notifications
                        <i class="bi bi-arrow-right-short"></i>
                    </a>
                </div>
            </div>

            <!-- Avatar et menu sur mobile -->
            <div class="dropdown position-static">
                <img src="<?= htmlspecialchars(AVATAR_PATH . '/' . getAvatarPath($userInfo['photo_profile'])) ?>"
                    alt="<?= htmlspecialchars($userInfo['prenom'] . ' ' . $userInfo['nom']) ?>"
                    class="user-avatar"
                    data-bs-toggle="dropdown"
                    role="button">
                <div class="dropdown-menu">
                    <li>
                        <div class="px-4 py-3 border-bottom">
                            <div class="d-flex align-items-center">
                                <img src="<?= htmlspecialchars(AVATAR_PATH . '/' . getAvatarPath($userInfo['photo_profile'])) ?>"
                                    alt="<?= htmlspecialchars($userInfo['prenom'] . ' ' . $userInfo['nom']) ?>"
                                    class="rounded-circle"
                                    width="50">
                                <div class="ms-3">
                                    <h6 class="mb-1"><?= htmlspecialchars(($userInfo['prenom'] ?? '') . ' ' . ($userInfo['nom'] ?? '')) ?></h6>
                                    <small class="text-muted d-block"><?= htmlspecialchars($userInfo['email'] ?? '') ?></small>
                                    <small class="text-muted d-block"><?= htmlspecialchars($userInfo['matricule'] ?? '') ?></small>
                                </div>
                            </div>
                        </div>
                    </li>
                    <li><a class="dropdown-item" href="<?= BASE_PATH ?>/settings/"><i class="bi bi-gear me-2"></i>Paramètres</a></li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li><a class="dropdown-item text-danger" href="<?= BASE_PATH ?>/auth/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Déconnexion</a></li>
                </div>
            </div>
        </div>
    </div>