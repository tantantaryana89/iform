<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\User $model */

$this->title = 'Update User';
$this->params['breadcrumbs'][] = ['label' => 'User Management', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Update';
?>

<div class="user-update card shadow-sm p-3">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="mb-1">
                <i class="bi bi-person-gear me-2"></i>
                <?= Html::encode($model->fullname) ?>
            </h3>
            <p class="text-muted small mb-0">Username: <?= Html::encode($model->username) ?></p>
        </div>

        <?= Html::a(
            '<i class="bi bi-arrow-left"></i> Kembali',
            ['index'],
            ['class' => 'btn btn-secondary']
        ) ?>
    </div>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
