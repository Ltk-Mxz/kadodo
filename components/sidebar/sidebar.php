<?php
require_once __DIR__ . '/../../utils/auth.php';
$userRole = $_SESSION['user']['type'] ?? null;

if (!in_array($userRole, ROLES)) {
    header('Location: /myschoolface/403.php');
    exit();
}
?>

<!-- Version Desktop -->
<div class="sidebar d-none d-md-block" style="overflow-y: scroll;">
    <nav class=" nav flex-column">
        <?php require_once 'nav.php'; ?>
    </nav>
</div>

<!-- Version mobile -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="sidebar" aria-labelledby="sidebarLabel" style="overflow-y: scroll;">
    <div class=" offcanvas-header">
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-0">
        <h5 class="offcanvas-title ms-2" id="sidebarLabel">Menu</h5>

        <nav class="nav flex-column">
            <?php include 'nav.php'; ?>
        </nav>
    </div>
</div>