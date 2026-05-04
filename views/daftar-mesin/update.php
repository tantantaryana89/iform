<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\DaftarMesin $model */

$this->title = 'Update Mesin: ' . $model->no_mesin;
$this->params['breadcrumbs'][] = ['label' => 'Daftar Mesin', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->no_mesin, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="daftar-mesin-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
