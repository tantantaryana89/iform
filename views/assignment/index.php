<?php
use yii\helpers\Html;

$this->title = 'Assign Role ke User';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="assignment-index">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?= $this->title ?></h3>
        </div>
        <div class="card-body">
            <?php if (Yii::$app->session->hasFlash('success')): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?= Yii::$app->session->getFlash('success') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (empty($users)): ?>
                <p class="text-muted">Tidak ada user.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th width="25%">Username</th>
                                <th width="35%">Nama Lengkap</th>
                                <th width="25%">Role</th>
                                <th width="15%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?= Html::encode($user->username) ?></td>
                                    <td><?= Html::encode($user->fullname) ?></td>
                                    <td>
                                        <?php if (empty($userRoles[$user->id])): ?>
                                            <span class="badge bg-secondary">Tidak ada role</span>
                                        <?php else: ?>
                                            <?php foreach ($userRoles[$user->id] as $role): ?>
                                                <span class="badge bg-primary"><?= Html::encode($role) ?></span>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?= Html::a('Assign', ['assign', 'userId' => $user->id], ['class' => 'btn btn-info btn-xs']) ?>
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
