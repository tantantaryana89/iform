<?php
use yii\helpers\Html;
use app\assets\AdminKitAsset;
use yii\web\YiiAsset;

// Asset
AdminKitAsset::register($this);
YiiAsset::register($this);

// FontAwesome Local
$this->registerCssFile('@web/fontawesome/css/all.min.css');

// Bootstrap Icons
$this->registerCssFile('@web/bootstrap-icons/bootstrap-icons.css');

// Bootstrap CSS
$this->registerCssFile('@web/css/bootstrap.min.css');
?>

<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title ?: 'Login i-Forms') ?></title>

    <?php $this->head() ?>
    <style>
        body {
        font-family: "Inter", sans-serif;
        background: #0b1e33;
    }

    .login-wrapper {
        height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        background:
            radial-gradient(circle at 50% 20%,
                rgba(80, 185, 255, 0.65) 0%,
                rgba(30, 64, 98, 0.95) 55%,
                rgba(10, 24, 46, 1) 100%
            );
        background-color: #0b1e33;
    }

    .login-card {
        width: 400px;
        background: rgba(10, 24, 46, 0.35);
        border-radius: 18px;
        border: 1px solid rgba(77, 210, 255, 0.28);
        backdrop-filter: blur(20px);
        box-shadow: 0 8px 32px rgba(77, 210, 255, 0.15),
                    inset 0 0 30px rgba(77, 210, 255, 0.05),
                    0 0 20px rgba(77, 210, 255, 0.1);
    }

    /* Remove autocomplete white background */
    input:-webkit-autofill,
    input:-webkit-autofill:hover,
    input:-webkit-autofill:focus,
    input:-webkit-autofill:active {
        -webkit-box-shadow: 0 0 0 1000px transparent inset !important;
        -webkit-text-fill-color: #dceaff !important;
        transition: background-color 5000s ease-in-out 0s;
    }

    /* Branding */
    .brand-title span:first-child {
        font-size: 2.5rem;
        font-weight: 900;
        color: #e9f6ff;
    }
    .brand-title span:last-child {
        font-size: 2.5rem;
        font-weight: 700;
        background: linear-gradient(90deg, #4ad2ff, #007bff);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    /* INPUT GROUP */
    .input-group {
        border: 1px solid rgba(150, 175, 200, 0.22);
        border-radius: 10px;
        background: transparent;
    }

    .input-group-text {
        border: none;
        background: transparent;
        color: #53c9ff;
    }

    .form-control {
        background: transparent !important;
        border: none !important;
        color: #dceaff !important;
        padding: 10px;
        caret-color: #4ad2ff;
    }
    .form-control::placeholder {
        color: rgba(255,255,255,0.45);
    }
    .form-control:focus {
        background: transparent !important;
        color: #fff !important;
        box-shadow: none !important;
        border: none !important;
    }

    /* Focus Glow Subtle */
    .input-group:focus-within {
        border-color: rgba(70, 180, 255, 0.9);
        box-shadow: 0 0 6px rgba(70,180,255,0.45);
    }

    /* PASSWORD TOGGLE BUTTON */
    .password-wrapper {
        position: relative;
        flex: 1;
        display: flex;
        align-items: center;
    }

    .password-wrapper .field {
        flex: 1;
        margin: 0;
    }

    .password-wrapper #password-field {
        padding-right: 45px;
        width: 100%;
    }

    .password-toggle {
        position: absolute;
        right: 10px;
        border: none !important;
        background: transparent !important;
        padding: 8px 10px;
        color: #53c9ff !important;
        cursor: pointer;
        font-size: 1rem;
        z-index: 10;
    }

    .password-toggle:hover {
        color: #4ad2ff !important;
    }

    /* BUTTON */
    .btn-info {
        background: linear-gradient(90deg, #3dd7ff, #007bff);
        border: none;
        font-weight: 600;
    }
    .btn-info:hover {
        filter: brightness(1.07);
    }
    /* Hide Debug Toolbar */
    #yii-debug-toolbar,
    .yii-debug-toolbar,
    .sf-toolbar {
        display: none !important;
    }
    </style>
</head>

<body>
<?php $this->beginBody() ?>

<?= $content ?>

<?php
$this->registerJsFile('@web/js/bootstrap.bundle.min.js', [
    'depends' => [yii\web\YiiAsset::class],
    'position' => \yii\web\View::POS_END,
]);
?>

<script>
    // Password visibility toggle
    document.getElementById('password-toggle')?.addEventListener('click', function(e) {
        e.preventDefault();
        const passwordField = document.getElementById('password-field');
        const toggle = this.querySelector('i');
        
        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            toggle.classList.remove('fa-eye');
            toggle.classList.add('fa-eye-slash');
        } else {
            passwordField.type = 'password';
            toggle.classList.remove('fa-eye-slash');
            toggle.classList.add('fa-eye');
        }
    });
</script>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
