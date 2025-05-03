document.addEventListener('DOMContentLoaded', function() {
    const filiereForm = document.getElementById('filiereForm');
    const filieresTable = document.getElementById('filieresTable');
    const filiereModal = document.getElementById('filiereModal');
    const modal = new bootstrap.Modal(filiereModal);

    function showToast(message, type = 'success') {
        const toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        toastContainer.innerHTML = `
            <div class="toast show" role="alert">
                <div class="toast-header">
                    <strong class="me-auto">Notification</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body bg-${type} text-white">
                    ${message}
                </div>
            </div>
        `;
        document.body.appendChild(toastContainer);
        setTimeout(() => toastContainer.remove(), 3000);
    }

    async function loadFilieres() {
        try {
            const response = await fetch('handlers/get_filieres.php');
            const data = await response.json();
            
            const tbody = filieresTable.querySelector('tbody');
            if (data.success) {
                tbody.innerHTML = data.filieres.map(filiere => `
                    <tr data-filiere-id="${filiere.id_filiere}">
                        <td>${filiere.libelle_filiere}</td>
                        <td>${filiere.nombre_etudiants}</td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="editFiliere(${filiere.id_filiere})">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="deleteFiliere(${filiere.id_filiere})">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                `).join('');
            }
        } catch (error) {
            showToast('Erreur lors du chargement des filières', 'danger');
        }
    }

    filiereForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        try {
            const formData = new FormData(this);
            const response = await fetch('handlers/save_filiere.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            
            if (data.success) {
                showToast(data.message);
                modal.hide();
                this.reset();
                loadFilieres();
            } else {
                throw new Error(data.message);
            }
        } catch (error) {
            showToast(error.message, 'danger');
        }
    });

    window.editFiliere = async function(id) {
        try {
            const response = await fetch(`handlers/get_single_filiere.php?id=${id}`);
            const data = await response.json();
            
            if (data.success && data.filiere) {
                const filiere = data.filiere;
                
                // Remplir le formulaire
                document.getElementById('filiereId').value = filiere.id_filiere;
                document.querySelector('input[name="libelle_filiere"]').value = filiere.libelle_filiere;
                
                // Mettre à jour le titre du modal - Fixed selector
                document.querySelector('#filiereModal .modal-title').textContent = 'Modifier une filière';
                
                // Changer le texte du bouton submit
                const submitBtn = document.querySelector('#filiereForm button[type="submit"]');
                if (submitBtn) {
                    submitBtn.textContent = 'Modifier';
                }
                
                // Afficher le modal
                modal.show();
            } else {
                throw new Error(data.message || 'Erreur lors du chargement de la filière');
            }
        } catch (error) {
            console.error('Erreur:', error);
            showToast('Erreur lors du chargement de la filière', 'danger');
        }
    };

    window.deleteFiliere = async function(id) {
        if (!confirm('Êtes-vous sûr de vouloir supprimer cette filière et toutes ses données associées ?')) {
            return;
        }
        
        try {
            const row = document.querySelector(`tr[data-filiere-id="${id}"]`);
            if (row) {
                row.style.backgroundColor = '#ffe6e6';
                row.style.transition = 'all 0.3s ease';
            }

            const response = await fetch('handlers/delete_filiere.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id_filiere: id })
            });

            const data = await response.json();
            
            if (data.success) {
                if (row) {
                    row.style.opacity = '0';
                    row.style.transform = 'translateX(100%)';
                    await new Promise(resolve => setTimeout(resolve, 300));
                    row.remove();
                }
                showToast('Filière supprimée avec succès');
            } else {
                if (row) {
                    row.style.backgroundColor = '';
                }
                throw new Error(data.message);
            }
        } catch (error) {
            showToast(error.message || 'Erreur lors de la suppression', 'danger');
        }
    };

    loadFilieres();
});
