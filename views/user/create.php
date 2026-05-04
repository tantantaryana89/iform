<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\User $model */

$this->title = 'Tambah User';
$this->params['breadcrumbs'][] = ['label' => 'User Management', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="user-create card shadow-sm p-3">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">
            <i class="bi bi-person-plus-fill me-2"></i>
            <?= Html::encode($this->title) ?>
        </h3>

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
