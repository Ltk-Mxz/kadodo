document.addEventListener('DOMContentLoaded', function() {
    loadPendingUsers();

    function showToast(message, type = 'success') {
        const toast = new bootstrap.Toast(createToast(message, type));
        toast.show();
    }

    function getRoleClass(role) {
        const roleMapping = {
            'etudiant': 'bg-success bg-opacity-10 text-success',
            'professeur': 'bg-primary bg-opacity-10 text-primary',
            'admin': 'bg-danger bg-opacity-10 text-danger',
            'moderateur': 'bg-warning bg-opacity-10 text-warning'
        };
        return roleMapping[role.toLowerCase()] || '';
    }

    function getStatusClass(status) {
        const statusMapping = {
            'actif': 'bg-success',
            'inactif': 'bg-warning',
            'rejete': 'bg-danger'
        };
        return statusMapping[status.toLowerCase()] || 'bg-secondary';
    }

    function formatStatus(status) {
        const statusLabels = {
            'actif': 'Actif',
            'inactif': 'En attente',
            'rejete': 'Rejeté'
        };
        return statusLabels[status.toLowerCase()] || status;
    }

    async function loadPendingUsers() {
        try {
            const response = await fetch('handlers/get_pending.php');
            const data = await response.json();
            const tbody = document.getElementById('pendingUsers');

            if (data.success) {
                if (data.users.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="bi bi-check-circle text-success" style="font-size: 3rem;"></i>
                                    <p class="mt-3 text-muted">Aucune demande d'approbation en attente</p>
                                </div>
                            </td>
                        </tr>
                    `;
                    return;
                }

                tbody.innerHTML = data.users.map(user => `
                    <tr data-user-id="${user.id_utilisateur}" class="user-row">
                        <td>${user.matricule}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img src="/myschoolface/uploads/avatar/${user.photo_profile}" 
                                     alt="Photo" class="rounded-circle" width="32" height="32">
                                ${user.prenom} ${user.nom}
                            </div>
                        </td>
                        <td>${user.email}</td>
                        <td><span class="badge ${getRoleClass(user.nom_role)}">${user.nom_role}</span></td>
                        <td>
                            <span class="badge ${getStatusClass(user.statut_compte)}">
                                ${formatStatus(user.statut_compte)}
                            </span>
                        </td>
                        <td class="actions">
                            <div class="btn-group">
                                <button onclick="validateUser(${user.id_utilisateur}, this)" class="btn btn-success btn-sm">
                                    <i class="bi bi-check-lg"></i> Valider
                                </button>
                                <button onclick="rejectUser(${user.id_utilisateur}, this)" class="btn btn-danger btn-sm">
                                    <i class="bi bi-x-lg"></i> Rejeter
                                </button>
                            </div>
                        </td>
                    </tr>
                `).join('');
            }
        } catch (error) {
            showToast('Erreur lors du chargement des utilisateurs', 'danger');
        }
    }

    window.validateUser = async function(userId, button) {
        try {
            if (button) button.disabled = true;
            const row = button.closest('tr');
            
            const response = await fetch('handlers/validate_user.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ userId, action: 'validate' })
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Animation de suppression
                await animateAndRemoveRow(row);
                checkEmptyState();
                showToast('Compte validé avec succès', 'success');
            } else {
                throw new Error(data.message || 'Erreur lors de la validation');
            }
        } catch (error) {
            console.error('Erreur:', error);
            if (button) button.disabled = false;
            showToast(error.message || 'Erreur lors de la validation', 'danger');
        }
    };

    window.rejectUser = async function(userId, button) {
        if (!confirm('Êtes-vous sûr de vouloir rejeter cette demande ?')) return;
        
        try {
            button.disabled = true;
            const row = button.closest('tr');
            
            const response = await fetch('handlers/validate_user.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ userId, action: 'reject' })
            });
            
            const data = await response.json();
            
            if (data.success) {
                await animateAndRemoveRow(row);
                checkEmptyState();
                showToast('Compte rejeté', 'warning');
            }
        } catch (error) {
            button.disabled = false;
            showToast('Erreur lors du rejet', 'danger');
        }
    };

    function animateAndRemoveRow(row) {
        return new Promise(resolve => {
            row.style.transition = 'all 0.5s ease';
            row.style.opacity = '0';
            row.style.transform = 'translateX(100%)';
            
            setTimeout(() => {
                row.remove();
                resolve();
            }, 500);
        });
    }

    function checkEmptyState() {
        const tbody = document.getElementById('pendingUsers');
        if (tbody.children.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center py-5">
                        <div class="empty-state">
                            <i class="bi bi-check-circle text-success" style="font-size: 3rem;"></i>
                            <p class="mt-3 text-muted">Aucune demande d'approbation en attente</p>
                        </div>
                    </td>
                </tr>
            `;
        }
    }

    // Initial load
    loadPendingUsers();
});
