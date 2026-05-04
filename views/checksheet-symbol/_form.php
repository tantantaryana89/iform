<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>

<?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

<?= $form->field($model, 'code')->textInput(['maxlength' => true, 'disabled' => !$model->isNewRecord]) ?>

<?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

<?= $form->field($model, 'description')->textarea(['rows' => 3]) ?>

<?= $form->field($model, 'imageFile')->fileInput() ?>

<?php if (!$model->isNewRecord && $model->image_path): ?>
    <p>
        <strong>Preview:</strong><br>
        <img src="<?= $model->image_path ?>" width="48">
    </p>
<?php endif ?>

<?= $form->field($model, 'is_active')->checkbox() ?>

<div class="form-group">
    <?= Html::submitButton('Simpan', ['class' => 'btn btn-success']) ?>
</div>

<?php ActiveForm::end(); ?>
