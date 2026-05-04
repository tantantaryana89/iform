<?php
use yii\widgets\ActiveForm;
use yii\helpers\Html;

/** @var $item app\models\ChecksheetItem */
?>

<h2>Tambah Item Checksheet</h2>

<?php $form = ActiveForm::begin(); ?>

<?= $form->field($item, 'label')->textInput() ?>

<?= $form->field($item, 'shift_json')->checkboxList([
    'shift1' => 'Shift 1',
    'shift2' => 'Shift 2',
    'shift3' => 'Shift 3',
]) ?>

<?= $form->field($item, 'waktu_json')->checkboxList([
    'awal' => 'Awal',
    'tengah' => 'Tengah',
    'akhir' => 'Akhir',
]) ?>

<div class="form-group">
    <?= Html::submitButton('Simpan Item', ['class' => 'btn btn-primary']) ?>
</div>

<?php ActiveForm::end(); ?>
