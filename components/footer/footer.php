<!-- Script Bootstrap Bundle avec Popper -->
<script src="<?= ASSETS_PATH ?>/bootstrap/popper-2.11.6.min.js"></script>
<script src="<?= ASSETS_PATH ?>/bootstrap/bootstrap.min-5.2.3.js"></script>

<?php if (str_contains($_SERVER['REQUEST_URI'], '/admin/classes/')): ?>
    <script src="js/classes.js"></script>
<?php endif; ?>

<?php if (str_contains($_SERVER['REQUEST_URI'], '/admin/rooms/')): ?>
    <script src="js/rooms.js"></script>
<?php endif; ?>

<footer class="footer mt-auto py-3 bg-light">
    <div class="container text-center">
        <span class="text-muted">© 2024 Kadodo. Tous droits réservés.</span>
    </div>
</footer>

</body>

</html>