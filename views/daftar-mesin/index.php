<?php

use app\models\DaftarMesin;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\grid\ActionColumn;

/** @var yii\web\View $this */
/** @var app\models\DaftarMesinSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Daftar Mesin';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="daftar-mesin-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Tambah Data  Mesin', ['create'], ['class' => 'btn btn-success']) ?>
        <?= Html::a('Cetak QR Mesin', ['export-pdf'], [
            'class' => 'btn btn-danger',
            'target' => '_blank'
        ]) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel'  => $searchModel,
        'tableOptions' => ['class' => 'table table-bordered table-striped table-hover'],

        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            // no mesin
            [
                'attribute' => 'no_mesin',
                'contentOptions' => ['style' => 'font-weight:600;'],
            ],

            // nama mesin
            [
                'attribute' => 'nama_mesin',
                'contentOptions' => ['style' => 'min-width:150px;'],
            ],

            // kategori
            [
                'attribute' => 'kategori',
                'filter'    => DaftarMesin::getKategoriList(),
                'content' => function($model) {
                    return Html::tag('span', $model->kategori ?: '-', [
                        'class' => 'badge bg-primary',
                    ]);
                },
                'format' => 'raw',
            ],

            // lokasi
            [
                'attribute' => 'lokasi',
                'contentOptions' => ['style' => 'min-width:120px;'],
            ],

            // status
            // status
            [
                'attribute' => 'status',
                'filter'    => DaftarMesin::getStatusList(),
                'content' => function ($model) {

                    $statusList = DaftarMesin::getStatusList();

                    $colors = [
                        'active'   => 'success',
                        'inactive' => 'secondary',
                        'maint'    => 'warning',
                    ];

                    $status = $model->status;

                    // label aman
                    $label = $statusList[$status] ?? '-';

                    // warna aman
                    $color = $colors[$status] ?? 'dark';

                    return Html::tag('span', $label, [
                        'class' => "badge bg-$color",
                    ]);
                },
                'format' => 'raw',
            ],
            // tombol generate qr
            //[
             //   'label' => 'QR Code',
               // 'content' => function($model) {
                 //   return Html::a(
                   //     'QR', 
                     //   ['/qrcode/default/create', 'id' => $model->id, 'download' => 1],
                       // ['class' => 'btn btn-outline-primary btn-sm', 'title' => 'Generate & Download QR']
                    //);
                //},
                //'format' => 'raw',
            //],

            // default action column
            [
                'class' => ActionColumn::class,
                'header' => 'Aksi',
                'template' => '{view} {update} {delete}',
                'buttons' => [
                    'view' => function ($url, $model, $key) {
                        return Html::a('<i class="fas fa-eye"></i>', $url, [
                            'title' => 'Lihat',
                            'class' => 'btn btn-sm btn-primary me-1'
                        ]);
                    },
                    'update' => function ($url, $model, $key) {
                        return Html::a('<i class="fas fa-pencil-alt"></i>', $url, [
                            'title' => 'Update',
                            'class' => 'btn btn-sm btn-warning text-white me-1'
                        ]);
                    },
                    'delete' => function ($url, $model, $key) {
                        return Html::a('<i class="fas fa-trash"></i>', $url, [
                            'title' => 'Hapus',
                            'class' => 'btn btn-sm btn-danger',
                            'data-confirm' => 'Yakin mau hapus data ini?',
                            'data-method' => 'post'
                        ]);
                    },
                ],
                'urlCreator' => function ($action, $model, $key, $index) {
                    return Url::to([$action, 'id' => $model->id]);
                },
            ],
        ],
    ]); ?>

</div>
