document.addEventListener('DOMContentLoaded', function() {
    const studentForm = document.getElementById('studentRegisterForm');
    const teacherForm = document.getElementById('teacherRegisterForm');

    function showToast(message, type = 'success') {
        const container = document.getElementById('notifications-container');
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type} border-0`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');

        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        `;

        container.appendChild(toast);
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();

        toast.addEventListener('hidden.bs.toast', () => {
            container.removeChild(toast);
        });
    }

    async function handleSubmit(e, isStudent = true) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.disabled = true;

        try {
            const formData = new FormData(form);
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            
            if (data.success) {
                // Créer et afficher le modal avec les informations
                const modalHtml = `
                    <div class="modal fade" id="credentialsModal" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Informations de compte</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="alert alert-info">
                                        <p>Bonjour ${data.credentials.prenom} ${data.credentials.nom},</p>
                                        <p>Votre compte a été créé avec succès et est en attente de validation!</p>
                                        <hr>
                                        <p><strong>Votre matricule :</strong> ${data.credentials.matricule}</p>
                                        <p class="text-warning">
                                            <strong>Important :</strong> ${data.credentials.statut}
                                            <br>
                                            Vous recevrez un email lorsque votre compte sera validé.
                                        </p>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Compris</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                // Ajouter le modal au document
                document.body.insertAdjacentHTML('beforeend', modalHtml);
                
                const modal = new bootstrap.Modal(document.getElementById('credentialsModal'));
                modal.show();

                // Rediriger vers la page de connexion après la fermeture du modal
                document.getElementById('credentialsModal').addEventListener('hidden.bs.modal', () => {
                    window.location.href = '/myschoolface/auth/login';
                });
            } else {
                throw new Error(data.message);
            }
        } catch (error) {
            showToast(error.message || 'Une erreur est survenue', 'danger');
        } finally {
            submitBtn.disabled = false;
        }
    }

    if (studentForm) {
        studentForm.addEventListener('submit', e => handleSubmit(e, true));
    }

    if (teacherForm) {
        teacherForm.addEventListener('submit', e => handleSubmit(e, false));
    }
});
