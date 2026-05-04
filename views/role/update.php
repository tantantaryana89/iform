<?php
use yii\helpers\Html;

$this->title = 'Edit Role: ' . $role->name;
$this->params['breadcrumbs'][] = ['label' => 'Manajemen Role', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $role->name, 'url' => ['view', 'name' => $role->name]];
$this->params['breadcrumbs'][] = 'Edit';
?>

<div class="role-update">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?= $this->title ?></h3>
        </div>
        <div class="card-body">
            <form method="post">
                <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
                
                <div class="mb-3">
                    <label class="form-label">Nama Role</label>
                    <input type="text" class="form-control" disabled value="<?= Html::encode($role->name) ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" class="form-control" rows="3"><?= Html::encode($role->description) ?></textarea>
                </div>

                <div>
                    <?= Html::submitButton('Simpan', ['class' => 'btn btn-primary']) ?>
                    <?= Html::a('Batal', ['view', 'name' => $role->name], ['class' => 'btn btn-secondary']) ?>
                </div>
            </form>
        </div>
    </div>
</div>
