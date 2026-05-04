<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Login';
?>

<div class="login-wrapper">

    <div class="login-card shadow-lg p-4">
        
        <!-- Logo iForms -->
        <div class="text-center mb-4 brand-title">
            <span>i-</span><span>Forms</span>
        </div>

        <h5 class="text-center mb-4 text-white-50">Selamat Datang</h5>

        <?php $form = ActiveForm::begin([
            'options' => ['class' => 'login-form'],
        ]); ?>

        <div class="input-group mb-3">
            <span class="input-group-text"><i class="fas fa-user text-info"></i></span>
            <?= $form->field($model, 'username')
                ->textInput(['placeholder' => 'Username', 'class' => 'form-control'])
                ->label(false) ?>
        </div>

        <div class="input-group mb-3">
            <span class="input-group-text"><i class="fas fa-lock text-info"></i></span>
            <div class="password-wrapper">
                <?= $form->field($model, 'password')
                    ->passwordInput(['placeholder' => 'Password', 'class' => 'form-control', 'id' => 'password-field'])
                    ->label(false) ?>
                <button class="password-toggle" type="button" id="password-toggle">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
        </div>

        <div class="form-check mb-3 text-white">
            <?= $form->field($model, 'rememberMe')
                ->checkbox(['label' => 'Ingat saya'])
                ->label(false) ?>
        </div>

        <div class="d-grid mb-3">
            <?= Html::submitButton(
                '<i class="fas fa-right-to-bracket me-2"></i> Masuk',
                ['class' => 'btn btn-info btn-lg fw-bold shadow']
            ) ?>
        </div>

        <p class="text-center small text-white-50 mb-0">
            © <?= date('Y') ?> Assembling i-Forms
        </p>

        <?php ActiveForm::end(); ?>
    </div>
</div>
