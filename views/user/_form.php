<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\User $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="user-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->errorSummary($model, ['class' => 'alert alert-danger']); ?>

    <?= $form->field($model, 'username')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'fullname')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'password')
        ->passwordInput(['maxlength' => true])
        ->hint('Kosongkan jika tidak mengubah password. Untuk create, minimal 6 karakter.') ?>

    <?= $form->field($model, 'role')->dropDownList([
        'operator' => 'Operator',
        'subforeman' => 'Sub Foreman',
        'foreman' => 'Foreman',
        'chief' => 'Chief',
        'manager' => 'Manager',
        'admin' => 'Admin',
    ], ['prompt' => '- Pilih Role -']) ?>

    <?= $form->field($model, 'shift_code')->textInput(['maxlength' => true]) ?>

    <hr>

    <h5 class="mt-3">
        <i class="bi bi-key-fill me-1"></i> Pengaturan PIN Approval
    </h5>

    <div class="mb-2 form-check">
        <input class="form-check-input" type="radio" name="pin_type"
               id="pin-generate" value="generate" checked>
        <label class="form-check-label" for="pin-generate">
            Generate PIN otomatis (6 digit)
        </label>
    </div>

    <div class="mb-2 form-check">
        <input class="form-check-input" type="radio" name="pin_type"
               id="pin-manual" value="manual">
        <label class="form-check-label" for="pin-manual">
            Input PIN manual
        </label>
    </div>

    <div class="mb-3" id="manual-pin-box" style="display:none;">
        <?= Html::activeLabel($model, 'pin', ['class' => 'form-label']) ?>
        <?= Html::activeTextInput($model, 'pin', [
            'class' => 'form-control',
            'placeholder' => 'Masukkan PIN (4–6 digit)',
        ]) ?>
    </div>

    <?= $form->field($model, 'status')->checkbox(['label' => 'Aktif']) ?>

    <div class="form-group mt-3">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>
    <script>
        (function () {
            var radios = document.getElementsByName('pin_type');
            var box = document.getElementById('manual-pin-box');

            if (!radios || radios.length === 0 || !box) {
                return;
            }

            function togglePinBox() {
                for (var i = 0; i < radios.length; i++) {
                    if (radios[i].checked) {
                        box.style.display = (radios[i].value === 'manual') ? 'block' : 'none';
                        break;
                    }
                }
            }

            for (var i = 0; i < radios.length; i++) {
                radios[i].addEventListener('change', togglePinBox);
            }

            togglePinBox();
        })();
    </script>


</div>

