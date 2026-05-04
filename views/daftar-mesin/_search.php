<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\DaftarMesinSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="daftar-mesin-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'no_mesin') ?>

    <?= $form->field($model, 'nama_mesin') ?>

    <?= $form->field($model, 'kategori') ?>

    <?= $form->field($model, 'lokasi') ?>

    <?php // echo $form->field($model, 'status') ?>

    <?php // echo $form->field($model, 'vendor') ?>

    <?php // echo $form->field($model, 'serial_number') ?>

    <?php // echo $form->field($model, 'tgl_last_maintenance') ?>

    <?php // echo $form->field($model, 'next_maintenance_due') ?>

    <?php // echo $form->field($model, 'qr_code_path') ?>

    <?php // echo $form->field($model, 'created_at') ?>

    <?php // echo $form->field($model, 'updated_at') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
