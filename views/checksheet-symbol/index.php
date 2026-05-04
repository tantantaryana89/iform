<?php

use yii\helpers\Html;
use yii\grid\GridView;
use app\models\Symbol;

$this->title = 'Master Simbol Checksheet';
$this->params['breadcrumbs'][] = $this->title;

/* ===============================
 * HELPER
 * =============================== */
function statusBadge($status)
{
    return $status
        ? '<span class="badge bg-success">Aktif</span>'
        : '<span class="badge bg-secondary">Nonaktif</span>';
}
?>

<div class="symbol-index card shadow-sm p-3">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">
            <i class="bi bi-images me-2"></i>
            <?= Html::encode($this->title) ?>
        </h3>

        <?= Html::a(
            '<i class="bi bi-plus-circle"></i> Tambah Simbol',
            ['create'],
            ['class' => 'btn btn-success']
        ) ?>
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'tableOptions' => [
            'class' => 'table table-striped table-hover align-middle'
        ],
        'columns' => [

            ['class' => 'yii\grid\SerialColumn'],

            [
                'header' => 'Preview',
                'format' => 'raw',
                'contentOptions' => ['class' => 'text-center'],
                'value' => fn($m) =>
                    Html::img(
                        $m->image_path,
                        ['width' => 32, 'alt' => $m->code]
                    ),
            ],

            [
                'attribute' => 'code',
                'format' => 'raw',
                'value' => fn($m) =>
                    '<strong>' . Html::encode($m->code) . '</strong>',
            ],

            'name',

            [
                'attribute' => 'is_active',
                'label' => 'Status',
                'format' => 'raw',
                'contentOptions' => ['class' => 'text-center'],
                'value' => fn($m) => statusBadge($m->is_active),
                'filter' => [
                    1 => 'Aktif',
                    0 => 'Nonaktif',
                ],
            ],

            [
                'class' => 'yii\grid\ActionColumn',
                'header' => 'Aksi',
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
                'template' => '{update} {delete}',
                'buttons' => [

                    'update' => fn($url) =>
                        Html::a(
                            '<i class="fas fa-pencil-alt"></i>',
                            $url,
                            [
                                'title' => 'Update',
                                'class' => 'btn btn-sm btn-warning text-white me-1',
                            ]
                        ),

                    'delete' => fn($url, $model) =>
                        Html::a(
                            '<i class="fas fa-trash"></i>',
                            $url,
                            [
                                'title' => 'Delete',
                                'class' => 'btn btn-sm btn-danger',
                                'data' => [
                                    'method' => 'post',
                                    'confirm' =>
                                        'Hapus simbol ini?\n\n' .
                                        'Simbol yang masih dipakai oleh checksheet item tidak bisa dihapus.',
                                ],
                            ]
                        ),
                ],
            ],
        ],
    ]); ?>

</div>
