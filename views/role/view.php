<?php
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Role: ' . $role->name;
$this->params['breadcrumbs'][] = ['label' => 'Manajemen Role', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="role-view">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?= Html::encode($role->name) ?></h3>
            <div class="card-tools">
                <?= Html::a('Edit', ['update', 'name' => $role->name], ['class' => 'btn btn-warning btn-sm']) ?>
                <?= Html::a('Tambah Permission', ['add-permission', 'name' => $role->name], ['class' => 'btn btn-success btn-sm']) ?>
                <?= Html::a('Hapus', ['delete', 'name' => $role->name], [
                    'class' => 'btn btn-danger btn-sm',
                    'data' => ['confirm' => 'Yakin ingin menghapus role ini?'],
                ]) ?>
            </div>
        </div>
        <div class="card-body">
            <p><strong>Deskripsi:</strong> <?= Html::encode($role->description) ?></p>

            <hr>

            <h5>Permissions</h5>
            <?php if (empty($parents)): ?>
                <p class="text-muted">Tidak ada permission yang diberikan ke role ini.</p>
            <?php else: ?>
                <div class="list-group">
                    <?php foreach ($parents as $name => $permission): ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong><?= Html::encode($name) ?></strong><br>
                                <small class="text-muted"><?= Html::encode($permission->description) ?></small>
                            </div>
                            <?= Html::a('Hapus', ['remove-permission', 'name' => $role->name, 'permission' => $name], [
                                'class' => 'btn btn-danger btn-sm',
                                'data' => ['confirm' => 'Yakin?'],
                            ]) ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <hr>

            <h5>User dengan Role Ini</h5>
            <?php if (empty($users)): ?>
                <p class="text-muted">Tidak ada user dengan role ini.</p>
            <?php else: ?>
                <ul class="list-group">
                    <?php foreach ($users as $user): ?>
                        <li class="list-group-item">
                            <strong><?= Html::encode($user->username) ?></strong> (<?= Html::encode($user->fullname) ?>)
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</div>
