function editSchedule(id) {
    fetch(`handlers/get_single_schedule.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const schedule = data.schedule;
                const form = document.querySelector('#addModal form');
                
                // Mettre à jour le titre du modal
                document.querySelector('#addModal .modal-title').textContent = 'Modifier un cours';
                
                // Remplir le formulaire
                document.getElementById('scheduleId').value = schedule.id_emploi_de_temps;
                form.querySelector('select[name="id_professeur"]').value = schedule.id_professeur;
                form.querySelector('select[name="id_cours"]').value = schedule.id_cours;
                form.querySelector('select[name="jour"]').value = schedule.jour;
                form.querySelector('input[name="heure_debut"]').value = schedule.heure_debut;
                form.querySelector('input[name="heure_fin"]').value = schedule.heure_fin;
                form.querySelector('input[name="salle"]').value = schedule.salle;
                
                // Changer le texte et la valeur du bouton submit
                const submitBtn = form.querySelector('button[type="submit"]');
                submitBtn.textContent = 'Modifier';
                submitBtn.value = 'modifier';

                // Afficher le modal
                const modal = new bootstrap.Modal(document.getElementById('addModal'));
                modal.show();
            } else {
                alert('Erreur lors du chargement des données');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Erreur lors du chargement des données');
        });
}

function deleteSchedule(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cet emploi du temps ?')) {
        fetch('handlers/delete_schedule.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: id })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message || 'Erreur lors de la suppression');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Erreur lors de la suppression');
        });
    }
}
