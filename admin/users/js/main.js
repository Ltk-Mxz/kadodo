document.addEventListener('DOMContentLoaded', function() {
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

    function createUserRow(user) {
        if (!user) {
            console.error('Données utilisateur manquantes');
            return;
        }
        
        const tr = document.createElement('tr');
        tr.dataset.userId = user.id_utilisateur;
        
        const roleClass = getRoleClass(user.type_utilisateur);
        
        tr.innerHTML = `
            <td class="text-muted">${user.matricule}</td>
            <td>
                <div class="d-flex align-items-center gap-2">
                    <img src="/myschoolface/uploads/avatar/${user.photo_profile || 'default.jpg'}" 
                        alt="${user.prenom} ${user.nom}" 
                        class="user-avatar">
                    <span class="user-name">${user.prenom} ${user.nom}</span>
                </div>
            </td>
            <td class="user-email">${user.email}</td>
            <td><span class="role-badge ${roleClass}">${user.type_utilisateur.charAt(0).toUpperCase() + user.type_utilisateur.slice(1)}</span></td>
            <td>
                <span class="status-badge status-active">Actif</span>
            </td>
            <td>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-primary" title="Modifier" onclick="editUser(${user.id_utilisateur})">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" title="Supprimer" onclick="deleteUser(${user.id_utilisateur})">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </td>
        `;

        return tr;
    }

    ['student', 'teacher', 'admin', 'moderator'].forEach(type => {
        const form = document.getElementById(`add${type.charAt(0).toUpperCase() + type.slice(1)}Form`); // Ex: addStudentForm
        if (form) {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                
                const submitButton = form.querySelector('button[type="submit"]');
                submitButton.disabled = true;
                
                try {
                    const formData = new FormData(form);
                    const response = await fetch(form.action, {
                        method: 'POST',
                        body: formData
                    });
                    
                    const data = await response.json();
                    console.log('Réponse du serveur:', data); // Debug

                    if (!data.success) {
                        throw new Error(data.message);
                    }

                    showToast(data.message, 'success');
                    
                    if (data.user && data.user.id_utilisateur) {
                        form.reset();
                        const tbody = document.querySelector('.table tbody');
                        if (tbody) {
                            const newRow = createUserRow(data.user);
                            if (newRow) {
                                tbody.insertBefore(newRow, tbody.firstChild);
                                newRow.style.animation = 'slideIn 0.5s ease-out';
                            }
                        }
                    } else {
                        console.error('Données utilisateur invalides:', data);
                    }
                } catch (error) {
                    showToast(error.message || 'Une erreur est survenue', 'danger');
                    console.error('Erreur:', error);
                } finally {
                    submitButton.disabled = false;
                }
            });
        }
    });

    // Spécifiquement pour le formulaire modérateur
    const modForm = document.getElementById('addModForm');
    if (modForm) {
        modForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            try {
                const formData = new FormData(modForm);
                // Gérer les permissions multiples
                const permissions = Array.from(modForm.querySelector('#permissions').selectedOptions).map(opt => opt.value);
                formData.set('permissions', permissions);

                const response = await fetch(modForm.action, {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                showToast(data.message, data.success ? 'success' : 'danger');
                
                if (data.success) {
                    modForm.reset();
                    const tbody = document.querySelector('.table tbody');
                    if (tbody && data.user) {
                        const newRow = createUserRow(data.user);
                        if (newRow) {
                            tbody.insertBefore(newRow, tbody.firstChild);
                            newRow.style.animation = 'slideIn 0.3s ease-out';
                        }
                    }
                }
            } catch (error) {
                showToast('Une erreur est survenue', 'danger');
                console.error('Erreur:', error);
            }
        });
    }

    // Modifier la fonction de suppression
    async function deleteUser(userId) {
        if (!confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')) {
            return;
        }

        try {
            const response = await fetch('handlers/delete_user.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ userId })
            });

            const data = await response.json();
            showToast(data.message, data.success ? 'success' : 'danger');

            if (data.success) {
                // Supprimer la ligne du tableau sans recharger la page
                const row = document.querySelector(`tr[data-user-id="${userId}"]`);
                if (row) {
                    row.style.animation = 'fadeOut 0.5s';
                    setTimeout(() => {
                        row.remove();
                        updateUserCount(); // Mise à jour du compteur après la suppression
                    }, 500);
                }
            }
        } catch (error) {
            showToast('Une erreur est survenue', 'danger');
            console.error('Erreur:', error);
        }
    }

    // Fonction pour mettre à jour le compteur d'utilisateurs
    function updateUserCount(total = null) {
        const tbody = document.querySelector('.table tbody');
        const countDisplay = document.querySelector('.user-count');
        
        if (!tbody || !countDisplay) return;
        
        const displayedRows = tbody.querySelectorAll('tr').length;
        const start = displayedRows > 0 ? 1 : 0;
        const totalCount = total || displayedRows;
        
        countDisplay.textContent = `Affichage de ${start}-${displayedRows} sur ${totalCount} utilisateurs`;
    }

    // Fonction de modification
    async function editUser(userId) {
        try {
            const response = await fetch(`handlers/get_user.php?id=${userId}`);
            const data = await response.json();
            
            if (data.success) {
                const modal = new bootstrap.Modal(document.getElementById('editUserModal'));
                const form = document.getElementById('editUserForm');
                const specificFields = document.getElementById('edit_specific_fields');
                
                // Remplir les champs communs
                form.querySelector('#edit_user_id').value = userId;
                form.querySelector('#edit_user_type').value = data.user.type_utilisateur;
                form.querySelector('#edit_firstname').value = data.user.prenom;
                form.querySelector('#edit_lastname').value = data.user.nom;
                form.querySelector('#edit_email').value = data.user.email;

                // Afficher la photo actuelle
                const currentPhoto = document.querySelector('#current_photo img');
                currentPhoto.src = `/myschoolface/uploads/avatar/${data.user.photo_profile}`;
                currentPhoto.classList.remove('d-none');
                
                specificFields.innerHTML = ''; // Nettoyer les champs précédents
                
                switch (data.user.type_utilisateur) {
                    case 'ROLE_ETUDIANT':
                        specificFields.innerHTML = `
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">Filière</label>
                                    <select class="form-select" name="filiere" required>
                                        ${data.filieres.map(filiere => `
                                            <option value="${filiere.id_filiere}" ${data.user.id_filiere == filiere.id_filiere ? 'selected' : ''}>
                                                ${filiere.libelle_filiere}
                                            </option>
                                        `).join('')}
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Niveau</label>
                                    <select class="form-select" name="niveau" required>
                                        ${data.niveaux.map(niveau => `
                                            <option value="${niveau.id_niveau}" ${data.user.id_niveau == niveau.id_niveau ? 'selected' : ''}>
                                                ${niveau.libelle_niveau}
                                            </option>
                                        `).join('')}
                                    </select>
                                </div>
                            </div>
                        `;
                        break;
                    case 'ROLE_PROFESSEUR':
                        // ...
                        break;
                    case 'ROLE_ADMIN':
                        // ...
                        break;
                    case 'ROLE_MODERATEUR':
                        // ...
                        break;
                }
                
                modal.show();
            }
        } catch (error) {
            showToast('Erreur lors de la récupération des données', 'danger');
            console.error('Erreur:', error);
        }
    }

    // Ajout du gestionnaire de soumission pour le formulaire d'édition
    const editForm = document.getElementById('editUserForm');
    if (editForm) {
        editForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            try {
                const formData = new FormData(editForm);
                const response = await fetch(editForm.action, {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                showToast(data.message, data.success ? 'success' : 'danger');
                
                if (data.success) {
                    const userId = formData.get('user_id');
                    const row = document.querySelector(`tr[data-user-id="${userId}"]`);
                    
                    if (row) {
                        row.querySelector('.user-name').textContent = 
                            `${formData.get('firstname')} ${formData.get('lastname')}`;
                        row.querySelector('.user-email').textContent = formData.get('email');
                        
                        // Mettre à jour immédiatement la photo si elle a été modifiée
                        if (data.photo) {
                            const timestamp = new Date().getTime();
                            const avatars = row.querySelectorAll('.user-avatar');
                            const photoUrl = `/myschoolface/uploads/avatar/${data.photo}?t=${timestamp}`;
                            
                            avatars.forEach(avatar => {
                                avatar.src = photoUrl;
                            });

                            // Mettre aussi à jour la photo dans le dropdown du header si c'est le même utilisateur
                            const headerAvatars = document.querySelectorAll('.header .user-avatar');
                            headerAvatars.forEach(avatar => {
                                avatar.src = photoUrl;
                            });
                        }
                    }

                    const modal = bootstrap.Modal.getInstance(document.getElementById('editUserModal'));
                    modal.hide();
                }
            } catch (error) {
                showToast('Une erreur est survenue', 'danger');
                console.error('Erreur:', error);
            }
        });
    }

    // Fonction pour changer de page
    function changePage(page) {
        const url = new URL(window.location);
        url.searchParams.set('page', page);
        window.location = url;
    }

    // Fonction pour mettre à jour la pagination
    function updatePagination() {
        const urlParams = new URLSearchParams(window.location.search);
        const currentPage = parseInt(urlParams.get('page')) || 1;
        
        document.querySelectorAll('.pagination .page-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const href = this.getAttribute('href');
                const page = new URLSearchParams(href.split('?')[1]).get('page');
                changePage(page);
            });
        });
    }

    // Initialiser la pagination
    updatePagination();

    window.changePage = changePage;

    window.editUser = editUser;
    window.deleteUser = deleteUser;

    // Recherche en direct
    const searchInput = document.querySelector('input.search-input');
    const roleSelect = document.querySelector('#roleFilter');
    const statusSelect = document.querySelector('#statusFilter');
    let searchTimeout = null;

    function getRoleClass(role) {
        const roleMapping = {
            'etudiant': 'bg-success bg-opacity-10 text-success',
            'professeur': 'bg-primary bg-opacity-10 text-primary',
            'admin': 'bg-danger bg-opacity-10 text-danger',
            'moderateur': 'bg-warning bg-opacity-10 text-warning'
        };
        return roleMapping[role.toLowerCase()] || '';
    }

    async function performSearch() {
        if (!searchInput || !roleSelect || !statusSelect) {
            console.error('Elements de recherche non trouvés');
            return;
        }

        const searchTerm = searchInput.value.trim();
        const roleValue = roleSelect.value;
        const statusValue = statusSelect.value;

        console.log('Envoi des filtres:', {
            searchTerm,
            roleValue,
            statusValue
        });

        try {
            const params = new URLSearchParams();
            
            // Ajout des paramètres seulement s'ils ont une valeur
            if (searchTerm) params.append('q', searchTerm);
            if (roleValue) params.append('role', roleValue);
            if (statusValue) params.append('status', statusValue);

            const response = await fetch('handlers/search_users.php?' + params.toString(), {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            });
            
            if (!response.ok) {
                const errorData = await response.text();
                console.error('Réponse du serveur:', errorData);
                throw new Error('Erreur lors de la recherche');
            }
            
            const data = await response.json();
            
            if (data.success) {
                updateTable(data.users, data.total);
            } else {
                throw new Error(data.message || 'Erreur lors de la recherche');
            }
        } catch (error) {
            console.error('Erreur de recherche:', error);
            showToast(error.message, 'danger');
        }
    }

    function updateTable(users, totalUsers) {
        const tbody = document.querySelector('.table tbody');
        if (!tbody || !Array.isArray(users)) return;

        tbody.innerHTML = users.map(user => {
            const roleClass = getRoleClass(user.nom_role);
            return `
                <tr data-user-id="${user.id_utilisateur}">
                    <td class="text-muted">${user.matricule || ''}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <img src="/myschoolface/uploads/avatar/${user.photo_profile || 'default.jpg'}" 
                                alt="${user.prenom || ''} ${user.nom || ''}" 
                                class="user-avatar">
                            <span class="user-name">${user.prenom || ''} ${user.nom || ''}</span>
                        </div>
                    </td>
                    <td class="user-email">${user.email || ''}</td>
                    <td><span class="role-badge ${roleClass}">${ucfirst(user.nom_role || '')}</span></td>
                    <td>
                        <span class="status-badge ${user.statut_compte === 'actif' ? 'status-active' : 'status-inactive'}">
                            ${ucfirst(user.statut_compte || 'inactif')}
                        </span>
                    </td>
                    <td>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-primary" title="Modifier" onclick="editUser(${user.id_utilisateur})">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" title="Supprimer" onclick="deleteUser(${user.id_utilisateur})">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
        updateUserCount(totalUsers);
    }

    // Fonction utilitaire pour la première lettre en majuscule
    function ucfirst(str) {
        return str ? str.charAt(0).toUpperCase() + str.slice(1) : '';
    }

    // Initialisation des écouteurs d'événements pour la recherche
    if (searchInput) {
        searchInput.addEventListener('input', () => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(performSearch, 300);
        });
    }

    if (roleSelect) {
        roleSelect.addEventListener('change', performSearch);
    }

    if (statusSelect) {
        statusSelect.addEventListener('change', performSearch);
    }
});
