<?php
use yii\widgets\ActiveForm;
use yii\helpers\Html;

/** @var app\models\ChecksheetTemplate $model */
/** @var array $mesinList */
?>

<?php $form = ActiveForm::begin([
    'id' => 'template-meta-form',
]); ?>

<div class="alert alert-secondary">
    <strong>Alur legacy:</strong><br>
    Di builder manual ini, template langsung dikaitkan ke <b>1 mesin</b> melalui field mesin di bawah.
    Ini berbeda dengan alur baru, di mana template di-upload dulu lalu assignment ke mesin dilakukan terpisah.
</div>

<?= $form->field($model, 'name')->textInput([
    'maxlength' => true,
    'placeholder' => 'Nama template checksheet'
]) ?>

<?= $form->field($model, 'mesin_id')->dropDownList(
    $mesinList,
    ['prompt' => '-- Pilih Mesin --']
) ?>

<?= $form->field($model, 'version')->textInput([
    'placeholder' => 'Contoh: 1.0'
]) ?>

<?= $form->field($model, 'status')->dropDownList([
    'draft'  => 'Draft',
    'active' => 'Active',
]) ?>

<div class="mt-3">
    <?= Html::submitButton('Simpan Template', [
        'class' => 'btn btn-primary'
    ]) ?>
</div>

<?php ActiveForm::end(); ?>
