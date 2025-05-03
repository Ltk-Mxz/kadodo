<?php
$userRole = $_SESSION['user']['type'];
$dashboardPath = '/myschoolface/' . ROLE_PATHS[$userRole] . '/dashboard/';
?>

<nav class="nav flex-column gap-2">
    <!-- Menus communs -->
    <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/dashboard') ? 'active' : '' ?>"
        href="<?= $dashboardPath ?>">
        <i class="bi bi-speedometer2"></i>
        Tableau de bord
    </a>
    <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/announcements') ? 'active' : '' ?>" href="/myschoolface/announcements/">
        <i class="bi bi-megaphone"></i>
        Annonces
    </a>

    <!-- Menus spécifiques -->
    <?php if ($userRole === 'etudiant'): ?>
        <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/notes') ? 'active' : '' ?>" href="/myschoolface/students/notes/">
            <i class="bi bi-card-checklist"></i>
            Mes Notes
        </a>
        <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/courses') ? 'active' : '' ?>" href="/myschoolface/students/courses/">
            <i class="bi bi-book"></i>
            Mes Cours
        </a>
        <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/documents') ? 'active' : '' ?>" href="/myschoolface/students/documents/">
            <i class="bi bi-file-earmark-text"></i>
            Mes Documents
        </a>
    <?php endif; ?>

    <?php if ($userRole === 'professeur'): ?>
        <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/documents') ? 'active' : '' ?>" href="/myschoolface/professors/documents">
            <i class="bi bi-file-earmark-text"></i>
            Mes Documents
        </a>
        <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/grades') ? 'active' : '' ?>" href="/myschoolface/professors/grades/">
            <i class="bi bi-pencil-square"></i>
            Saisie des Notes
        </a>
        <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/notes') ? 'active' : '' ?>" href="/myschoolface/professors/grades/student_list.php">
            <i class="bi bi-card-list"></i>
            Notes Etudiants
        </a>
        <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/courses') ? 'active' : '' ?>" href="/myschoolface/professors/courses/">
            <i class="bi bi-book"></i>
            Mes Cours
        </a>
        <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/classroom') ? 'active' : '' ?>" href="/myschoolface/professors/classroom/">
            <i class="bi bi-door-open"></i>
            Salle de Classe
        </a>
        <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/calendars') ? 'active' : '' ?>" href="/myschoolface/professors/calendars/">
            <i class="bi bi-calendar2-week"></i>
            Emploi du Temps
        </a>
    <?php endif; ?>

    <!-- Admin -->
    <?php if ($userRole === 'admin'): ?>
        <?php
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT COUNT(*) 
            FROM utilisateur u 
            WHERE u.statut_compte = 'inactif'
        ");
        $stmt->execute();
        $pendingCount = $stmt->fetchColumn();
        ?>
        <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/validations') ? 'active' : '' ?>"
            href="/myschoolface/admin/validations/">
            <i class="bi bi-check2-circle"></i>
            Validation des Comptes
            <?php if ($pendingCount > 0): ?>
                <span class="badge bg-danger rounded-pill ms-2"><?= $pendingCount ?></span>
            <?php endif; ?>
        </a>
        <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/users') ? 'active' : '' ?>" href="/myschoolface/admin/users/">
            <i class="bi bi-people"></i>
            Gestion Utilisateurs
        </a>
        <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/financial') ? 'active' : '' ?>" href="/myschoolface/admin/financial/">
            <i class="bi bi-cash"></i>
            Gestion Financière
        </a>

        <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/professor') ? 'active' : '' ?>" href="/myschoolface/admin/professor/">
            <i class="bi bi-person-workspace"></i>
            Gestion Professeur
        </a>
        <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/emplois-du-temps') ? 'active' : '' ?>" href="/myschoolface/admin/emplois-du-temps/">
            <i class="bi bi-calendar3"></i>
            Gestion Emploi du temps
        </a>
        <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/rooms') ? 'active' : '' ?>" href="/myschoolface/admin/rooms/">
            <i class="bi bi-building"></i>
            Gestion Salle
        </a>
        <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/courses') ? 'active' : '' ?>" href="/myschoolface/admin/courses/">
            <i class="bi bi-journals"></i>
            Gestion Cours
        </a>
        <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/filieres') ? 'active' : '' ?>" href="/myschoolface/admin/filieres/">
            <i class="bi bi-diagram-3"></i>
            Gestion Filières
        </a>
    <?php endif; ?>

    <!-- Moderateur -->

    <?php if ($userRole === 'moderateur'): ?>
        <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/gblog') ? 'active' : '' ?>" href="/myschoolface/moderators/gblog">
            <i class="bi bi-file-richtext"></i>
            Gestion de Blog
        </a>
        <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/annoncements') ? 'active' : '' ?>" href="/myschoolface/moderators/annoncements/addnews.php">
            <i class="bi bi-newspaper"></i>
            Ajouter des Annonces
        </a>
    <?php endif; ?>

    <!-- Menus communs -->
    <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/blog') ? 'active' : '' ?>" href="/myschoolface/blog/">
        <i class="bi bi-pencil-square"></i>
        Blog
    </a>
    <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/notifications') ? 'active' : '' ?>" href="/myschoolface/notifications/">
        <i class="bi bi-bell"></i>
        Notifications
    </a>
    <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/settings') ? 'active' : '' ?>" href="/myschoolface/settings/">
        <i class="bi bi-gear"></i>
        Paramètres
    </a>
    <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/forum') ? 'active' : '' ?>" href="/myschoolface/forum/">
        <i class="bi bi-chat-square-text"></i>
        Forum
    </a>
    <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/chats') ? 'active' : '' ?>" href="/myschoolface/chats/">
        <i class="bi bi-chat-dots"></i>
        Messagerie
    </a>
    <a class="nav-link text-danger" href="/myschoolface/auth/logout.php">
        <i class="bi bi-box-arrow-right"></i>
        Déconnexion
    </a>
</nav>