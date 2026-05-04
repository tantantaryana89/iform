<?php
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Manajemen Role';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="role-index">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Daftar Role</h3>
            <?= Html::a('+ Tambah Role', ['create'], ['class' => 'btn btn-primary btn-sm']) ?>
        </div>
        <div class="card-body">
            <?php if (Yii::$app->session->hasFlash('success')): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?= Yii::$app->session->getFlash('success') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (empty($roles)): ?>
                <p class="text-muted">Tidak ada role.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th width="40%">Nama Role</th>
                                <th width="40%">Deskripsi</th>
                                <th width="20%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($roles as $role): ?>
                                <tr>
                                    <td><strong><?= Html::encode($role->name) ?></strong></td>
                                    <td><?= Html::encode($role->description) ?></td>
                                    <td class="text-center">
                                        <?= Html::a('Lihat', ['view', 'name' => $role->name], ['class' => 'btn btn-info btn-xs']) ?>
                                        <?= Html::a('Edit', ['update', 'name' => $role->name], ['class' => 'btn btn-warning btn-xs']) ?>
                                        <?= Html::a('Hapus', ['delete', 'name' => $role->name], [
                                            'class' => 'btn btn-danger btn-xs',
                                            'data' => ['confirm' => 'Yakin ingin menghapus role ini?'],
                                        ]) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
