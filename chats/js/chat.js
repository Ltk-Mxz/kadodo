let activeConversationId = null;
let lastMessageTime = null;
let refreshInterval = null;
let searchTimeout = null;
let lastCheckTime = new Date().toISOString();
let cachedAvatars = new Map();
let onlineStatuses = new Map();

document.addEventListener('DOMContentLoaded', async () => {
    const chatInput = document.querySelector('.chat-input');
    const sendBtn = document.querySelector('.send-btn');
    const messagesContainer = document.querySelector('.chat-messages');
    const newChatMobileBtn = document.querySelector('#new-chat-mobile');

    // Charger les conversations au démarrage
    await loadConversations();

    // Démarrer le polling global des conversations
    startGlobalPolling();

    // Gestionnaire d'envoi de message
    chatInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    sendBtn.addEventListener('click', sendMessage);

    // Gestionnaire de sélection de conversation
    document.querySelector('.conversations-list').addEventListener('click', (e) => {
        const conversation = e.target.closest('.conversation');
        if (conversation) {
            const conversationId = conversation.dataset.conversationId;
            switchConversation(conversationId);
        }
    });

    // Gestionnaire de recherche
    document.querySelector('.search-input').addEventListener('input', (e) => {
        const query = e.target.value.trim();
        
        clearTimeout(searchTimeout);
        
        if (query.length < 2) {
            loadConversations();
            return;
        }
        
        searchTimeout = setTimeout(() => searchUsers(query), 300);
    });

    // Gestionnaire pour le bouton nouveau message sur mobile
    if (newChatMobileBtn) {
        newChatMobileBtn.addEventListener('click', () => {
            // Cacher la liste des conversations mobile
            document.querySelector('.mobile-conversations').classList.add('d-none');
            
            // Afficher la zone de chat avec l'état initial
            const chatArea = document.querySelector('.chat-area');
            chatArea.classList.remove('d-none');
            chatArea.classList.add('d-flex');
            
            // Afficher l'état de recherche
            document.querySelector('.chat-initial-state').style.display = 'flex';
            document.querySelector('.chat-active-state').style.display = 'none';
            
            // Focus sur la recherche
            document.querySelector('.search-input').focus();
        });
    }
});

async function loadConversations() {
    try {
        const params = new URLSearchParams({
            last_update: '0', // Au chargement initial, on récupère tout
            include_unread: true
        });

        const response = await fetch(`handlers/get_messages.php?${params}`);
        const data = await response.json();
        
        if (data.success) {
            updateConversationsList(data.data);
            // Mettre à jour le lastMessageTime après le chargement initial
            if (data.data && data.data.length > 0) {
                lastMessageTime = new Date().toISOString();
            }
        }
    } catch (error) {
        console.error('Erreur lors du chargement des conversations:', error);
    }
}

function getCurrentUserId() {
    const container = document.querySelector('.container-fluid');
    const userId = container?.dataset.userId;
    if (!userId) {
        throw new Error('ID utilisateur non trouvé dans la page');
    }
    return parseInt(userId);
}

