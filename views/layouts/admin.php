<?php
use yii\helpers\Url;

$this->beginPage();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Admin</title>
    <?php $this->head(); ?>

    <!-- ADMIN CSS (REAL, SESUAI FOLDER KAMU) -->
    <link rel="stylesheet" href="<?= Yii::getAlias('@web/css/admin/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= Yii::getAlias('@web/css/admin/animate.css') ?>">
    <link rel="stylesheet" href="<?= Yii::getAlias('@web/css/admin/jquery-ui.css') ?>">
    <link rel="stylesheet" href="<?= Yii::getAlias('@web/css/admin/main.css') ?>">
    <link rel="stylesheet" href="<?= Yii::getAlias('@web/css/admin/custom.css') ?>">
    <style>
        /* Hide Debug Toolbar */
        #yii-debug-toolbar,
        .yii-debug-toolbar,
        .sf-toolbar {
            display: none !important;
        }
    </style>
</head>

<body>
<?php $this->beginBody(); ?>

<div class="wrapper">

    <aside class="sidebar">
        <ul>
            <li><a href="<?= Url::to(['/dashboard/index']) ?>">Dashboard</a></li>
            <li><a href="<?= Url::to(['/site/logout']) ?>" data-method="post">Logout</a></li>
        </ul>
    </aside>

    <main class="content">
        <?= $content ?>
    </main>

</div>

<?php $this->endBody(); ?>
</body>
</html>
<?php $this->endPage(); ?>

