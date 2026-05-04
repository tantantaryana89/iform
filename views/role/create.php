<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Buat Role Baru';
$this->params['breadcrumbs'][] = ['label' => 'Manajemen Role', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="role-create">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?= $this->title ?></h3>
        </div>
        <div class="card-body">
            <?php if (Yii::$app->session->hasFlash('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?= Yii::$app->session->getFlash('error') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="post">
                <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
                
                <div class="mb-3">
                    <label class="form-label">Nama Role</label>
                    <input type="text" name="name" class="form-control" required placeholder="contoh: moderator">
                    <small class="form-text text-muted">Gunakan huruf kecil tanpa spasi</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Jelaskan role ini"></textarea>
                </div>

                <div>
                    <?= Html::submitButton('Buat Role', ['class' => 'btn btn-primary']) ?>
                    <?= Html::a('Batal', ['index'], ['class' => 'btn btn-secondary']) ?>
                </div>
            </form>
        </div>
    </div>
</div>