async function loadMessages(conversationId, silent = false) {
    try {
        if (!conversationId) {
            throw new Error('ID de conversation manquant');
        }

        const currentUserId = getCurrentUserId();
        const params = new URLSearchParams({
            conversation_id: conversationId,
            // Suppression de last_message_time pour toujours obtenir tous les messages
        });

        const response = await fetch(`handlers/get_messages.php?${params}`);
        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.message || 'Erreur lors du chargement des messages');
        }

        const messages = Array.isArray(data.data) ? data.data : [];
        const userIdNum = parseInt(currentUserId);
        
        const messagesHtml = messages.map(msg => {
            const expediteurId = parseInt(msg.id_expediteur);
            const isSender = expediteurId === userIdNum;
            const avatarInfo = cachedAvatars.get(expediteurId);
            
            return `
                <div class="message-wrapper ${isSender ? 'sent' : 'received'}">
                    ${!isSender && avatarInfo ? `
                        <img src="${avatarInfo.src}" 
                             alt="${avatarInfo.name}" 
                             class="message-avatar">` : ''}
                    <div class="message ${isSender ? 'sent' : 'received'}">
                        ${!isSender ? `<div class="message-sender">${msg.prenom}</div>` : ''}
                        <div class="message-bubble">
                            <p>${msg.contenu}</p>
                            <span class="message-time">${formatDate(msg.date_envoi)}</span>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
        
        document.querySelector('.chat-messages').innerHTML = messagesHtml;
        
        if (data.interlocuteur) {
            // Préserver le statut en ligne actuel pour éviter le clignotement
            const currentStatus = onlineStatuses.get(data.interlocuteur.id_utilisateur);
            const isOnline = currentStatus !== undefined ? currentStatus : Boolean(data.interlocuteur.is_online);
            
            document.querySelector('.chat-user-info').innerHTML = `
                <img src="/myschoolface/uploads/avatar/${data.interlocuteur.photo_profile || 'default.jpg'}" 
                     alt="${data.interlocuteur.prenom} ${data.interlocuteur.nom}" 
                     class="chat-avatar">
                <div>
                    <h2 class="chat-username">${data.interlocuteur.prenom} ${data.interlocuteur.nom}</h2>
                    <span class="chat-status ${isOnline ? 'online' : 'offline'}">
                        ${isOnline ? 'En ligne' : 'Hors ligne'}
                    </span>
                </div>
            `;

            // Ne mettre à jour le statut en ligne que si c'est un vrai changement
            if (onlineStatuses.get(data.interlocuteur.id_utilisateur) !== isOnline) {
                onlineStatuses.set(data.interlocuteur.id_utilisateur, isOnline);
                updateUserInterface(data.interlocuteur.id_utilisateur, isOnline);
            }
        }

        // Scroll vers le bas
        const messagesContainer = document.querySelector('.chat-messages');
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    } catch (error) {
        if (!silent) {
            console.error('Erreur détaillée:', error);
            const errorDiv = document.createElement('div');
            errorDiv.className = 'alert alert-danger';
            errorDiv.textContent = `Erreur: ${error.message}`;
            document.querySelector('.chat-messages').prepend(errorDiv);
        }
    }
}

function getRoleBadgeHtml(role) {
    const roleStyles = {
        'etudiant': {
            bg: '#e3f2fd', 
            color: '#1565c0',
            icon: 'bi-mortarboard'
        },
        'professeur': {
            bg: '#e8f5e9',
            color: '#2e7d32',
            icon: 'bi-person-workspace'
        },
        'admin': {
            bg: '#fce4ec',
            color: '#c2185b',
            icon: 'bi-shield-lock'
        },
        'moderateur': {
            bg: '#fff3e0',
            color: '#f57c00',
            icon: 'bi-shield'
        }
    };
    
    const style = roleStyles[role] || { bg: '#f5f5f5', color: '#666666', icon: 'bi-person' };
    return `
        <span class="role-badge" style="background-color: ${style.bg}; color: ${style.color}">
            <i class="bi ${style.icon} me-1"></i>
            ${role}
        </span>
    `;
}

// Iinitialisation du cache des avatars
function initAvatarCache(conversations) {
    conversations.forEach(conv => {
        if (!cachedAvatars.has(conv.id_utilisateur)) {
            cachedAvatars.set(conv.id_utilisateur, {
                src: `/myschoolface/uploads/avatar/${conv.photo_profile || 'default.jpg'}`,
                name: `${conv.prenom} ${conv.nom}`
            });
        }
    });
}

function updateConversationsList(conversations) {
    const container = document.querySelector('.conversations-list');
    const activeId = document.querySelector('.conversation.active')?.dataset.conversationId;
    
    if (!conversations || conversations.length === 0) {
        container.innerHTML = `
            <div class="empty-conversations">
                <i class="bi bi-chat-dots"></i>
                <p>Aucune conversation</p>
                <p class="text-muted">Commencez à discuter avec quelqu'un!</p>
            </div>
        `;
        return;
    }

    // Initialiser le cache des avatars
    initAvatarCache(conversations);

    // Trier les conversations par date
    conversations.sort((a, b) => {
        return new Date(b.date_dernier_message) - new Date(a.date_dernier_message);
    });

    // Conserver l'avatar existant ou utiliser celui du cache
    const getAvatarInfo = (conv) => {
        return cachedAvatars.get(conv.id_utilisateur) || {
            src: `/myschoolface/uploads/avatar/default.jpg`,
            name: `${conv.prenom} ${conv.nom}`
        };
    };

    // Mettre à jour seulement les éléments nécessaires
    conversations.forEach(conv => {
        const existingConv = document.querySelector(`[data-conversation-id="${conv.id_conversation}"]`);
        const avatarInfo = getAvatarInfo(conv);
        
        if (existingConv) {
            // Mise à jour du statut en ligne dans la conversation existante
            const statusDot = existingConv.querySelector('.status-dot') || createStatusDot(existingConv);
            const isOnline = onlineStatuses.get(conv.id_utilisateur);
            statusDot.className = `status-dot ${isOnline ? 'online' : 'offline'}`;

            // Mettre à jour les autres éléments
            const preview = existingConv.querySelector('.conversation-preview');
            const timeSpan = existingConv.querySelector('.conversation-time');
            
            if (preview) {
                preview.innerHTML = `
                    ${conv.dernier_message ? conv.dernier_message.substring(0, 50) + '...' : 'Aucun message'}
                    ${conv.non_lus > 0 ? `<span class="badge bg-primary">${conv.non_lus}</span>` : ''}
                `;
            }
            
            if (timeSpan) {
                timeSpan.textContent = formatDate(conv.date_dernier_message);
            }
        } else {
            // Créer le point de statut pour les nouvelles conversations
            const newConvHtml = `
                <div class="conversation" data-conversation-id="${conv.id_conversation}" data-user-id="${conv.id_utilisateur}">
                    <div class="avatar-container" style="position: relative;">
                        <img src="${avatarInfo.src}" 
                             alt="${avatarInfo.name}" 
                             class="conversation-avatar">
                        <span class="status-dot ${onlineStatuses.get(conv.id_utilisateur) ? 'online' : 'offline'}"></span>
                    </div>
                    <div class="conversation-info">
                        <div class="conversation-header">
                            <div>
                                <h3 class="conversation-name">${conv.prenom} ${conv.nom}</h3>
                                ${getRoleBadgeHtml(conv.nom_role)}
                            </div>
                            <span class="conversation-time">${formatDate(conv.date_dernier_message)}</span>
                        </div>
                        <p class="conversation-preview">
                            ${conv.dernier_message ? conv.dernier_message.substring(0, 50) + '...' : 'Aucun message'}
                            ${conv.non_lus > 0 ? `<span class="badge bg-primary">${conv.non_lus}</span>` : ''}
                        </p>
                    </div>
                </div>
            `;
            document.querySelector('.conversations-list').insertAdjacentHTML('beforeend', newConvHtml);
        }
    });
}

async function sendMessage() {
    if (!activeConversationId) return;

    const input = document.querySelector('.chat-input');
    const content = input.value.trim();
    
    if (!content) return;

    try {
        // Use the new function here too to ensure user ID is available
        getCurrentUserId();

        const formData = new FormData();
        const activeConversation = document.querySelector(`[data-conversation-id="${activeConversationId}"]`);
        const isNewChat = !activeConversation;
        
        if (isNewChat) {
            // Nouvelle conversation
            formData.append('destinataire_id', activeConversationId);
        } else {
            // Conversation existante
            formData.append('conversation_id', activeConversationId);
        }
        
        formData.append('contenu', content);

        const response = await fetch('handlers/send_message.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();
        
        if (!response.ok) {
            throw new Error(data.message || `Erreur ${response.status}`);
        }
        
        if (data.success) {
            input.value = '';
            if (isNewChat) {
                activeConversationId = data.conversation_id;
            }
            
            // Créer une notification pour le destinataire
            const notification = {
                id_notification: Date.now(),
                type: 'info',
                message: `Nouveau message de ${data.sender_name}`,
                lien: `/myschoolface/chats/?conv=${data.conversation_id}`,
            };
            
            window.notificationManager.showBrowserNotification(notification);
            window.notificationManager.playNotificationSound();
            
            // Mettre à jour les indicateurs pour toutes les conversations
            if (data.unread_counts) {
                updateUnreadIndicators(data.unread_counts);
            }
            
            await loadMessages(activeConversationId);
            await loadConversations();
        } else {
            throw new Error(data.message || 'Erreur lors de l\'envoi du message');
        }
    } catch (error) {
        console.error('Erreur lors de l\'envoi du message:', error);
        alert(error.message);
    }
}

function formatDate(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diff = now - date;
    
    if (diff < 24 * 60 * 60 * 1000) {
        return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }
    if (diff < 7 * 24 * 60 * 60 * 1000) {
        return date.toLocaleDateString([], { weekday: 'short' });
    }
    return date.toLocaleDateString();
}

async function switchConversation(conversationId) {
    try {
        if (!conversationId || conversationId === 'undefined' || conversationId === 'null') {
            console.warn('ID de conversation invalide:', conversationId);
            return; // Silencieusement ignorer les IDs invalides
        }

        // Retirer la classe active de toutes les conversations
        document.querySelectorAll('.conversation').forEach(conv => {
            conv.classList.remove('active');
        });

        // Ajouter la classe active à la conversation sélectionnée
        const conversationElement = document.querySelector(`[data-conversation-id="${conversationId}"]`);
        if (conversationElement) {
            conversationElement.classList.add('active');
            activeConversationId = conversationId; // Mettre à jour l'ID de conversation actif
        }

        // Basculer l'affichage
        document.querySelector('.chat-initial-state').style.display = 'none';
        document.querySelector('.chat-active-state').style.display = 'flex';
        
        // Ne pas créer un nouveau polling ici, utiliser le polling global
        if (refreshInterval) {
            clearInterval(refreshInterval);
        }

        // Ajouter l'appel pour marquer les messages comme lus
        await markMessagesAsRead(conversationId);
        
        // Charger les messages
        await loadMessages(conversationId);
        
        // Donner le focus à la zone de saisie
        document.querySelector('.chat-input').focus();
    } catch (error) {
        console.error('Erreur lors du changement de conversation:', error);
        alert(`Impossible de charger la conversation: ${error.message}`);
    }
}

async function markMessagesAsRead(conversationId) {
    try {
        const response = await fetch('handlers/mark_as_read.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ conversation_id: conversationId })
        });
        
        if (response.ok) {
            // Mettre à jour l'interface pour enlever les indicateurs
            const conversationElement = document.querySelector(`[data-conversation-id="${conversationId}"]`);
            if (conversationElement) {
                const badge = conversationElement.querySelector('.badge.bg-primary');
                if (badge) {
                    badge.remove();
                }
            }
        }
    } catch (error) {
        console.error('Erreur lors du marquage des messages comme lus:', error);
    }
}

function returnToSearch() {
    if (window.innerWidth < 768) {
        // Sur mobile, retourner à la liste des conversations
        document.querySelector('.mobile-conversations').classList.remove('d-none');
        document.querySelector('.chat-area').classList.add('d-none');
    }
    
    // État initial de la conversation
    document.querySelector('.chat-active-state').style.display = 'none';
    document.querySelector('.chat-initial-state').style.display = 'flex';
    
    // Réinitialiser la conversation active
    activeConversationId = null;
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
}

async function searchUsers(query) {
    try {
        cachedAvatars.clear(); // Vider le cache lors d'une nouvelle recherche
        const response = await fetch(`handlers/search_users.php?q=${encodeURIComponent(query)}`);
        const data = await response.json();
        
        if (data.success) {
            displaySearchResults(data.data);
        }
    } catch (error) {
        console.error('Erreur lors de la recherche:', error);
    }
}

function displaySearchResults(users) {
    const container = document.querySelector('.conversations-list');
    
    if (users.length === 0) {
        container.innerHTML = `
            <div class="empty-conversations">
                <p>Aucun utilisateur trouvé</p>
            </div>
        `;
        return;
    }

    const usersHtml = users.map(user => `
        <div class="conversation new-chat" data-user-id="${user.id_utilisateur}">
            <img src="/myschoolface/uploads/avatar/${user.photo_profile || 'default.jpg'}" 
                 alt="${user.prenom} ${user.nom}" 
                 class="conversation-avatar">
            <div class="conversation-info">
                <div class="conversation-header">
                    <h3 class="conversation-name">
                        ${user.prenom} ${user.nom}
                        ${getRoleBadgeHtml(user.nom_role)}
                    </h3>
                </div>
                <p class="conversation-preview">
                    <span class="text-muted">Cliquez pour démarrer une conversation</span>
                </p>
            </div>
        </div>
    `).join('');

    container.innerHTML = usersHtml;

    // Ajouter les gestionnaires d'événements pour les nouvelles conversations
    document.querySelectorAll('.new-chat').forEach(elem => {
        elem.addEventListener('click', () => {
            activeConversationId = elem.dataset.userId; // Mettre à jour l'ID actif
            startNewConversation(elem.dataset.userId);
        });
    });
}

async function startNewConversation(userId) {
    try {
        const formData = new FormData();
        formData.append('destinataire_id', userId);
        formData.append('contenu', 'Début de la conversation');

        const response = await fetch('handlers/send_message.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || 'Erreur lors de la création de la conversation');
        }

        if (data.success && data.conversation_id) {
            await loadConversations();
            // Mettre à jour l'ID de conversation active immédiatement
            activeConversationId = data.conversation_id;
            
            // Basculer l'affichage
            document.querySelector('.chat-initial-state').style.display = 'none';
            document.querySelector('.chat-active-state').style.display = 'flex';
            
            // Charger les messages directement
            await loadMessages(data.conversation_id);
            
            // Réinitialiser l'intervalle
            if (refreshInterval) {
                clearInterval(refreshInterval);
            }
            
            // Démarrer le rafraîchissement
            refreshInterval = setInterval(() => {
                loadMessages(data.conversation_id, true);
            }, 5000);
            
            // Focus sur l'input
            document.querySelector('.chat-input').focus();
        }
    } catch (error) {
        console.error('Erreur lors du démarrage de la conversation:', error);
        alert(error.message);
    }
}

async function checkOnlineStatus() {
    try {
        const userIds = new Set();
        document.querySelectorAll('.conversation').forEach(conv => {
            const userId = conv.dataset.userId;
            if (userId && userId !== getCurrentUserId().toString()) {
                userIds.add(userId);
            }
        });

        if (userIds.size === 0) return;

        const response = await fetch(`handlers/check_status.php?user_ids=${Array.from(userIds).join(',')}`);
        if (!response.ok) throw new Error('Erreur réseau');
        
        const data = await response.json();
        
        if (data.success) {
            console.log('Debug statuts en ligne:', data.debug); // Pour debugger
            
            // Réinitialiser tous les statuts
            onlineStatuses.clear();
            
            // Mettre à jour les statuts
            Object.entries(data.statuses).forEach(([userId, isOnline]) => {
                onlineStatuses.set(userId, Boolean(isOnline));
                updateUserInterface(userId, Boolean(isOnline));
            });
        }
    } catch (error) {
        console.error('Erreur lors de la vérification des statuts:', error);
    }
}

function updateUserInterface(userId, isOnline) {
    // S'assurer que le statut est un booléen
    isOnline = Boolean(isOnline);
    
    // Mettre à jour le cache des statuts
    onlineStatuses.set(userId, isOnline);
    
    // Mise à jour dans la liste des conversations
    const convCard = document.querySelector(`.conversation[data-user-id="${userId}"]`);
    if (convCard) {
        const statusDot = convCard.querySelector('.status-dot') || createStatusDot(convCard);
        statusDot.className = `status-dot ${isOnline ? 'online' : 'offline'}`;
    }

    // Mise à jour dans la conversation active si nécessaire
    const activeChat = document.querySelector('.chat-active-state');
    if (activeChat && activeChat.style.display !== 'none') {
        const statusText = document.querySelector('.chat-status');
        if (statusText && convCard?.classList.contains('active')) {
            statusText.textContent = isOnline ? 'En ligne' : 'Hors ligne';
            statusText.className = `chat-status ${isOnline ? 'online' : 'offline'}`;
        }
    }
}

function createStatusDot(conversation) {
    const avatarContainer = conversation.querySelector('.avatar-container');
    if (!avatarContainer) {
        // Créer le container s'il n'existe pas
        const img = conversation.querySelector('.conversation-avatar');
        const parent = img.parentElement;
        const container = document.createElement('div');
        container.className = 'avatar-container';
        container.style.position = 'relative';
        parent.replaceChild(container, img);
        container.appendChild(img);
    }

    const dot = document.createElement('span');
    dot.className = 'status-dot offline'; // Par défaut hors ligne
    avatarContainer.appendChild(dot);
    return dot;
}

function startGlobalPolling() {
    // Vérification initiale
    checkOnlineStatus();
    
    // Démarrer le polling
    return setInterval(() => {
        checkForUpdates();
        checkOnlineStatus();
    }, 10000); // Toutes les 10 secondes
}

async function checkForUpdates() {
    try {
        const currentTime = new Date().toISOString();
        const params = new URLSearchParams({
            last_check: lastCheckTime,
            current_time: currentTime
        });
        
        const response = await fetch(`handlers/check_updates.php?${params}`);
        const data = await response.json();
        
        if (data.success) {
            // Toujours mettre à jour les indicateurs non lus
            if (data.conversations) {
                updateConversationsList(data.conversations);
            }
            
            // Mettre à jour les badges
            if (data.unread_counts) {
                Object.entries(data.unread_counts).forEach(([convId, count]) => {
                    updateUnreadBadge(convId, count);
                });
            }
            
            lastCheckTime = currentTime;
        }
    } catch (error) {
        console.error('Erreur lors de la vérification des mises à jour:', error);
    }
}

function updateUnreadBadge(conversationId, count) {
    const conversation = document.querySelector(`[data-conversation-id="${conversationId}"]`);
    if (!conversation) return;

    const preview = conversation.querySelector('.conversation-preview');
    let badge = preview.querySelector('.badge.bg-primary');

    if (count > 0) {
        if (!badge) {
            badge = document.createElement('span');
            badge.className = 'badge bg-primary';
            preview.appendChild(badge);
        }
        badge.textContent = count;
    } else if (badge) {
        badge.remove();
    }
}

function updateUnreadIndicators(unreadCounts) {
    if (!unreadCounts) return;
    
    const conversations = document.querySelectorAll('.conversation');
    conversations.forEach(conv => {
        const convId = conv.dataset.conversationId;
        if (!convId || convId === activeConversationId) return;

        const count = unreadCounts[convId] || 0;
        const preview = conv.querySelector('.conversation-preview');
        if (!preview) return;

        let badge = preview.querySelector('.badge.bg-primary');
        
        if (count > 0) {
            if (!badge) {
                badge = document.createElement('span');
                badge.className = 'badge bg-primary';
                preview.appendChild(badge);
            }
            badge.textContent = count;
        } else if (badge) {
            badge.remove();
        }
    });
}