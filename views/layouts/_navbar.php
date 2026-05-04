<?php
use yii\helpers\Html;
use yii\helpers\Url;
use app\models\ChecksheetResult;

$user = Yii::$app->user->isGuest ? null : Yii::$app->user->identity;
$fullname = $user ? $user->fullname : 'Guest';

$pendingCount = 0;
$notifications = [];
$buttonLabel = 'Lihat Pending Approval';
$buttonUrl = ['/checksheet-result/pending-approval'];

if ($user) {

    // SUBFOREMAN / FOREMAN
    if (in_array($user->role, ['subforeman', 'foreman'])) {

        $pendingCount = ChecksheetResult::find()
            ->where([
                'approval_status' => 'submitted',
                'leader_notif_read' => 0
            ])
            ->count();

        $notifications = ChecksheetResult::find()
            ->where(['approval_status' => 'submitted'])
            ->orderBy(['id' => SORT_DESC])
            ->limit(5)
            ->all();
    }

    // CHIEF
    elseif ($user->role === 'chief') {

        $pendingCount = ChecksheetResult::find()
            ->where([
                'approval_status' => 'leader_approved',
                'chief_notif_read' => 0
            ])
            ->count();

        $notifications = ChecksheetResult::find()
            ->where(['approval_status' => 'leader_approved'])
            ->orderBy(['id' => SORT_DESC])
            ->limit(5)
            ->all();
    }

    // MANAGER
    elseif ($user->role === 'manager') {

        $pendingCount = ChecksheetResult::find()
            ->where([
                'approval_status' => 'chief_approved',
                'manager_notif_read' => 0
            ])
            ->count();

        $notifications = ChecksheetResult::find()
            ->where(['approval_status' => 'chief_approved'])
            ->orderBy(['id' => SORT_DESC])
            ->limit(5)
            ->all();
    }

    // ADMIN
    elseif ($user->role === 'admin') {

        $pendingCount = ChecksheetResult::find()
            ->where([
                'approval_status' => 'approved',
                'admin_notif_read' => 0
            ])
            ->count();

        $notifications = ChecksheetResult::find()
            ->where(['approval_status' => 'approved'])
            ->orderBy(['id' => SORT_DESC])
            ->limit(5)
            ->all();

        $buttonLabel = 'Lihat Approval History';
        $buttonUrl = ['/checksheet-result/approval-history'];
    }
}
?>

