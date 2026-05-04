<?php
use yii\helpers\Html;
use yii\helpers\Url;
use app\assets\AdminKitAsset;
use app\models\FormResult;
use yii\web\YiiAsset;

// Daftarkan asset di awal (sebelum beginPage)
AdminKitAsset::register($this);
YiiAsset::register($this);

// CSS tambahan
$this->registerCssFile('@web/fontawesome/css/all.min.css');
$this->registerCssFile('@web/bootstrap-icons/bootstrap-icons.css');
$this->registerCssFile('@web/css/bootstrap.min.css');

// Hide Debug Toolbar
$this->registerCss('
    #yii-debug-toolbar,
    .yii-debug-toolbar,
    .sf-toolbar {
        display: none !important;
    }
    
    /* Notification Bell Styling */
    .notification-bell {
        color: #495057;
        transition: all 0.3s ease;
        font-size: 1.1rem;
        padding: 0.5rem 0.75rem;
        line-height: 1;
    }
    
    .notification-bell:hover {
        color: #3dd7ff;
        transform: scale(1.1);
    }
    
    .notification-badge {
        top: -5px;
        right: -8px;
        font-size: 0.65rem;
        padding: 0.25rem 0.5rem !important;
        min-width: 20px;
        text-align: center;
        animation: pulse-badge 2s infinite;
    }
    
    @keyframes pulse-badge {
        0%, 100% {
            box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7);
        }
        50% {
            box-shadow: 0 0 0 8px rgba(220, 53, 69, 0);
        }
    }
    
    .notification-dropdown {
        border-radius: 0.5rem;
    }
    
    .notification-item {
        border-bottom: 1px solid #f0f0f0;
        padding: 10px 15px;
        transition: background-color 0.2s ease;
        cursor: pointer;
    }
    
    .notification-item:hover:not(.empty-state) {
        background-color: #f8f9fa;
    }
    
    .notification-item:last-child {
        border-bottom: none;
    }
    
    .notification-item.empty-state {
        border: none;
        cursor: default;
    }
    
    .notification-item.empty-state:hover {
        background-color: transparent;
    }
    
    .notification-title {
        font-weight: 600;
        color: #212529;
        margin-bottom: 0.25rem;
        font-size: 0.95rem;
    }
    
    .notification-message {
        color: #6c757d;
        font-size: 0.875rem;
        margin-bottom: 0.25rem;
    }
    
    .notification-time {
        color: #999;
        font-size: 0.75rem;
    }
');
?>

<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php $this->registerCsrfMetaTags() ?>

    <title><?= Html::encode($this->title ?: "Dashboard") ?></title>

    <?php $this->head() ?>
</head>

<body data-theme="default">
<?php $this->beginBody() ?>

<div class="wrapper">

    <?= $this->render('_sidebar') ?>

    <div class="main">

        <?php
            $pendingApprovalNotifications = [];
            if (!Yii::$app->user->isGuest) {
                $pendingApprovalNotifications = FormResult::getPendingApprovalNotifications(Yii::$app->user->identity);
            }
        ?>
        <?= $this->render('_navbar', ['pendingApprovalNotifications' => $pendingApprovalNotifications]) ?>

        <main class="content">
            <div class="container-fluid p-0">

                <?php foreach (Yii::$app->session->getAllFlashes() as $type => $message): ?>
                    <div class="alert alert-<?= $type ?> alert-dismissible fade show" role="alert">
                        <?= $message ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endforeach; ?>

                <?= $content ?>
            </div>
        </main>

    </div>
</div>

<!-- Logout Form -->
<form id="logout-form" action="<?= Url::to(['/site/logout']) ?>" method="post" style="display:none;">
    <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">
</form>

<!-- Logout Modal -->
<div class="modal fade" id="confirmLogoutModal" tabindex="-1" aria-labelledby="confirmLogoutLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content modal-logout">
      <div class="modal-body text-center p-5">

        <i class="fas fa-exclamation-circle fa-3x text-warning mb-4"></i>

        <h5 id="confirmLogoutLabel" class="fw-bold mb-3">Yakin ingin logout?</h5>
        <p class="text-muted">Sesi akan berakhir dan kamu perlu login kembali.</p>

        <div class="d-flex justify-content-center gap-2 mt-4">
          <button class="btn btn-outline-secondary" data-bs-dismiss="modal">
            <i class="fas fa-times me-2"></i> Batal
          </button>
          <button id="confirmLogoutBtn" class="btn btn-danger">
            <i class="fas fa-sign-out-alt me-2"></i> Logout
          </button>
        </div>

      </div>
    </div>
  </div>
</div>

<?php
// Bootstrap Bundle LOCAL (JS) - load setelah YiiAsset/jQuery
$this->registerJsFile('@web/js/bootstrap.bundle.min.js', [
    'depends' => [yii\web\YiiAsset::class],
    'position' => \yii\web\View::POS_END,
]);

// Script modal logout + pastikan pakai bootstrap yang sudah ter-load
$js = <<<JS
document.addEventListener("DOMContentLoaded", function () {
    // Logout Modal
    var trigger = document.getElementById("triggerLogoutModal");
    var modalEl = document.getElementById("confirmLogoutModal");
    var logoutForm = document.getElementById("logout-form");
    var confirmBtn = document.getElementById("confirmLogoutBtn");

    if (trigger && modalEl) {
        trigger.addEventListener("click", function(e) {
            e.preventDefault();
            var instance = bootstrap.Modal.getOrCreateInstance(modalEl);
            instance.show();
        });
    }

    if (confirmBtn && logoutForm) {
        confirmBtn.addEventListener("click", function() {
            logoutForm.submit();
        });
    }

    // Notification System
    var clearBtn = document.querySelector(".clear-notifications");
    var notificationDropdown = document.querySelector(".notification-dropdown");

    if (clearBtn) {
        clearBtn.addEventListener("click", function(e) {
            e.preventDefault();
            var items = notificationDropdown.querySelectorAll(".notification-item:not(.empty-state)");
            items.forEach(function(item) {
                item.remove();
            });
            updateNotificationBadge();
            showEmptyState();
        });
    }

    // Update badge count
    function updateNotificationBadge() {
        var badge = document.querySelector(".notification-badge");
        var count = document.querySelectorAll(".notification-item:not(.empty-state)").length;
        badge.textContent = count > 0 ? count : "0";
        badge.style.display = count > 0 ? "block" : "block";
    }

    // Show empty state
    function showEmptyState() {
        var items = notificationDropdown.querySelectorAll(".notification-item:not(.empty-state)");
        var emptyState = notificationDropdown.querySelector(".notification-item.empty-state");
        if (items.length === 0 && emptyState) {
            emptyState.style.display = "block";
        }
    }

    // Initialize badge
    updateNotificationBadge();
});
JS;
$this->registerJs($js, \yii\web\View::POS_END);
?>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
