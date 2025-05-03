class NotificationManager {
    constructor() {
        this.container = document.getElementById('notifications-container');
        this.lastCheck = new Date().toISOString();
        this.sound = new Audio('/myschoolface/assets/sounds/notification.wav');
        this.requestNotificationPermission();
        this.init();
        this.loadInitialCount();
        this.checkNewNotifications(); // Vérifier immédiatement
        this.startPolling();
        this.windowFocused = true;
        this.initWindowFocus();
        this.headerBadge = document.querySelector('.notification-btn .notification-badge');
    }

    async requestNotificationPermission() {
        if ('Notification' in window) {
            try {
                const permission = await Notification.requestPermission();
                this.notificationsAllowed = permission === 'granted';
                
                // Sauvegarder la préférence utilisateur
                if (this.notificationsAllowed) {
                    localStorage.setItem('notifications_enabled', 'true');
                }
                
                return this.notificationsAllowed;
            } catch (error) {
                console.error('Erreur lors de la demande de permission:', error);
                return false;
            }
        }
        return false;
    }

    init() {
        this.startPolling();
        this.handleMarkAsRead();
    }

    async loadInitialCount() {
        try {
            const response = await fetch('/myschoolface/notifications/get_count.php');
            
            if (!response.ok) {
                const errorText = await response.text();
                console.error('Réponse serveur:', errorText);
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.success && typeof data.count !== 'undefined') {
                this.updateAllCounters(data.count);
                this.updateHeaderBadge(data.count);
            } else {
                throw new Error(data.error || 'Format de réponse invalide');
            }
        } catch (error) {
            console.error('Erreur détaillée:', error);
            this.updateAllCounters(0);
            this.updateHeaderBadge(0);
        }
    }

    startPolling() {
        // Vérification immédiate
        this.checkNewNotifications();
        // Puis toutes les 30 secondes
        setInterval(() => this.checkNewNotifications(), 30000);
    }

    async checkNewNotifications() {
        try {
            const response = await fetch('/myschoolface/handlers/get_notifications.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ last_check: this.lastCheck })
            });
            
            const data = await response.json();
            
            if (data.success) {
                // S'assurer que unread_count est un nombre
                const unreadCount = parseInt(data.unread_count) || 0;
                this.updateAllCounters(unreadCount);
                this.renderNotifications(data.notifications);
                
                if (data.notifications.length > 0 && !document.hasFocus()) {
                    this.showBrowserNotification(data.notifications[0]);
                }
            }
            
            this.lastCheck = new Date().toISOString();
        } catch (error) {
            console.error('Erreur lors de la vérification des notifications:', error);
            // En cas d'erreur, mettre les compteurs à 0
            this.updateAllCounters(0);
        }
    }

    updateAllCounters(count) {
        // S'assurer que count est un nombre
        count = parseInt(count) || 0;
        
        const badges = document.querySelectorAll('.notification-badge');
        badges.forEach(badge => {
            if (badge) {
                if (count > 0) {
                    badge.style.display = 'flex';
                    badge.textContent = count.toString();
                } else {
                    badge.style.display = 'none';
                }
            }
        });
        
        // Mettre à jour le badge du header
        this.updateHeaderBadge(count);
    }

    updateHeaderBadge(count) {
        // S'assurer que count est un nombre
        count = parseInt(count) || 0;
        
        if (!this.headerBadge) {
            this.headerBadge = document.querySelector('.notification-btn .notification-badge');
        }
        
        if (this.headerBadge) {
            if (count > 0) {
                this.headerBadge.style.display = 'flex';
                this.headerBadge.textContent = count.toString();
            } else {
                this.headerBadge.style.display = 'none';
            }
        }
    }

    playNotificationSound() {
        // Ne jouer le son que si l'utilisateur a interagi avec la page
        if (document.hasFocus() && this.sound) {
            this.sound.play().catch(error => {
                // Ignorer silencieusement l'erreur de lecture du son
                console.log('Son non joué :', error.message);
            });
        }
    }

    initWindowFocus() {
        window.addEventListener('focus', () => this.windowFocused = true);
        window.addEventListener('blur', () => this.windowFocused = false);
    }

    async showBrowserNotification(notification) {
        if (!this.notificationsAllowed || !('Notification' in window)) return;

        // Ne montrer la notification que si la fenêtre n'est pas active
        if (!document.hasFocus()) {
            try {
                const notif = new Notification('MySchoolFace', {
                    body: notification.message,
                    icon: '/myschoolface/assets/images/logo.png',
                    badge: '/myschoolface/assets/images/badge.png',
                    tag: `notification-${notification.id_notification}`,
                    requireInteraction: true,
                    data: notification
                });

                notif.onclick = () => {
                    window.focus();
                    if (notification.lien) {
                        window.location.href = notification.lien;
                    }
                    this.markAsRead(notification.id_notification);
                };

                // Jouer le son uniquement lors d'une interaction utilisateur
                document.addEventListener('click', () => {
                    this.playNotificationSound();
                }, { once: true });
            } catch (error) {
                console.error('Erreur lors de l\'affichage de la notification:', error);
            }
        }
    }

    handleMarkAsRead() {
        document.addEventListener('click', async (e) => {
            const notification = e.target.closest('.notification-item');
            if (!notification) return;
            
            const notificationId = notification.dataset.notificationId;
            await this.markAsRead(notificationId);
            notification.classList.add('read');
            notification.classList.remove('unread');
            
            // Mettre à jour le compteur
            const count = document.querySelectorAll('.notification-item.unread').length;
            this.updateNotificationBadge(count);
        });
    }

    async markAsRead(notificationId) {
        try {
            const response = await fetch('handlers/mark_notification_read.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ notification_id: notificationId })
            });
            const data = await response.json();
            
            if (data.success) {
                this.updateNotificationBadge(data.unread_count);
            }
        } catch (error) {
            console.error('Erreur lors du marquage de la notification:', error);
        }
    }

    async markAllAsRead() {
        try {
            const response = await fetch('/myschoolface/handlers/mark_all_read.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' }
            });

            const data = await response.json();
            if (data.success) {
                // Mise à jour du badge header
                this.updateHeaderBadge(0);
                
                // Mettre à jour tous les compteurs à 0
                const resetCounters = {
                    total: 0,
                    note: 0,
                    document: 0,
                    annonce: 0,
                    message: 0
                };
                this.updateAllCounters(resetCounters);
                
                // Mettre à jour visuellement les notifications
                const notifications = document.querySelectorAll('.notification-item');
                notifications.forEach(notif => {
                    notif.classList.remove('unread');
                    notif.classList.add('read');
                });
            }
        } catch (error) {
            console.error('Erreur lors du marquage des notifications:', error);
        }
    }

    updateNotificationBadge(count) {
        // Mettre à jour tous les badges de notification
        const badges = document.querySelectorAll('.notification-badge');
        badges.forEach(badge => {
            if (badge) {
                badge.textContent = count;
                badge.style.display = count > 0 ? 'flex' : 'none';
            }
        });
    }

    renderNotifications(notifications) {
        const container = document.getElementById('notifications-container');
        if (!container) return;

        if (!notifications || notifications.length === 0) {
            container.innerHTML = `
                <div class="empty-state">
                    <i class="bi bi-bell-slash"></i>
                    <p>Aucune notification</p>
                </div>
            `;
            return;
        }

        const html = notifications.map(notif => `
            <div class="notification-item ${!notif.lu ? 'unread' : ''}" 
                 data-notification-id="${notif.id_notification}"
                 data-type="${notif.type}">
                <div class="d-flex gap-2 align-items-center">
                    <div class="notification-icon">
                        <img src="/myschoolface/uploads/avatar/${notif.photo_profile || 'default.jpg'}" 
                             alt="Avatar" 
                             class="rounded-circle"
                             width="40" height="40">
                    </div>
                    <div class="notification-content">
                        <div class="notification-message">
                            ${notif.message}
                        </div>
                        <div class="notification-meta">
                            <small class="text-muted">${this.formatDate(notif.date_creation)}</small>
                        </div>
                    </div>
                </div>
                ${!notif.lu ? '<div class="unread-dot"></div>' : ''}
            </div>
        `).join('');

        container.innerHTML = html;
    }

    async handleNotificationClick(notificationId) {
        // Marquer comme lu
        await this.markAsRead(notificationId);
        
        // Récupérer la notification
        const notificationElement = document.querySelector(`[data-notification-id="${notificationId}"]`);
        const notification = this.getNotificationData(notificationElement);
        
        // Rediriger si un lien est présent
        if (notification && notification.lien) {
            window.location.href = notification.lien;
        }
    }

    getNotificationData(element) {
        if (!element) return null;
        return {
            id: element.dataset.notificationId,
            message: element.querySelector('.notification-message')?.textContent,
            lien: element.dataset.link
        };
    }

    getIconForType(type) {
        const icons = {
            'inscription_pending': 'bi-person-plus',
            'message': 'bi-chat-dots',
            'note': 'bi-star',
            'default': 'bi-bell'
        };
        return icons[type] || icons.default;
    }

    getIconName(type) {
        const icons = {
            'info': 'bi-info-circle',
            'success': 'bi-check-circle',
            'warning': 'bi-exclamation-triangle',
            'error': 'bi-x-circle'
        };
        return icons[type] || 'bi-bell';
    }

    formatDate(date) {
        const now = new Date();
        const notifDate = new Date(date);
        const diffTime = Math.abs(now - notifDate);
        const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
        const diffHours = Math.floor(diffTime / (1000 * 60 * 60));
        const diffMinutes = Math.floor(diffTime / (1000 * 60));

        if (diffMinutes < 1) return "À l'instant";
        if (diffMinutes < 60) return `Il y a ${diffMinutes} min`;
        if (diffHours < 24) return `Il y a ${diffHours}h`;
        if (diffDays < 7) return `Il y a ${diffDays}j`;
        
        return notifDate.toLocaleDateString();
    }
}

// Initialiser le gestionnaire de notifications
document.addEventListener('DOMContentLoaded', () => {
    window.notificationManager = new NotificationManager();
});
