<?php

use yii\helpers\Html;
use yii\grid\GridView;
use app\models\User;

$this->title = 'User Management';
$this->params['breadcrumbs'][] = $this->title;

function roleBadge($role) {
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

function statusBadge($status) {
    return $status
        ? '<span class="badge bg-success">Aktif</span>'
        : '<span class="badge bg-danger">Nonaktif</span>';
}

?>
<div class="user-index card shadow-sm p-3">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">
            <i class="bi bi-people-fill me-2"></i>
            <?= Html::encode($this->title) ?>
        </h3>
        <?= Html::a('<i class="bi bi-person-plus-fill"></i> Tambah User', ['create'], ['class' => 'btn btn-success']) ?>
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'tableOptions' => ['class' => 'table table-striped table-hover align-middle'],
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'attribute' => 'fullname',
                'format' => 'raw',
                'value' => fn($m) => '<strong>' . $m->fullname . '</strong>',
            ],
            'username',
            [
                'attribute' => 'role',
                'format' => 'raw',
                'value' => fn($m) => roleBadge($m->role),
                'filter' => User::optsRole()
            ],
            [
                'attribute' => 'status',
                'format' => 'raw',
                'value' => fn($m) => statusBadge($m->status),
                'filter' => ['1' => 'Aktif', '0' => 'Nonaktif']
            ],

            [
                'class' => 'yii\grid\ActionColumn',
                'header' => 'Aksi',
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
                'template' => '{view} {update} {delete} {pin}',
                'buttons' => [

                    'view' => fn($url) =>
                        Html::a('<i class="fas fa-eye"></i>', $url, [
                            'title' => 'Detail',
                            'class' => 'btn btn-sm btn-primary me-1'
                        ]),

                    'update' => fn($url) =>
                        Html::a('<i class="fas fa-pencil-alt"></i>', $url, [
                            'title' => 'Update',
                            'class' => 'btn btn-sm btn-warning text-white me-1'
                        ]),

                    'delete' => fn($url) =>
                        Html::a('<i class="fas fa-trash"></i>', $url, [
                            'title' => 'Delete',
                            'class' => 'btn btn-sm btn-danger me-1',
                            'data' => [
                                'method' => 'post',
                                'confirm' => 'Hapus user ini?',
                            ],
                        ]),

                    'pin' => function ($url, $model) {
                        if (Yii::$app->user->id == $model->id) return '';

                        return Html::a('<i class="fas fa-key"></i>', ['manage-pin', 'id' => $model->id], [
                            'class' => 'btn btn-sm btn-info text-white me-1',
                            'title' => 'Kelola PIN',
                        ]);
                    },
                ],
            ],
        ],
    ]); ?>

</div>
