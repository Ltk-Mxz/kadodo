document.addEventListener('DOMContentLoaded', function() {
    const coursesTable = document.getElementById('coursesTable');
    const courseForm = document.getElementById('courseForm');
    const modal = document.getElementById('courseModal');
    const bootstrapModal = new bootstrap.Modal(modal);
    
    let isSubmitting = false;

    // Charger les listes déroulantes
    async function loadSelects() {
        try {
            // Charger les professeurs
            const profResponse = await fetch('handlers/get_professors.php');
            const profData = await profResponse.json();
            if (profData.success) {
                const profSelect = document.querySelector('select[name="id_professeur"]');
                profData.professors.forEach(prof => {
                    const option = new Option(`${prof.nom} ${prof.prenom}`, prof.id_professeur);
                    profSelect.add(option);
                });
            }

            // Charger les salles
            const roomResponse = await fetch('handlers/get_rooms.php');
            const roomData = await roomResponse.json();
            
            console.log(roomData);

            if (roomData.success) {
                const roomSelect = document.querySelector('select[name="salle"]');
                roomData.rooms.forEach(room => {
                    const option = new Option(room.nom_salle, room.nom_salle);
                    roomSelect.add(option);
                });
            }
        } catch (error) {
            console.error('Erreur lors du chargement des données:', error);
            showToast('Erreur lors du chargement des données', 'danger');
        }
    }

    // Ajouter le chargement des filières
    async function loadFilieres() {
        try {
            const response = await fetch('handlers/get_filieres.php');
            const data = await response.json();
            
            if (data.success) {
                const filiereSelect = document.querySelector('select[name="filieres[]"]');
                data.filieres.forEach(filiere => {
                    const option = new Option(filiere.libelle_filiere, filiere.id_filiere);
                    filiereSelect.add(option);
                });
            }
        } catch (error) {
            showToast('Erreur lors du chargement des filières', 'danger');
        }
    }

    // Modifier la fonction loadCourses pour améliorer l'affichage
    async function loadCourses() {
        try {
            const response = await fetch('handlers/get_courses.php');
            const data = await response.json();
            
            const tbody = document.querySelector('#coursesTable tbody');
            if (data.success) {
                tbody.innerHTML = data.courses.map(course => {
                    // Formater les dates
                    const dateDebut = course.date_debut ? new Date(course.date_debut).toLocaleDateString('fr-FR') : 'Non définie';
                    const dateFin = course.date_fin ? new Date(course.date_fin).toLocaleDateString('fr-FR') : 'Non définie';
                    
                    // Formater le nom du professeur
                    const profName = course.prof_nom ? 
                        `${course.prof_nom} ${course.prof_prenom || ''}`.trim() : 
                        'Non assigné';

                    // Formater les filières
                    const filieres = course.filieres && course.filieres.length > 0 ? 
                        course.filieres.map(f => f.libelle_filiere).join(', ') : 
                        'Aucune filière';

                    return `
                        <tr data-course-id="${course.id_cours}">
                            <td class="align-middle">${course.nom_cours || 'Non défini'}</td>
                            <td class="align-middle">${profName}</td>
                            <td class="align-middle">${course.salle || 'Non définie'}</td>
                            <td class="align-middle">${course.nombre_heures || '0'} h</td>
                            <td class="align-middle small">${filieres}</td>
                            <td class="align-middle">
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-sm btn-primary" onclick="editCourse(${course.id_cours})" title="Modifier">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger ms-1" onclick="deleteCourse(${course.id_cours})" title="Supprimer">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `;
                }).join('');
            }
        } catch (error) {
            showToast('Erreur lors du chargement des cours', 'danger');
        }
    }

    // Initialisation
    loadSelects();
    loadFilieres();
    loadCourses();
    
    // Gérer la soumission du formulaire
    document.getElementById('courseForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        if (isSubmitting) return;
        isSubmitting = true;

        try {
            const formData = new FormData(this);
            // Récupérer les filières sélectionnées
            const filieres = Array.from(document.querySelector('select[name="filieres[]"]').selectedOptions)
                                .map(option => option.value);
            formData.append('filieres', JSON.stringify(filieres));

            const response = await fetch('handlers/save_course.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Afficher le message de succès
                showToast(data.message, 'success');
                
                // Fermer le modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('courseModal'));
                modal.hide();
                
                // Réinitialiser le formulaire
                this.reset();
                
                // Recharger la liste des cours
                await loadCourses();
            } else {
                showToast(data.message, 'danger');
            }
        } catch (error) {
            console.error('Erreur:', error);
            showToast('Erreur lors de l\'enregistrement', 'danger');
        } finally {
            isSubmitting = false;
        }
    });

    // Fonction d'attribution automatique des cours
    const autoAssignBtn = document.getElementById('autoAssignBtn');
    if (autoAssignBtn) {
        autoAssignBtn.addEventListener('click', async () => {
            if (!confirm('Voulez-vous lancer l\'attribution automatique des cours aux étudiants ?')) {
                return;
            }

            try {
                const response = await fetch('handlers/auto_assign_courses.php');
                const data = await response.json();

                if (data.success) {
                    showToast(data.message, 'success');
                } else {
                    showToast(data.message, 'danger');
                }
            } catch (error) {
                console.error('Erreur:', error);
                showToast('Erreur lors de l\'attribution automatique', 'danger');
            }
        });
    }
});

// Fonction d'édition globale
window.editCourse = async function(courseId) {
    try {
        const response = await fetch(`handlers/get_course.php?id=${courseId}`);
        const data = await response.json();
        
        if (data.success) {
            const course = data.course;
            document.getElementById('courseId').value = course.id_cours;
            document.querySelector('input[name="nom_cours"]').value = course.nom_cours;
            document.querySelector('select[name="id_professeur"]').value = course.id_professeur;
            document.querySelector('select[name="salle"]').value = course.salle || '';
            document.querySelector('input[name="nombre_heures"]').value = course.nombre_heures || 2;
            
            // Sélectionner les filières du cours
            const filiereSelect = document.querySelector('select[name="filieres[]"]');
            Array.from(filiereSelect.options).forEach(option => {
                option.selected = course.filieres.some(f => f.id_filiere === parseInt(option.value));
            });
            
            // Mettre à jour le titre du modal
            document.querySelector('.modal-title').textContent = 'Modifier le cours';
            const modal = new bootstrap.Modal(document.getElementById('courseModal'));
            modal.show();
        }
    } catch (error) {
        showToast('Erreur lors du chargement du cours', 'danger');
    }
};

// Fonction de suppression globale
window.deleteCourse = async function(courseId) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer ce cours ?')) {
        return;
    }

    try {
        const response = await fetch('handlers/delete_course.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id_cours: courseId })
        });

        const data = await response.json();
        
        if (data.success) {
            // Trouver et supprimer la ligne du tableau immédiatement
            const row = document.querySelector(`tr[data-course-id="${courseId}"]`);
            if (row) {
                row.remove();
            }
            showToast(data.message, 'success');
            await loadCourses();
        } else {
            showToast(data.message, 'danger');
        }
    } catch (error) {
        console.error('Erreur:', error);
        showToast('Erreur lors de la suppression', 'danger');
    }
};

function showToast(message, type = 'success') {
    const container = document.getElementById('notifications-container');
    if (!container) return;

    const toastHtml = `
        <div class="toast align-items-center text-white bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;
    
    container.innerHTML = toastHtml;
    const toastElement = container.querySelector('.toast');
    const toast = new bootstrap.Toast(toastElement, { delay: 3000 });
    toast.show();
}
