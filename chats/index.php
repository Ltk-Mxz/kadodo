<?php
$pageTitle = "Messagerie";
require_once '../components/header/header.php'; ?>

<div class="container-fluid" data-user-id="<?php echo htmlspecialchars($_SESSION['user']['id']); ?>">
  <div class="row">
    <?php require_once '../components/sidebar/sidebar.php'; ?>

    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
      <div class="chat-container">
        <!-- Liste des conversations à gauche -->
        <div class="conversations-list">
          <!-- Les conversations seront injectées ici avec Js -->
        </div>

        <!-- Zone de chat à droite -->
        <div class="chat-area">
          <!-- État initial - recherche -->
          <div class="chat-initial-state">
            <div class="search-container">
              <input
                type="text"
                class="search-input"
                placeholder="Rechercher un utilisateur pour démarrer une conversation..."
                aria-label="Rechercher un utilisateur">
              <i class="bi bi-search search-icon"></i>
            </div>
          </div>

          <!-- État conversation - caché initialement -->
          <div class="chat-active-state" style="display: none;">
            <div class="chat-header">
              <button class="back-btn" onclick="returnToSearch()">
                <i class="bi bi-arrow-left"></i>
              </button>
              <div class="chat-user-info">
                <!-- Info utilisateur injecté Js -->
              </div>
              <div class="chat-actions">
                <button class="attachment-btn">
                  <i class="bi bi-paperclip"></i>
                </button>
                <button class="shared-files-btn">
                  <i class="bi bi-folder"></i>
                </button>
              </div>
            </div>

            <div class="chat-messages">
              <!-- Messages injectés avec Js -->
            </div>

            <div class="chat-input-area">
              <input type="text" class="chat-input" placeholder="Écrivez votre message...">
              <button class="send-btn">
                <i class="bi bi-send"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</div>

<script src="js/chat.js"></script>
<?php require_once '../components/footer/footer.php'; ?>