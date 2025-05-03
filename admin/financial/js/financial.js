document.addEventListener('DOMContentLoaded', function() {
    // Fonction pour afficher les notifications
    function showToast(message, type = 'success') {
        const container = document.getElementById('notifications-container');
        if (!container) return;

        const toastHtml = `
            <div class="toast align-items-center text-white bg-${type} border-0" role="alert">
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `;
        
        container.innerHTML = toastHtml;
        const toastElement = container.querySelector('.toast');
        const toast = new bootstrap.Toast(toastElement, { delay: 5000 });
        toast.show();
    }

    let allStudents = []; // Pour stocker la liste complète des étudiants
    const studentSearch = document.getElementById('studentSearch');
    const studentSelect = document.getElementById('studentSelect');

    // Fonction de recherche d'étudiants
    function filterStudents(searchTerm) {
        const filtered = allStudents.filter(student => 
            student.nom.toLowerCase().includes(searchTerm.toLowerCase()) ||
            student.prenom.toLowerCase().includes(searchTerm.toLowerCase()) ||
            student.matricule.toLowerCase().includes(searchTerm.toLowerCase())
        );

        studentSelect.innerHTML = filtered.length ? 
            filtered.map(student => `
                <option value="${student.id_utilisateur}">
                    ${student.matricule} - ${student.nom} ${student.prenom} 
                    (${student.nom_classe})
                </option>
            `).join('') :
            '<option value="">Aucun résultat</option>';
    }

    // Gestionnaire de recherche
    if (studentSearch) {
        studentSearch.addEventListener('input', (e) => {
            filterStudents(e.target.value);
        });
    }

    // Charger les statistiques
    async function loadStats() {
        try {
            const response = await fetch('handlers/get_stats.php');
            const data = await response.json();
            
            if (data.success) {
                document.getElementById('montantCollecte').textContent = 
                    `FCFA ${new Intl.NumberFormat().format(data.data.montant_collecte)}`;
                document.getElementById('montantAttente').textContent = 
                    `FCFA ${new Intl.NumberFormat().format(data.data.montant_attente)}`;
                document.getElementById('nbPaiementsAttente').textContent = 
                    `${data.data.nb_attente} paiements en attente`;
                document.getElementById('nbEtudiantsRetard').textContent = data.data.nb_retard;
            }
        } catch (error) {
            console.error('Erreur:', error);
            showToast('Erreur lors du chargement des statistiques', 'danger');
        }
    }

    // Charger la liste des étudiants
    async function loadStudents() {
        try {
            const response = await fetch('handlers/get_students.php');
            const data = await response.json();
            
            if (data.success && data.students.length > 0) {
                allStudents = data.students; // Sauvegarder la liste complète
                filterStudents(''); // Afficher tous les étudiants
            }
        } catch (error) {
            console.error('Erreur:', error);
            showToast('Erreur lors du chargement des étudiants', 'danger');
        }
    }

    // Gérer l'ajout de paiement
    const paymentForm = document.getElementById('addPaymentForm');
    if (paymentForm) {
        loadStudents(); // Charger la liste des étudiants au chargement

        paymentForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (!paymentForm.checkValidity()) {
                showToast('Veuillez remplir tous les champs requis', 'warning');
                paymentForm.classList.add('was-validated');
                return;
            }

            try {
                const formData = new FormData(paymentForm);
                const response = await fetch('handlers/add_payment.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                showToast(data.message, data.success ? 'success' : 'danger');
                
                if (data.success) {
                    paymentForm.reset();
                    paymentForm.classList.remove('was-validated');
                    loadStats();
                    loadTransactions();
                }
            } catch (error) {
                showToast('Une erreur est survenue', 'danger');
                console.error('Erreur:', error);
            }
        });

        // Formater le montant lors de la saisie
        const amountInput = paymentForm.querySelector('input[name="amount"]');
        amountInput.addEventListener('input', function() {
            if (this.value) {
                this.value = Math.max(0, Math.floor(this.value));
            }
        });
    }

    // Charger les transactions récentes
    async function loadTransactions() {
        try {
            const response = await fetch('handlers/get_transactions.php');
            const data = await response.json();
            
            if (data.success) {
                const tbody = document.querySelector('#transactionsTable tbody');
                if (!tbody) return;

                tbody.innerHTML = data.transactions.map(transaction => `
                    <tr>
                        <td class="text-muted">${transaction.matricule}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img src="/myschoolface/uploads/avatar/${transaction.photo_profile || 'default.jpg'}" 
                                    alt="${transaction.prenom} ${transaction.nom}" 
                                    class="user-avatar">
                                <span>${transaction.prenom} ${transaction.nom}</span>
                            </div>
                        </td>
                        <td>FCFA ${new Intl.NumberFormat().format(transaction.montant)}</td>
                        <td>${transaction.type_paiement}</td>
                        <td>${new Date(transaction.date_paiement).toLocaleDateString()}</td>
                        <td>
                            <span class="badge ${
                                transaction.statut_paiement === 'paye' ? 'bg-success' : 
                                transaction.statut_paiement === 'en_attente' ? 'bg-warning' : 
                                'bg-danger'
                            }">
                                ${transaction.statut_paiement}
                            </span>
                        </td>
                    </tr>
                `).join('');
            }
        } catch (error) {
            console.error('Erreur lors du chargement des transactions:', error);
            showToast('Erreur lors du chargement des transactions', 'danger');
        }
    }

    // Initialisation
    loadStats();
    loadTransactions();
});
