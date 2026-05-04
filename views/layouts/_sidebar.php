<?php

$templateStats = [
    'valid' => 0,
    'invalid' => 0,
];

if (!\Yii::$app->user->isGuest && \Yii::$app->authManager->getAssignment('admin', \Yii::$app->user->id)) {
    $templates = \app\models\FormTemplate::find()->all();
    foreach ($templates as $template) {
        $summary = $template->getSchemaValidationSummary();
        if ($summary['is_valid']) {
            $templateStats['valid']++;
        } else {
            $templateStats['invalid']++;
        }
    }
}
?>

<style>
/* Indent submenu items and show indicator for parents with children */
.sidebar-dropdown .sidebar-link { padding-left: 2.2rem !important; }
.has-submenu > .sidebar-link { position: relative; }
.submenu-indicator {
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%) rotate(0deg);
    transition: transform .15s ease-in-out;
    font-size: 0.9rem;
    opacity: 0.9;
}
/* when expanded the anchor loses `collapsed` class, rotate indicator */
.sidebar-link:not(.collapsed) .submenu-indicator { transform: translateY(-50%) rotate(90deg); }
</style>

<nav id="sidebar" class="sidebar js-sidebar">
    <div class="sidebar-content js-simplebar">
        <a class="sidebar-brand d-flex justify-content-center align-items-center py-3" href="#" 
            style="text-align:center;">

                <span class="align-middle" style="font-size:1.8rem; line-height:1; display:flex; gap:0;">

                    <!-- i- putih tebal -->
                    <span style="color:#fff; font-weight:900;">i-</span>

                    <!-- Form gradient -->
                    <span style="
                        font-weight:700;
                        background: linear-gradient(90deg, #00eaff, #007bff);
                        background-clip: text;
                        -webkit-background-clip: text;
                        -webkit-text-fill-color: transparent;
                    ">
                        Forms
                    </span>

                </span>
            </a>
        <ul class="sidebar-nav">

            <li class="sidebar-item active">
                <a class="sidebar-link" href="<?= \yii\helpers\Url::to(['/dashboard/index']) ?>">
                    <i class="align-middle" data-feather="sliders"></i>
                    <span class="align-middle">Dashboard</span>
                </a>
            </li>

            <li class="sidebar-item">
                <a class="sidebar-link" href="<?= \yii\helpers\Url::to(['/daftar-mesin/index']) ?>">
                    <i class="align-middle" data-feather="settings"></i>
                    <span class="align-middle">Daftar Mesin</span>
                </a>
            </li>

            <?php if (!\Yii::$app->user->isGuest && \Yii::$app->authManager->getAssignment('admin', \Yii::$app->user->id)): ?>
            <li class="sidebar-item">
                <a class="sidebar-link" href="<?= \yii\helpers\Url::to(['/user/index']) ?>">
                    <i class="align-middle" data-feather="user"></i>
                    <span class="align-middle">Users</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a class="sidebar-link" href="<?= \yii\helpers\Url::to(['/checksheet-template/index']) ?>">
                    <i data-feather="edit"></i>
                    <span class="align-middle">Form Builder (Legacy)</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a class="sidebar-link" href="<?= \yii\helpers\Url::to(['/checksheet-symbol/index']) ?>">
                    <i data-feather="image"></i>
                    <span class="align-middle">Symbol</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a class="sidebar-link" href="<?= \yii\helpers\Url::to(['/form-template/index']) ?>">
                    <i data-feather="list"></i>
                    <span class="align-middle">Form Template Aktif</span>
                    <span class="badge bg-success ms-2"><?= (int)$templateStats['valid'] ?></span>
                    <span class="badge bg-danger ms-1"><?= (int)$templateStats['invalid'] ?></span>
                </a>
            </li>
            <?php endif; ?>

            <!-- ======== FORM SUBMISSION ======== -->
            <li class="sidebar-item has-submenu">
                <a data-bs-target="#forms" data-bs-toggle="collapse" class="sidebar-link collapsed">
                    <i class="align-middle" data-feather="inbox"></i>
                    <span class="align-middle">Form Submission</span>
                        <span class="submenu-indicator">▸</span>
                </a>
                <ul id="forms" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                    <li class="sidebar-item">
                        <a class="sidebar-link ps-4" href="<?= \yii\helpers\Url::to(['/checksheet-result/index']) ?>">
                            <span class="align-middle">• Daftar Checksheet Results</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- ======== ADMINISTRASI (HANYA UNTUK ADMIN) ======== -->
            <?php if (!\Yii::$app->user->isGuest && \Yii::$app->authManager->getAssignment('admin', \Yii::$app->user->id)): ?>
            <li class="sidebar-item has-submenu">
                <a data-bs-target="#admin" data-bs-toggle="collapse" class="sidebar-link collapsed">
                    <i class="align-middle" data-feather="lock"></i>
                    <span class="align-middle">Administrasi</span>
                        <span class="submenu-indicator">▸</span>
                </a>
                <ul id="admin" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                    <li class="sidebar-item">
                        <a class="sidebar-link ps-4" href="<?= \yii\helpers\Url::to(['/site/admin-index']) ?>">
                            <span class="align-middle">• Admin Dashboard</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a class="sidebar-link ps-4" href="<?= \yii\helpers\Url::to(['/role/index']) ?>">
                            <span class="align-middle">• Manajemen Role</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a class="sidebar-link ps-4" href="<?= \yii\helpers\Url::to(['/assignment/index']) ?>">
                            <span class="align-middle">• Assign Role</span>
                        </a>
                    </li>
                </ul>
            </li>
            <?php endif; ?>
        </ul>
    </div>
</nav>