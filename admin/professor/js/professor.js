document.addEventListener('DOMContentLoaded', function() {
    const assignForm = document.getElementById('assignClassForm');
    const coursesTableBody = document.querySelector('#coursesTable tbody');
    const modal = document.getElementById('assignClassModal');
    const bootstrapModal = new bootstrap.Modal(modal);

    let isSubmitting = false;

    // Charger les cours
    async function loadCourses() {
        try {
            const response = await fetch('handlers/get_courses.php');
            const data = await response.json();
            
            if (data.success) {
                coursesTableBody.innerHTML = data.courses.map(course => `
                    <tr data-course-id="${course.id_cours}">
                        <td>${course.prof_nom || 'Non assigné'}</td>
                        <td>${course.nom_cours || ''}</td>
                        <td>${course.salle || ''}</td>
                        <td>${course.libelle_filiere}</td>
                        <td>${formatDate(course.date_debut)}</td>
                        <td>${formatDate(course.date_fin)}</td>
                        <td>${course.nombre_heures}h</td>
                        <td>
                            <div class="btn-group">
                                <button class="btn btn-sm btn-primary me-1" onclick="editCourse(${course.id_cours})">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteCourse(${course.id_cours})">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `).join('');
            } else {
                showToast(data.message || 'Erreur lors du chargement des cours', 'danger');
            }
        } catch (error) {
            console.error('Erreur:', error);
            showToast('Erreur lors du chargement des cours', 'danger');
        }
    }

    // Fonction pour charger les données des selects
    async function loadSelects() {
        try {
            // Charger les professeurs
            const profResponse = await fetch('handlers/get_professors.php');
            const profData = await profResponse.json();
            
            if (profData.success) {
                const profSelect = document.querySelector('select[name="professor_id"]');
                profSelect.innerHTML = '<option value="">Sélectionner un professeur</option>';
                profData.professors.forEach(prof => {
                    profSelect.innerHTML += `
                        <option value="${prof.id_professeur}">${prof.nom} ${prof.prenom}</option>
                    `;
                });
            }

            // Charger les cours
            const coursResponse = await fetch('handlers/get_subjects.php');
            const coursData = await coursResponse.json();
            
            if (coursData.success) {
                const coursSelect = document.querySelector('select[name="subject"]');
                coursSelect.innerHTML = '<option value="">Sélectionner un cours</option>';
                coursData.subjects.forEach(cours => {
                    coursSelect.innerHTML += `
                        <option value="${cours.nom_cours}">${cours.nom_cours}</option>
                    `;
                });
            }

            // Charger les salles
            const salleResponse = await fetch('handlers/get_rooms.php');
            const salleData = await salleResponse.json();

            console.log(salleData);

            if (salleData.success) {
                const salleSelect = document.querySelector('select[name="room"]');
                salleSelect.innerHTML = '<option value="">Sélectionner une salle</option>';
                salleData.rooms.forEach(salle => {
                    salleSelect.innerHTML += `
                        <option value="${salle.nom_salle}">${salle.nom_salle}</option>
                    `;
                });
            }

        } catch (error) {
            console.error('Erreur lors du chargement des données:', error);
            showToast('Erreur lors du chargement des données', 'danger');
        }
    }

    // Fonction pour charger les salles
    function loadRooms() {
        fetch('handlers/get_rooms.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const roomSelect = document.querySelector('select[name="room"]');
                    roomSelect.innerHTML = '<option value="">Sélectionner une salle</option>';
                    data.rooms.forEach(room => {
                        roomSelect.innerHTML += `
                            <option value="${room.nom_salle}">
                                ${room.nom_salle} (capacité: ${room.capacite})
                            </option>
                        `;
                    });
                }
            })
            .catch(error => console.error('Erreur:', error));
    }

    // Charger les données au chargement de la page
    loadSelects();

    // Recharger les données à l'ouverture du modal
    modal.addEventListener('show.bs.modal', function() {
        loadSelects();
        loadRooms();
    });

    // Gérer la soumission du formulaire
    if (assignForm) {
        assignForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (isSubmitting) return;
            isSubmitting = true;

            const submitBtn = assignForm.querySelector('button[type="submit"]');
            if (!submitBtn) {
                console.error('Submit button not found');
                return;
            }

            try {
                const originalText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Enregistrement...';

                const formData = new FormData(assignForm);
                const response = await fetch('handlers/save_course.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    loadCourses(); // Reload the courses table
                    showToast(data.message, 'success');
                    bootstrapModal.hide();
                    assignForm.reset();
                } else {
                    showToast(data.message || 'Erreur lors de l\'enregistrement', 'danger');
                }
            } catch (error) {
                console.error('Erreur:', error);
                showToast('Erreur lors de l\'enregistrement', 'danger');
            } finally {
                isSubmitting = false;
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Enregistrer';
                }
            }
        });
    }

    // Fonction pour éditer un cours
    window.editCourse = async function(courseId) {
        try {
            // Ouvrir le modal immédiatement pour montrer que quelque chose se passe
            document.querySelector('.modal-title').textContent = 'Modifier le cours';
            bootstrapModal.show();

            // Charger d'abord les listes déroulantes
            await loadSelects();
            
            const response = await fetch(`handlers/get_single_course.php?id=${courseId}`);
            const data = await response.json();
            
            if (data.success && data.course) {
                const course = data.course;
                
                // Remplir tous les champs du formulaire
                document.getElementById('courseId').value = course.id_cours;
                document.querySelector('select[name="professor_id"]').value = course.id_professeur || '';
                document.querySelector('select[name="subject"]').value = course.nom_cours || '';
                document.querySelector('select[name="room"]').value = course.salle || '';
                document.querySelector('select[name="filiere_id"]').value = course.id_filiere || '';
                
                // Formater les dates pour l'input date
                if (course.date_debut) {
                    const dateDebut = new Date(course.date_debut);
                    document.querySelector('input[name="date_debut"]').value = dateDebut.toISOString().split('T')[0];
                }
                if (course.date_fin) {
                    const dateFin = new Date(course.date_fin);
                    document.querySelector('input[name="date_fin"]').value = dateFin.toISOString().split('T')[0];
                }
                
                document.querySelector('input[name="nombre_heures"]').value = course.nombre_heures || 2;
            } else {
                showToast('Erreur lors du chargement du cours', 'danger');
                bootstrapModal.hide();
            }
        } catch (error) {
            console.error('Erreur:', error);
            showToast('Erreur lors du chargement du cours', 'danger');
            bootstrapModal.hide();
        }
    };

    // Fonction pour supprimer un cours avec animation
    window.deleteCourse = async function(courseId) {
        if (!confirm('Êtes-vous sûr de vouloir supprimer ce cours ? Cette action supprimera également toutes les données associées (notes, inscriptions, etc.).')) {
            return;
        }

        const row = document.querySelector(`tr[data-course-id="${courseId}"]`);
        if (!row) return;

        try {
            // Animation de début de suppression
            row.style.backgroundColor = '#ffe6e6';
            row.style.transition = 'all 0.5s ease';
            await new Promise(resolve => setTimeout(resolve, 100));

            const response = await fetch('handlers/delete_course.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id_cours: courseId })
            });

            const data = await response.json();
            
            if (data.success) {
                row.style.opacity = '0';
                row.style.transform = 'translateX(100%)';
                await new Promise(resolve => setTimeout(resolve, 500));
                row.remove();
                showToast('Cours supprimé avec succès', 'success');
            } else {
                // Restaurer l'apparence en cas d'erreur
                row.style.backgroundColor = '';
                row.style.transition = 'all 0.3s ease';
                showToast(data.message || 'Erreur lors de la suppression', 'danger');
            }
        } catch (error) {
            console.error('Erreur:', error);
            // Restaurer l'apparence en cas d'erreur
            row.style.backgroundColor = '';
            row.style.transition = 'all 0.3s ease';
            showToast('Erreur lors de la suppression', 'danger');
        }
    };

    // Formatter les dates
    function formatDate(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        return date.toLocaleDateString();
    }

    // Fonction pour afficher les toasts
    function showToast(message, type = 'success') {
        const toast = `
            <div class="toast align-items-center text-white bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `;
        
        const container = document.getElementById('notifications-container');
        if (!container) {
            console.error('Container de notifications non trouvé');
            return;
        }
        container.innerHTML = toast;
        const toastElement = container.querySelector('.toast');
        const bsToast = new bootstrap.Toast(toastElement);
        bsToast.show();
    }

    // Réinitialiser le formulaire lors de la fermeture du modal
    modal.addEventListener('hidden.bs.modal', function() {
        assignForm.reset();
        const courseIdInput = document.querySelector('input[name="course_id"]');
        if (courseIdInput) courseIdInput.value = '';
        
        // Réinitialiser le titre du modal
        const modalTitle = document.querySelector('.modal-title');
        if (modalTitle) {
            modalTitle.textContent = 'Ajouter un cours';
        }
    });

    // Fonction pour assigner une filière
    async function assignFiliere(professorId, filiereId) {
        try {
            const response = await fetch('handlers/assign_filiere.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    professor_id: professorId,
                    filiere_id: filiereId 
                })
            });

            const data = await response.json();
            
            if (data.success) {
                showToast(data.message, 'success');
                await loadCourses(); // Recharger la liste
            } else {
                throw new Error(data.message);
            }
        } catch (error) {
            console.error('Erreur:', error);
            showToast(error.message, 'danger');
        }
    }

    // Initialisation
    loadCourses();
    loadSelects();
});
