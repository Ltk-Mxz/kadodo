document.addEventListener('DOMContentLoaded', function() {
    const roomsTable = document.getElementById('roomsTable');
    const roomForm = document.getElementById('roomForm');
    const modal = document.getElementById('roomModal');
    const bootstrapModal = new bootstrap.Modal(modal);
    
    let isSubmitting = false; // Drapeau pour éviter la double soumission

    // Charger les salles
    async function loadRooms() {
        try {
            const response = await fetch('handlers/get_rooms.php');
            const data = await response.json();
            
            const tbody = roomsTable.querySelector('tbody');
            if (data.success) {
                tbody.innerHTML = data.rooms.map(room => `
                    <tr>
                        <td>${room.nom_salle}</td>
                        <td>${room.capacite} places</td>
                        <td>${room.equipements || 'Aucun'}</td>
                        <td>
                            <span class="badge ${room.disponible ? 'bg-success' : 'bg-danger'}">
                                ${room.disponible ? 'Disponible' : 'Occupée'}
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="editRoom(${room.id_salle})">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="deleteRoom(${room.id_salle})">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                `).join('');
            }
        } catch (error) {
            showToast('Erreur lors du chargement des salles', 'danger');
        }
    }

    // Initialisation
    loadRooms();
    
    // Gérer la soumission du formulaire
    roomForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        if (isSubmitting) return; // Évite la double soumission
        isSubmitting = true;

        try {
            const formData = new FormData(roomForm);
            const response = await fetch('handlers/save_room.php', {  // Ajout de .php
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                showToast(data.message, 'success');
                bootstrapModal.hide();
                roomForm.reset();
                await loadRooms();
            } else {
                showToast(data.message, 'danger');
            }
        } catch (error) {
            showToast('Erreur lors de l\'enregistrement', 'danger');
        } finally {
            isSubmitting = false;
        }
    });

    function showToast(message, type = 'success') {
        const container = document.getElementById('notifications-container');
        if (!container) {
            console.error('Notifications container not found');
            return;
        }

        const toastHtml = `
            <div class="toast align-items-center text-white bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;
        
        container.innerHTML = toastHtml;
        
        const toastElement = container.querySelector('.toast');
        const toast = new bootstrap.Toast(toastElement, {
            delay: 5000
        });
        toast.show();
    }

    // Fonction d'édition globale
    window.editRoom = async function(roomId) {
        try {
            const response = await fetch(`handlers/get_room.php?id=${roomId}`);
            const data = await response.json();
            
            if (data.success) {
                const room = data.room;
                document.getElementById('roomId').value = room.id_salle;
                document.querySelector('input[name="nom_salle"]').value = room.nom_salle;
                document.querySelector('input[name="capacite"]').value = room.capacite;
                document.querySelector('textarea[name="equipements"]').value = room.equipements || '';
                document.querySelector('input[name="disponible"]').checked = room.disponible == 1;
                
                // Mettre à jour le titre du modal
                document.querySelector('.modal-title').textContent = 'Modifier la salle';
                bootstrapModal.show();
            }
        } catch (error) {
            showToast('Erreur lors du chargement de la salle', 'danger');
        }
    };

    // Fonction de suppression globale
    window.deleteRoom = async function(roomId) {
        if (!confirm('Êtes-vous sûr de vouloir supprimer cette salle ?')) {
            return;
        }

        try {
            const response = await fetch('handlers/delete_room.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id_salle: roomId })
            });

            const data = await response.json();
            
            if (data.success) {
                showToast(data.message, 'success');
                loadRooms();
            } else {
                showToast(data.message, 'danger');
            }
        } catch (error) {
            showToast('Erreur lors de la suppression', 'danger');
        }
    };

    // Reset le formulaire quand le modal est fermé
    modal.addEventListener('hidden.bs.modal', function () {
        roomForm.reset();
        document.getElementById('roomId').value = '';
        document.querySelector('.modal-title').textContent = 'Ajouter une salle';
    });
});