<nav class="navbar navbar-expand navbar-light navbar-bg">
    <a class="sidebar-toggle js-sidebar-toggle">
        <i class="hamburger align-self-center"></i>
    </a>

    <div class="navbar-collapse collapse">
        <ul class="navbar-nav ms-auto navbar-align">

            <?php if ($user): ?>

                <!-- Notification -->
                <li class="nav-item dropdown me-1">
                    <a href="#"
                       class="nav-link notification-bell d-flex align-items-center position-relative"
                       id="notificationDropdown"
                       role="button"
                       data-bs-toggle="dropdown"
                       data-bs-auto-close="outside"
                       aria-expanded="false"
                       title="Notifikasi">

                        <i class="fa-solid fa-bell"></i>

                        <?php if ($pendingCount > 0): ?>
                            <span class="notification-badge badge bg-danger rounded-pill position-absolute top-0 start-100 translate-middle">
                                <?= $pendingCount ?>
                            </span>
                        <?php endif; ?>
                    </a>

                    <?= Html::beginForm(
                        $buttonUrl,
                        'get',
                        ['class' => 'notification-mass-form']
                    ) ?>

                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 notification-dropdown"
                        aria-labelledby="notificationDropdown"
                        style="width:320px; max-height:400px; overflow-y:auto;">

                        <li class="px-3 py-3 dropdown-header bg-light border-bottom">
                            <div class="d-flex justify-content-between align-items-center">
                                <strong>Notifikasi</strong>

                                <?php if (!empty($notifications)): ?>
                                    <a href="<?= Url::to(['/checksheet-result/clear-notification']) ?>"
                                        class="btn btn-sm btn-link text-danger p-0"> Hapus semua
                                    </a>
                                <?php endif; ?>
                            </div>
                        </li>
                        <?php if (empty($notifications)): ?>
                            <li class="notification-item empty-state">
                                <div class="text-center py-4 text-muted">
                                    <i class="fa-solid fa-inbox fs-4 mb-2 d-block"></i>
                                    <small>Belum ada notifikasi</small>
                                </div>
                            </li>

                        <?php else: ?>

                            <?php foreach ($notifications as $notification): ?>
                                <li class="notification-item border-bottom">
                                    <div class="px-3 py-2">

                                        <div class="fw-semibold">
                                            Mesin:
                                            <?= Html::encode($notification->mesin) ?>
                                        </div>

                                        <div class="small text-muted">
                                            Shift:
                                            <?= Html::encode($notification->shift) ?>
                                        </div>

                                        <div class="small text-muted">
                                            <?= Yii::$app->formatter->asDatetime(
                                                $notification->submitted_at
                                            ) ?>
                                        </div>

                                        <div class="small text-primary mt-1">
                                            Status:
                                            <?= Html::encode(
                                                $notification->approval_status
                                            ) ?>
                                        </div>

                                    </div>
                                </li>
                            <?php endforeach; ?>

                            <li class="px-3 py-3 border-top">
                                <button
                                    type="submit"
                                    class="btn btn-sm btn-primary w-100">
                                    <?= $buttonLabel ?>
                                </button>
                            </li>

                        <?php endif; ?>
                    </ul>

                    <?= Html::endForm() ?>
                </li>

                <!-- User Dropdown -->
                <li class="nav-item dropdown">
                    <a href="#"
                       class="nav-link dropdown-toggle d-flex align-items-center"
                       id="navbarUserDropdown"
                       role="button"
                       data-bs-toggle="dropdown"
                       aria-expanded="false">

                        <i class="fa-solid fa-circle-user me-2 fs-5"></i>
                        <span><?= Html::encode($fullname) ?></span>
                    </a>

                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0"
                        aria-labelledby="navbarUserDropdown">

                        <li class="px-3 py-2 text-muted small">
                            Logged in as
                            <strong><?= Html::encode($fullname) ?></strong>
                        </li>

                        <li><hr class="dropdown-divider"></li>

                        <?php if ($user->role === 'admin'): ?>
                            <li>
                                <a href="<?= Url::to(['/checksheet-result/approval-history']) ?>"
                                   class="dropdown-item">
                                    <i class="fa-solid fa-history me-2"></i>
                                    Riwayat Approval
                                </a>
                            </li>

                            <li><hr class="dropdown-divider"></li>
                        <?php endif; ?>

                        <li>
                            <a href="#"
                               class="dropdown-item text-danger fw-semibold"
                               id="triggerLogoutModal">
                                <i class="fa-solid fa-right-from-bracket me-2"></i>
                                Logout
                            </a>
                        </li>

                    </ul>
                </li>

            <?php else: ?>

                <li class="nav-item">
                    <?= Html::a(
                        '<i class="fa-solid fa-right-to-bracket me-1"></i> Login',
                        ['/site/login'],
                        [
                            'class' => 'btn btn-outline-primary btn-sm'
                        ]
                    ) ?>
                </li>

            <?php endif; ?>

        </ul>
    </div>
</nav>
<script>
document.addEventListener("DOMContentLoaded", function () {

    const clearBtn = document.getElementById("clearNotificationsBtn");

    if (clearBtn) {
        clearBtn.addEventListener("click", function () {

            document.querySelectorAll(".notification-item").forEach(item => {
                item.remove();
            });

            const badge = document.querySelector(".notification-badge");

            if (badge) {
                badge.remove();
            }

            const dropdown = document.querySelector(".notification-dropdown");

            if (dropdown) {
                const emptyHtml = `
                    <li class="notification-item empty-state">
                        <div class="text-center py-4 text-muted">
                            <i class="fa-solid fa-inbox fs-4 mb-2 d-block"></i>
                            <small>Belum ada notifikasi</small>
                        </div>
                    </li>
                `;

                dropdown.insertAdjacentHTML('beforeend', emptyHtml);
            }
        });
    }

});
</script>