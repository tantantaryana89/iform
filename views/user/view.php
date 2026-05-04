<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use app\models\User;

/** @var yii\web\View $this */
/** @var app\models\User $model */

$this->title = $model->fullname;
$this->params['breadcrumbs'][] = ['label' => 'Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);

function badgeRole($role) {
    $colors = [
        User::ROLE_OPERATOR => 'secondary',
        User::ROLE_SUBFOREMAN => 'info',
        User::ROLE_FOREMAN => 'primary',
        User::ROLE_CHIEF => 'warning',
        User::ROLE_MANAGER => 'success',
        User::ROLE_ADMIN => 'danger',
    ];
    return '<span class="badge bg-' . ($colors[$role] ?? 'dark') . '">' . strtoupper($role) . '</span>';
}

?>
<div class="user-view card shadow-sm p-3">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0"><i class="fas fa-user-circle me-2"></i><?= Html::encode($model->fullname) ?></h2>
        <div>
            <?= Html::a('<i class="fas fa-edit"></i> Edit', ['update', 'id' => $model->id], ['class' => 'btn btn-primary me-1']) ?>
            <?= Html::a('<i class="fas fa-trash"></i> Hapus', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => 'Serius? Yakin hapus user ini?',
                    'method' => 'post',
                ],
            ]) ?>
        </div>
    </div>

    <?= DetailView::widget([
        'model' => $model,
        'options' => ['class' => 'table table-bordered table-hover'],
        'attributes' => [
            'username',
            [
                'attribute' => 'role',
                'format' => 'raw',
                'value' => badgeRole($model->role),
            ],
            [
                'attribute' => 'require_pin',
                'format' => 'raw',
                'value' => $model->require_pin ? '<span class="badge bg-success">Wajib PIN</span>' : '<span class="badge bg-secondary">Tanpa PIN</span>',
            ],
            'shift_code',
            [
                'attribute' => 'status',
                'format' => 'raw',
                'value' => $model->status ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-danger">Nonaktif</span>',
            ],
        ],
    ]) ?>

    <div class="mt-4 small text-muted">
        <i class="far fa-clock"></i> Dibuat: <?= $model->created_at ?><br>
        <i class="far fa-edit"></i> Diperbarui: <?= $model->updated_at ?>
    </div>

</div>
