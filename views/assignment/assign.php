<?php
use yii\helpers\Html;

$this->title = 'Assign Role ke: ' . $user->username;
$this->params['breadcrumbs'][] = ['label' => 'Assign Role', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="assignment-assign">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?= $this->title ?></h3>
        </div>
        <div class="card-body">
            <p><strong>Nama:</strong> <?= Html::encode($user->fullname) ?></p>
            <p><strong>Username:</strong> <?= Html::encode($user->username) ?></p>

            <hr>

            <form method="post">
                <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
                
                <div class="mb-3">
                    <label class="form-label">Pilih Role (dapat lebih dari satu)</label>
                    <div class="checkbox-group">
                        <?php foreach ($roles as $role): ?>
                            <div class="form-check">
                                <input 
                                    type="checkbox" 
                                    name="roles[]" 
                                    value="<?= Html::encode($role->name) ?>"
                                    id="role_<?= Html::encode($role->name) ?>"
                                    class="form-check-input"
                                    <?= in_array($role->name, $userRoles) ? 'checked' : '' ?>
                                >
                                <label class="form-check-label" for="role_<?= Html::encode($role->name) ?>">
                                    <strong><?= Html::encode($role->name) ?></strong>
                                    <span class="text-muted"><?= Html::encode($role->description) ?></span>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div>
                    <?= Html::submitButton('Simpan', ['class' => 'btn btn-primary']) ?>
                    <?= Html::a('Batal', ['index'], ['class' => 'btn btn-secondary']) ?>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.checkbox-group {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
}
.form-check {
    margin-bottom: 10px;
    padding: 10px;
    background: white;
    border-left: 3px solid #007bff;
}
.form-check-label {
    display: block;
    cursor: pointer;
    margin-bottom: 0;
}
.form-check input[type="checkbox"] {
    margin-right: 8px;
}
</style>
