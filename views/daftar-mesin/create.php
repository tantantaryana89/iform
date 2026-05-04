<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\DaftarMesin $model */

$this->title = 'Tambah Mesin';
$this->params['breadcrumbs'][] = ['label' => 'Daftar Mesin', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="daftar-mesin-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
