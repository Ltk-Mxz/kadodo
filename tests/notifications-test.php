<?php
require_once '../components/header/header.php';
require_once '../components/notifications/notification.php';
?>

<div class="container mt-4">
    <h2>Test des Notifications</h2>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Envoyer une notification de test</h5>
                    <div id="alertContainer"></div>
                    <form id="testNotificationForm">
                        <div class="mb-3">
                            <label class="form-label">Type</label>
                            <select class="form-select" name="type">
                                <option value="info">Information</option>
                                <option value="success">Succès</option>
                                <option value="warning">Avertissement</option>
                                <option value="error">Erreur</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Message</label>
                            <input type="text" class="form-control" name="message" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Envoyer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('testNotificationForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const form = e.target;
        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.disabled = true;

        try {
            const formData = new FormData(form);
            const response = await fetch('../handlers/send_test_notification.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            const alertContainer = document.getElementById('alertContainer');
            alertContainer.innerHTML = `
            <div class="alert alert-${data.success ? 'success' : 'danger'} alert-dismissible fade show" role="alert">
                ${data.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;

            if (data.success) {
                form.reset();
            }
        } catch (error) {
            console.error('Erreur:', error);
            document.getElementById('alertContainer').innerHTML = `
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                Une erreur est survenue
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        } finally {
            submitBtn.disabled = false;
        }
    });
</script>

<?php require_once '../components/footer/footer.php'; ?>