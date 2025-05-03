document.addEventListener('DOMContentLoaded', function() {
    const newTopicModal = new bootstrap.Modal(document.getElementById('newTopicModal'));
    const submitError = document.getElementById('submitError');
    const submitButton = document.getElementById('submitTopic');

    document.querySelector('.create-topic-btn').addEventListener('click', () => {
        newTopicModal.show();
    });

    document.getElementById('newTopicForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        submitButton.disabled = true;
        submitError.classList.add('d-none');

        try {
            const formData = new FormData(e.target);
            formData.set('content', document.getElementById('topicContent').value);

            if (formData.get('title').trim().length < 5) {
                throw new Error('Le titre doit contenir au moins 5 caractères');
            }

            if (formData.get('content').trim().length < 20) {
                throw new Error('Le contenu doit contenir au moins 20 caractères');
            }

            const response = await fetch('handlers/create_topic.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                addNewTopic(data.topic);
                showToast('Sujet créé avec succès', 'success');
                newTopicModal.hide();
                e.target.reset();
                document.getElementById('topicContent').value = '';
            } else {
                throw new Error(data.message);
            }
        } catch (error) {
            submitError.textContent = error.message;
            submitError.classList.remove('d-none');
        } finally {
            submitButton.disabled = false;
        }
    });

    function addNewTopic(topic) {
        const topicsContainer = document.querySelector('.recent-topics-section');
        const initialMessage = topicsContainer.querySelector('.initial-message');
        const topicsLoadingDiv = topicsContainer.querySelector('.topics-loading');
        let topicsList = topicsContainer.querySelector('.topics-list');
        
        if (initialMessage) initialMessage.remove();
        if (topicsLoadingDiv) topicsLoadingDiv.remove();

        if (!topicsList) {
            topicsList = document.createElement('div');
            topicsList.classList.add('topics-list');
            
            const headerDiv = document.createElement('div');
            headerDiv.className = 'd-flex justify-content-between align-items-center mb-4';
            headerDiv.innerHTML = `
                <h2>${topic.nom_categorie_forum}</h2>
                <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        Trier par
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#">Plus récents</a></li>
                        <li><a class="dropdown-item" href="#">Plus actifs</a></li>
                        <li><a class="dropdown-item" href="#">Plus populaires</a></li>
                    </ul>
                </div>
            `;
            
            topicsContainer.innerHTML = '';
            topicsContainer.appendChild(headerDiv);
            topicsContainer.appendChild(topicsList);
        }

        const roleClass = topic.nom_role ? topic.nom_role.toLowerCase() : 'utilisateur';
        const roleName = topic.nom_role || 'Utilisateur';

        const topicHTML = `
            <div class="topic-item" data-topic-id="${topic.id_publication_forum}">
                <img src="/myschoolface/uploads/avatar/${topic.photo_profile || 'default.jpg'}" 
                     alt="Avatar" class="topic-avatar">
                <div class="topic-content">
                    <h3 class="topic-title">${escapeHtml(topic.titre)}</h3>
                    <p class="topic-author">
                        Par ${escapeHtml(topic.prenom)} ${escapeHtml(topic.nom)}
                        <span class="badge-role ${roleClass}">${escapeHtml(roleName)}</span>
                        • ${escapeHtml(topic.nom_categorie_forum)}
                    </p>
                    <div class="topic-stats">
                        <span class="topic-replies">
                            <i class="fas fa-comment"></i> 0 réponses
                        </span>
                        <span class="topic-last-update">
                            • À l'instant
                        </span>
                    </div>
                </div>
            </div>
        `;

        topicsList.insertAdjacentHTML('afterbegin', topicHTML);

        const categoryId = topic.id_categorie_forum;
        updateActivityCount(categoryId);
    }

    function showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.textContent = message;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.classList.add('show');
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }, 100);
    }

    async function refreshTopics() {
        try {
            const topicsLoading = document.querySelector('.topics-loading');
            if (topicsLoading) {
                topicsLoading.style.display = 'block';
            }

            const response = await fetch('handlers/get_topics.php');
            const data = await response.json();
            
            if (data.success && Array.isArray(data.topics)) {
                updateTopicsList(data.topics);
            } else {
                showToast('Erreur lors du chargement des sujets', 'error');
            }
        } catch (error) {
            showToast('Erreur lors du chargement des sujets', 'error');
        } finally {
            const topicsLoading = document.querySelector('.topics-loading');
            if (topicsLoading) {
                topicsLoading.style.display = 'none';
            }
        }
    }

    function updateTopicsList(topics, categoryTitle = null) {
        const topicsHTML = topics.map(topic => `
            <div class="topic-item" data-topic-id="${topic.id_publication_forum}">
                <img src="/myschoolface/uploads/avatar/${topic.photo_profile || 'default.jpg'}" 
                     alt="Avatar" class="topic-avatar">
                <div class="topic-content">
                    <h3 class="topic-title">${escapeHtml(topic.titre)}</h3>
                    <p class="topic-author">
                        Par ${escapeHtml(topic.prenom)} ${escapeHtml(topic.nom)}
                        <span class="badge-role ${topic.nom_role.toLowerCase()}">${escapeHtml(topic.nom_role)}</span>
                        • ${escapeHtml(topic.nom_categorie_forum)}
                    </p>
                    <div class="topic-stats">
                        <span class="topic-replies">
                            <i class="fas fa-comment"></i> ${parseInt(topic.nombre_reponses) || 0} réponses
                        </span>
                        <span class="topic-last-update">
                            • Mis à jour ${topic.date_relative}
                        </span>
                    </div>
                </div>
            </div>
        `).join('');

        const headerTitle = categoryTitle ? categoryTitle : 'Sujets Récents';
        const topicsSection = `
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>${headerTitle}</h2>
                <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        Trier par
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#">Plus récents</a></li>
                        <li><a class="dropdown-item" href="#">Plus actifs</a></li>
                        <li><a class="dropdown-item" href="#">Plus populaires</a></li>
                    </ul>
                </div>
            </div>
            ${topicsHTML}
        `;

        const topicsContainer = document.querySelector('.recent-topics-section');
        topicsContainer.innerHTML = topicsSection;
    }

    function escapeHtml(unsafe) {
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    document.querySelector('.categories-section').addEventListener('click', async (e) => {
        const categoryItem = e.target.closest('.category-item');
        if (!categoryItem) return;

        const categoryId = categoryItem.dataset.categoryId;
        const categoryTitle = categoryItem.querySelector('.category-title').textContent;
        
        document.querySelectorAll('.category-item').forEach(c => c.classList.remove('active'));
        categoryItem.classList.add('active');

        const topicsLoading = document.querySelector('.topics-loading');
        const initialMessage = document.querySelector('.initial-message');
        
        if (topicsLoading) topicsLoading.classList.remove('d-none');
        if (initialMessage) initialMessage.classList.add('d-none');

        try {
            const response = await fetch(`handlers/get_topics.php?category=${categoryId}`);
            const data = await response.json();
            
            if (data.success) {
                updateTopicsList(data.topics, categoryTitle);
            }
        } catch (error) {
            showToast('Erreur lors du chargement des sujets', 'error');
        } finally {
            if (topicsLoading) topicsLoading.classList.add('d-none');
        }
    });

    document.querySelector('.recent-topics-section').addEventListener('click', async (e) => {
        const topicItem = e.target.closest('.topic-item');
        if (!topicItem) return;

        const topicId = topicItem.dataset.topicId;
        await loadTopic(topicId);
    });

    async function updateTopicsCount(categoryId) {
        try {
            const response = await fetch(`handlers/get_topics.php?category=${categoryId}`);
            const data = await response.json();
            
            if (data.success) {
                const categoryItem = document.querySelector(`.category-item[data-category-id="${categoryId}"]`);
                if (categoryItem) {
                    const countElement = categoryItem.querySelector('.category-count');
                    countElement.textContent = `${data.total} activité${data.total > 1 ? 's' : ''}`;
                }
            }
        } catch (error) {
            console.error('Erreur lors de la mise à jour du compteur:', error);
        }
    }

    async function loadTopic(topicId) {
        try {
            const response = await fetch(`handlers/get_topic.php?id=${topicId}`);
            const data = await response.json();
            
            if (data.success) {
                displayTopic(data.topic, data.replies);
            } else {
                showToast(data.message, 'error');
            }
        } catch (error) {
            showToast('Erreur lors du chargement du sujet', 'error');
        }
    }

    function displayTopic(topic, replies) {
        const modal = new bootstrap.Modal(document.getElementById('viewTopicModal'));
        const modalEl = document.getElementById('viewTopicModal');
        const currentUserId = document.body.dataset.userId;
        const userRole = document.body.dataset.userRole;
        const topicRole = topic.nom_role;
        const canEditTime = 30;
        const isAdmin = userRole === 'admin';
        const isModerator = userRole === 'moderateur';
        const isAuthor = topic.id_utilisateur == currentUserId;
        const canModerate = isAdmin || isModerator;

        const createdDate = new Date(topic.date_creation);
        const now = new Date();
        const minutesElapsed = (now - createdDate) / 1000 / 60;
        const canEdit = canModerate || (isAuthor && minutesElapsed <= canEditTime);

        const topicActions = canEdit ? `
            <div class="topic-actions">
                <button class="btn btn-sm btn-outline-primary edit-topic-btn" data-topic-id="${topic.id_publication_forum}">
                    <i class="fas fa-edit"></i> Modifier
                </button>
                <button class="btn btn-sm btn-outline-danger delete-topic-btn" data-topic-id="${topic.id_publication_forum}">
                    <i class="fas fa-trash"></i> Supprimer
                </button>
            </div>
        ` : '';
        
        modalEl.querySelector('.topic-author-info').innerHTML = `
            <div class="d-flex justify-content-between align-items-start">
                <div class="d-flex align-items-center">
                    <img src="/myschoolface/uploads/avatar/${topic.photo_profile || 'default.jpg'}" 
                         alt="Avatar" class="topic-avatar me-3">
                    <div>
                        <div class="fw-bold">
                            ${escapeHtml(topic.prenom)} ${escapeHtml(topic.nom)}
                            <span class="badge-role ${topicRole.toLowerCase()}">${escapeHtml(topicRole)}</span>
                        </div>
                        <div class="text-muted small">Publié le ${new Date(topic.date_creation).toLocaleString()}</div>
                    </div>
                </div>
                ${topicActions}
            </div>
        `;

        modalEl.querySelector('.topic-title').textContent = topic.titre;
        modalEl.querySelector('.topic-content-text').innerHTML = topic.contenu;
        modalEl.querySelector('.replies-count').textContent = `(${replies.length})`;
        modalEl.querySelector('[name="topic_id"]').value = topic.id_publication_forum;

        const repliesList = modalEl.querySelector('.replies-list');
        repliesList.innerHTML = replies.map(reply => createReplyHTML(reply, currentUserId, userRole)).join('');

        initializeTopicActions(modalEl, topic);
        initializeReplyActions(modalEl);

        modal.show();
    }

    function createReplyHTML(reply, currentUserId, userRole) {
        const createdDate = new Date(reply.date_creation);
        const now = new Date();
        const minutesElapsed = (now - createdDate) / (1000 * 60);
        const isAdmin = userRole === 'admin';
        const isModerator = userRole === 'moderateur';
        const isAuthor = reply.id_utilisateur == currentUserId;
        const canModerate = isAdmin || isModerator;
        const canEdit = canModerate || (isAuthor && minutesElapsed <= 30);

        const replyActions = canEdit ? `
            <div class="reply-actions">
                <button class="btn btn-sm btn-link edit-reply-btn" data-reply-id="${reply.id_commentaire}">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-link text-danger delete-reply-btn" data-reply-id="${reply.id_commentaire}">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        ` : '';

        return `
            <div class="reply-item mb-3" data-reply-id="${reply.id_commentaire}">
                <div class="d-flex justify-content-between">
                    <div class="d-flex">
                        <img src="/myschoolface/uploads/avatar/${reply.photo_profile || 'default.jpg'}" 
                             alt="Avatar" class="reply-avatar me-3">
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="fw-bold">
                                        ${escapeHtml(reply.prenom)} ${escapeHtml(reply.nom)}
                                        <span class="badge-role ${(reply.nom_role || 'utilisateur').toLowerCase()}">${escapeHtml(reply.nom_role)}</span>
                                    </div>
                                    <div class="text-muted small">Le ${new Date(reply.date_creation).toLocaleString()}</div>
                                </div>
                                ${replyActions}
                            </div>
                            <div class="mt-2 reply-content" data-original-content="${escapeHtml(reply.contenu)}">${reply.contenu}</div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    function initializeTopicActions(modalEl, topic) {
        const editBtn = modalEl.querySelector('.edit-topic-btn');
        const deleteBtn = modalEl.querySelector('.delete-topic-btn');

        if (editBtn) {
            editBtn.addEventListener('click', () => handleTopicEdit(topic));
        }
        if (deleteBtn) {
            deleteBtn.addEventListener('click', () => handleTopicDelete(topic.id_publication_forum));
        }
    }

    function initializeReplyActions(modalEl) {
        const repliesList = modalEl.querySelector('.replies-list');
        const newRepliesList = repliesList.cloneNode(true);
        repliesList.parentNode.replaceChild(newRepliesList, repliesList);

        newRepliesList.addEventListener('click', (e) => {
            const editBtn = e.target.closest('.edit-reply-btn');
            const deleteBtn = e.target.closest('.delete-reply-btn');

            if (editBtn) {
                handleReplyEdit(editBtn);
            } else if (deleteBtn) {
                handleReplyDelete(deleteBtn);
            }
        });
    }

    async function handleTopicEdit(topic) {
        const { title, content: originalContent } = topic;
        const modalBody = document.querySelector('.modal-body');
        
        modalBody.innerHTML = `
            <form id="editTopicForm">
                <div class="form-group mb-3">
                    <label>Titre</label>
                    <input type="text" class="form-control" name="title" value="${escapeHtml(title)}" required>
                </div>
                <div class="form-group mb-3">
                    <label>Contenu</label>
                    <textarea id="editTopicContent" name="content" class="forum-textarea" required>${escapeHtml(originalContent)}</textarea>
                </div>
                <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-secondary cancel-edit">Annuler</button>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        `;

        modalBody.querySelector('.cancel-edit').addEventListener('click', () => {
            modalBody.innerHTML = originalContent;
        });

        modalBody.querySelector('#editTopicForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            formData.set('content', document.getElementById('editTopicContent').value);

            try {
                const response = await fetch(`handlers/edit_topic.php?id=${topic.id_publication_forum}`, {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();

                if (data.success) {
                    document.querySelector('.topic-title').textContent = formData.get('title');
                    modalBody.innerHTML = formData.get('content');
                    showToast('Sujet modifié avec succès', 'success');
                    
                    const topicItem = document.querySelector(`[data-topic-id="${topic.id_publication_forum}"]`);
                    if (topicItem) {
                        topicItem.querySelector('.topic-title').textContent = formData.get('title');
                    }
                } else {
                    throw new Error(data.message);
                }
            } catch (error) {
                showToast(error.message || 'Erreur lors de la modification', 'error');
            }
        });
    }

    async function handleTopicDelete(topicId) {
        if (confirm('Êtes-vous sûr de vouloir supprimer ce sujet ?')) {
            try {
                const response = await fetch(`handlers/delete_topic.php?id=${topicId}`, {
                    method: 'GET'
                });
                const data = await response.json();

                if (data.success) {
                    const topicModal = bootstrap.Modal.getInstance(document.getElementById('viewTopicModal'));
                    topicModal.hide();
                    
                    const topicItem = document.querySelector(`[data-topic-id="${topicId}"]`);
                    if (topicItem) {
                        topicItem.remove();
                    }
                    
                    showToast('Sujet supprimé avec succès', 'success');
                    updateActivityCount(topic.id_categorie_forum);
                } else {
                    throw new Error(data.message);
                }
            } catch (error) {
                showToast(error.message || 'Erreur lors de la suppression', 'error');
            }
        }
    }

    async function handleReplyEdit(btn) {
        const replyId = btn.dataset.replyId;
        const replyItem = document.querySelector(`.reply-item[data-reply-id="${replyId}"]`);
        const replyContent = replyItem.querySelector('.reply-content');
        const originalContent = replyContent.dataset.originalContent;

        const editForm = document.createElement('form');
        editForm.innerHTML = `
            <textarea class="forum-textarea" id="editReplyContent">${escapeHtml(originalContent)}</textarea>
            <div class="mt-2">
                <button type="submit" class="btn btn-sm btn-primary me-2">Enregistrer</button>
                <button type="button" class="btn btn-sm btn-secondary cancel-edit">Annuler</button>
            </div>
        `;

        replyContent.innerHTML = '';
        replyContent.appendChild(editForm);

        editForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const newContent = document.getElementById('editReplyContent').value.trim();
            if (newContent && newContent !== originalContent) {
                try {
                    const response = await fetch(`handlers/edit_reply.php?id=${replyId}`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ content: newContent })
                    });
                    const data = await response.json();
                    
                    if (data.success) {
                        replyContent.innerHTML = newContent;
                        replyContent.dataset.originalContent = newContent;
                        showToast('Réponse modifiée avec succès', 'success');
                    } else {
                        showToast(data.message, 'error');
                    }
                } catch (error) {
                    showToast('Erreur lors de la modification', 'error');
                }
            } else {
                replyContent.innerHTML = originalContent;
            }
        });

        replyContent.querySelector('.cancel-edit').addEventListener('click', () => {
            replyContent.innerHTML = originalContent;
        });
    }

    async function handleReplyDelete(btn) {
        const replyId = btn.dataset.replyId;
        if (btn.dataset.confirming) return;
        
        btn.dataset.confirming = 'true';
        if (confirm('Êtes-vous sûr de vouloir supprimer cette réponse ?')) {
            try {
                const response = await fetch(`handlers/delete_reply.php?id=${replyId}`, {
                    method: 'DELETE'
                });
                const data = await response.json();
                
                if (data.success) {
                    const replyItem = document.querySelector(`.reply-item[data-reply-id="${replyId}"]`);
                    if (replyItem) {
                        replyItem.remove();
                    }
                    showToast('Réponse supprimée avec succès', 'success');
                    const categoryId = document.querySelector('.category-item.active')?.dataset.categoryId;
                    if (categoryId) updateActivityCount(categoryId);
                } else {
                    showToast(data.message, 'error');
                }
            } catch (error) {
                showToast('Erreur lors de la suppression', 'error');
            }
        }
        delete btn.dataset.confirming;
    }

    document.getElementById('replyForm')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const form = e.target;
        const submitBtn = form.querySelector('button[type="submit"]');
        const topicId = form.querySelector('[name="topic_id"]').value;
        const currentUserId = document.body.dataset.userId;
        const userRole = document.body.dataset.userRole;
        const topicTitle = document.querySelector('.topic-title').textContent;
        submitBtn.disabled = true;

        try {
            const formData = new FormData(form);
            const content = form.querySelector('#replyContent').value;
            formData.set('content', content);
            const response = await fetch('handlers/add_reply.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                const repliesList = document.querySelector('.replies-list');
                repliesList.insertAdjacentHTML('beforeend', createReplyHTML(data.reply, currentUserId, userRole));
                form.reset();
                form.querySelector('#replyContent').value = '';
                
                updateReplyCounters(topicId, data.total_replies);
                
                showToast('Réponse ajoutée avec succès', 'success');
                const categoryId = document.querySelector('.category-item.active')?.dataset.categoryId;
                if (categoryId) updateActivityCount(categoryId);

                const notification = {
                    id_notification: Date.now(),
                    type: 'info',
                    message: `Nouvelle réponse dans votre sujet "${topicTitle}"`,
                    lien: `/myschoolface/forum/?topic=${topicId}`,
                };
                
                window.notificationManager.showBrowserNotification(notification);
                window.notificationManager.playNotificationSound();
            } else {
                showToast(data.message, 'error');
            }
        } catch (error) {
            showToast('Erreur lors de l\'ajout de la réponse', 'error');
        } finally {
            submitBtn.disabled = false;
        }
    });

    function updateReplyCounters(topicId, totalReplies) {
        const modalCounter = document.querySelector('.replies-count');
        if (modalCounter) {
            modalCounter.textContent = `(${totalReplies})`;
        }

        const topicItem = document.querySelector(`.topic-item[data-topic-id="${topicId}"]`);
        if (topicItem) {
            const repliesCount = topicItem.querySelector('.topic-replies');
            if (repliesCount) {
                repliesCount.innerHTML = `<i class="fas fa-comment"></i> ${totalReplies} réponses`;
            }
        }
    }

    function updateActivityCount(categoryId) {
        const categoryItem = document.querySelector(`.category-item[data-category-id="${categoryId}"]`);
        if (!categoryItem) return;

        fetch(`handlers/get_topics.php?category=${categoryId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const totalActivity = data.total_topics + data.total_replies;
                    const countElement = categoryItem.querySelector('.category-count');
                    if (countElement) {
                        countElement.textContent = `${totalActivity} activité${totalActivity > 1 ? 's' : ''}`;
                    }
                }
            })
            .catch(error => console.error('Erreur de mise à jour du compteur:', error));
    }
});
