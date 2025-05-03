<?php
require_once '../config/constants.php';
$pageTitle = "Mes Notifications";
require_once '../components/header/header.php';
require_once '../components/notifications/notification.php';

// Récupérer les notifications de l'utilisateur
$result = Notification::getUnread($_SESSION['user']['id'], 50);
$notifications = $result['notifications'];

// Récupérer les compteurs
$counters = Notification::getUnreadCount($_SESSION['user']['id']);
?>

<div class="container-fluid p-0">
  <div class="row g-0">
    <!-- Sidebar -->
    <?php require_once '../components/sidebar/sidebar.php'; ?>

    <!-- Main content -->
    <main class="col-md-9 col-lg-10 ms-sm-auto px-2 px-md-4">
      <div class="notifications-container">
        <!-- Filters section -->
        <div class="filters-section d-none d-md-block">
          <h2 class="filters-title">Filtres</h2>
          <div class="filter-list">
            <div class="filter-item active" data-filter="all">
              <i class="bi bi-inbox"></i>
              <span>Toutes les notifications</span>
              <span class="count"><?= $counters['total'] ?? 0 ?></span>
            </div>
            <div class="filter-item" data-filter="note">
              <i class="bi bi-star"></i>
              <span>Notes</span>
              <span class="count"><?= $counters['note'] ?? 0 ?></span>
            </div>
            <div class="filter-item" data-filter="document">
              <i class="bi bi-file-earmark-text"></i>
              <span>Documents</span>
              <span class="count"><?= $counters['document'] ?? 0 ?></span>
            </div>
            <div class="filter-item" data-filter="annonce">
              <i class="bi bi-megaphone"></i>
              <span>Annonces</span>
              <span class="count"><?= $counters['annonce'] ?? 0 ?></span>
            </div>
          </div>
        </div>

        <!-- Mobile Filters -->
        <div class="d-md-none mb-3">
          <div class="dropdown w-100">
            <button class="btn btn-secondary dropdown-toggle w-100" type="button" data-bs-toggle="dropdown">
              <i class="bi bi-filter"></i> Filtrer
            </button>
            <ul class="dropdown-menu w-100">
              <li><a class="dropdown-item active" href="#" data-filter="all">Toutes les notifications</a></li>
              <li><a class="dropdown-item" href="#" data-filter="note">Notes</a></li>
              <li><a class="dropdown-item" href="#" data-filter="document">Documents</a></li>
              <li><a class="dropdown-item" href="#" data-filter="annonce">Annonces</a></li>
            </ul>
          </div>
        </div>

        <!-- Notifications section -->
        <div class="notifications-section">
          <div class="notifications-header">
            <h1 class="notifications-title h4">Notifications</h1>
            <button class="btn btn-outline-secondary btn-sm" id="markAllRead">
              <i class="bi bi-check2-all"></i>
              <span class="d-none d-sm-inline">Tout marquer comme lu</span>
            </button>
          </div>

          <div class="notification-list">
            <?php if (empty($notifications)): ?>
              <div class="empty-state">
                <i class="bi bi-bell-slash"></i>
                <p>Aucune notification</p>
              </div>
            <?php else: ?>
              <?php foreach ($notifications as $notif): ?>
                <div class="notification-item <?= !$notif['lu'] ? 'unread' : '' ?>"
                  data-notification-id="<?= htmlspecialchars($notif['id_notification']) ?>"
                  data-type="<?= htmlspecialchars($notif['type']) ?>">
                  <div class="notification-icon">
                    <?php
                    $photoPath = '../uploads/avatar/' . ($notif['photo_profile'] ?? 'default.jpg');
                    $defaultPath = '../assets/images/default-avatar.jpg';

                    if (!empty($notif['photo_profile']) && file_exists($photoPath)) {
                      $imagePath = $photoPath;
                      $className = '';
                    } else {
                      $imagePath = $defaultPath;
                      $className = 'default-avatar';
                    }
                    ?>
                    <img src="<?= htmlspecialchars($imagePath) ?>"
                      alt="Avatar"
                      class="<?= $className ?>"
                      onerror="this.src='<?= htmlspecialchars($defaultPath) ?>'"
                      width="40" height="40">
                  </div>
                  <div class="notification-content">
                    <?php if ($notif['type'] === 'inscription_pending'): ?>
                      <div class="notification-header">
                        <strong>Nouvelle inscription</strong>
                      </div>
                    <?php endif; ?>
                    <div class="notification-message"><?= htmlspecialchars($notif['message']) ?></div>
                    <div class="notification-meta">
                      <?php if ($notif['id_expediteur']): ?>
                        <span class="notification-sender">
                          <?= htmlspecialchars($notif['prenom'] . ' ' . $notif['nom']) ?>
                        </span>
                      <?php endif; ?>
                      <span class="notification-time">
                        <?= formatNotificationDate($notif['date_creation']) ?>
                      </span>
                    </div>
                  </div>
                  <?php if (!$notif['lu']): ?>
                    <div class="unread-dot"></div>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </main>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const filterItems = document.querySelectorAll('.filter-item');
    const notificationItems = document.querySelectorAll('.notification-item');
    const markAllReadBtn = document.getElementById('markAllRead');

    // Filtrage
    filterItems.forEach(item => {
      item.addEventListener('click', function() {
        const filter = this.dataset.filter;

        filterItems.forEach(fi => fi.classList.remove('active'));
        this.classList.add('active');

        notificationItems.forEach(ni => {
          if (filter === 'all' || ni.dataset.type === filter) {
            ni.style.display = 'flex';
          } else {
            ni.style.display = 'none';
          }
        });
      });
    });

    // Marquer une notification comme lue
    document.querySelectorAll('.notification-item').forEach(item => {
      item.addEventListener('click', async function() {
        const notificationId = this.dataset.notificationId;
        try {
          const response = await fetch('/myschoolface/handlers/mark_notification_read.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({
              notification_id: notificationId
            })
          });

          const data = await response.json();
          if (data.success) {
            this.classList.remove('unread');

            // Mettre à jour le compteur du filtre actif
            const activeFilter = document.querySelector('.filter-item.active');
            const counter = activeFilter.querySelector('.count');
            const currentCount = parseInt(counter.textContent);
            const newCount = Math.max(0, currentCount - 1);
            counter.textContent = newCount;

            // Mise à jour du badge dans le header sans le cacher
            const headerBadges = document.querySelectorAll('.notification-btn .notification-badge');
            headerBadges.forEach(badge => {
              badge.textContent = newCount;
            });
          }
        } catch (error) {
          console.error('Erreur:', error);
        }
      });
    });

    // Marquer tout comme lu
    markAllReadBtn.addEventListener('click', async function() {
      try {
        const response = await fetch('/myschoolface/handlers/mark_all_read.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          }
        });

        const data = await response.json();
        if (data.success) {
          document.querySelectorAll('.notification-item.unread').forEach(item => {
            item.classList.remove('unread');
          });

          // Mettre à jour tous les compteurs à 0
          document.querySelectorAll('.filter-item .count').forEach(counter => {
            counter.textContent = '0';
          });

          // Mettre à jour les badges à 0 sans les cacher
          document.querySelectorAll('.notification-btn .notification-badge').forEach(badge => {
            badge.textContent = '0';
          });
        }
      } catch (error) {
        console.error('Erreur:', error);
      }
    });
  });
</script>

<?php
function getNotificationIcon($type)
{
  return match ($type) {
    'note' => 'bi-star',
    'document' => 'bi-file-earmark-text',
    'annonce' => 'bi-megaphone',
    'message' => 'bi-chat-dots',
    default => 'bi-bell'
  };
}

function formatNotificationDate($date)
{
  $date = new DateTime($date);
  $now = new DateTime();
  $diff = $now->diff($date);

  if ($diff->d == 0) {
    if ($diff->h == 0) {
      if ($diff->i == 0) {
        return "À l'instant";
      }
      return "Il y a {$diff->i} minute" . ($diff->i > 1 ? 's' : '');
    }
    return "Il y a {$diff->h} heure" . ($diff->h > 1 ? 's' : '');
  }
  if ($diff->d <= 7) {
    return "Il y a {$diff->d} jour" . ($diff->d > 1 ? 's' : '');
  }
  return $date->format('d/m/Y');
}

require_once '../components/footer/footer.php';
?>