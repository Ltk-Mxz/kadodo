document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');

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

    function getRedirectUrl() {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('redirect') || '/myschoolface/dashboard/';
    }

    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const formData = new FormData(loginForm);
        try {
            const response = await fetch('login.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            
            if (data.success) {
                window.location.href = getRedirectUrl();
            } else {
                if (data.status === 'inactif' || data.status === 'rejete') {
                    // Afficher le modal d'information
                    const modalHtml = `
                        <div class="modal fade" id="statusModal" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Information</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="alert ${data.status === 'inactif' ? 'alert-warning' : 'alert-danger'}">
                                            ${data.message}
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Compris</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;

                    // Supprimer l'ancien modal s'il existe
                    const oldModal = document.getElementById('statusModal');
                    if (oldModal) {
                        oldModal.remove();
                    }

                    // Ajouter et afficher le nouveau modal
                    document.body.insertAdjacentHTML('beforeend', modalHtml);
                    const modal = new bootstrap.Modal(document.getElementById('statusModal'));
                    modal.show();
                } else {
                    showToast(data.message || 'Erreur de connexion', 'danger');
                }
            }
        } catch (error) {
            showToast('Une erreur est survenue', 'danger');
        }
    });
});