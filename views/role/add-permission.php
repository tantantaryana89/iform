<?php
use yii\helpers\Html;

$this->title = 'Tambah Permission ke Role: ' . $role->name;
$this->params['breadcrumbs'][] = ['label' => 'Manajemen Role', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $role->name, 'url' => ['view', 'name' => $role->name]];
$this->params['breadcrumbs'][] = 'Tambah Permission';
?>

<div class="role-add-permission">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?= $this->title ?></h3>
        </div>
        <div class="card-body">
            <form method="post">
                <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
                
                <div class="mb-3">
                    <label class="form-label">Pilih Permission</label>
                    <select name="permission" class="form-control" required>
                        <option value="">-- Pilih Permission --</option>
                        <?php foreach ($allPermissions as $perm): ?>
                            <?php if (!isset($currentPermissions[$perm->name])): ?>
                                <option value="<?= Html::encode($perm->name) ?>">
                                    <?= Html::encode($perm->name) ?> - <?= Html::encode($perm->description) ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <?= Html::submitButton('Tambah', ['class' => 'btn btn-success']) ?>
                    <?= Html::a('Batal', ['view', 'name' => $role->name], ['class' => 'btn btn-secondary']) ?>
                </div>
            </form>
        </div>
    </div>
</div>
