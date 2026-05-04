<?php

use yii\helpers\Html;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var app\models\FormTemplateSearch $searchModel */

$this->title = 'Form Template Management';
?>

<h3><?= Html::encode($this->title) ?></h3>

<div class="alert alert-secondary">
    <small>
        <strong>ℹ️ Sebagai reminder:</strong> Template ini adalah <b>generic form blueprint</b> 
        yang tidak terikat mesin spesifik. Assignment template ke mesin dilakukan di halaman 
        <b>Daftar Mesin</b>, bukan di sini.
        <br>
        Data daftar di halaman ini diambil langsung dari tabel <b>form_template</b> pada database yang sedang aktif.
    </small>
</div>

<p>
    <?= Html::a('Upload Template', ['create'], ['class' => 'btn btn-success']) ?>
</p>

<div class="card shadow-sm">
    <div class="card-body p-0">

        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel'  => $searchModel,

            'tableOptions' => ['class' => 'table table-bordered table-hover align-middle mb-0'],
            'headerRowOptions' => ['class' => 'table-light text-center'],
            'rowOptions' => function () {
                return ['style' => 'vertical-align: middle;'];
            },

            'columns' => [

                ['class' => 'yii\grid\SerialColumn'],

                [
                    'attribute' => 'name',
                    'label' => 'Nama Template',
                    'format' => 'raw',
                    'value' => function ($model) {
                        $name = preg_replace('/\s*\[.*?\]\s*/', '', $model->name);
                        return '<div class="fw-semibold">' . Html::encode($name) . '</div>';
                    },
                ],

                [
                    'label' => 'Jumlah Item',
                    'value' => function ($model) {
                        $schema = $model->getSchema();
                        return isset($schema['items']) ? count($schema['items']) : 0;
                    },
                    'contentOptions' => ['class' => 'text-center fw-semibold'],
                ],

                [
                    'attribute' => 'status',
                    'label' => 'Status',
                    'format' => 'raw',
                    'filter' => [
                        'draft' => 'DRAFT',
                        'active' => 'ACTIVE',
                        'archived' => 'ARCHIVED',
                    ],
                    'value' => function ($model) {
                        $status = (string)($model->status ?? 'draft');
                        $class = $status === 'active'
                            ? 'bg-success'
                            : ($status === 'archived'
                                ? 'bg-secondary'
                                : 'bg-warning text-dark');

                        return Html::tag('span', strtoupper($status), [
                            'class' => 'badge px-3 py-2 ' . $class
                        ]);
                    },
                    'contentOptions' => ['class' => 'text-center'],
                ],

                [
                    'attribute' => 'mapping_validation',
                    'label' => 'Validasi Mapping',
                    'format' => 'raw',
                    'filter' => [
                        'valid' => 'VALID',
                        'invalid' => 'INVALID',
                    ],
                    'value' => function ($model) {
                        $summary = $model->getSchemaValidationSummary();

                        if ($summary['is_valid']) {
                            return '<div class="text-center">'
                                . Html::tag('span', 'VALID', ['class' => 'badge bg-success mb-1'])
                                . '<div class="small text-muted">Siap produksi</div>'
                                . '</div>';
                        }

                        $firstError = $summary['errors'][0] ?? 'Mapping belum valid';

                        return '<div class="text-center">'
                            . Html::tag('span', 'ERROR (' . $summary['error_count'] . ')', ['class' => 'badge bg-danger mb-1'])
                            . '<div class="small text-muted">' . Html::encode($firstError) . '</div>'
                            . '</div>';
                    },
                ],

                [
                    'label' => 'Aksi',
                    'format' => 'raw',
                    'value' => function ($model) {

                        $machineCount = (int)(new \yii\db\Query())
                            ->from('machine_template')
                            ->where(['template_id' => $model->id])
                            ->count();

                        $instanceCount = 0;
                        if (\Yii::$app->db->schema->getTableSchema('checksheet_instance', true) !== null) {
                            $instanceCount = (int)(new \yii\db\Query())
                                ->from('checksheet_instance')
                                ->where(['template_id' => $model->id])
                                ->count();
                        }

                        $resultCount = 0;
                        if (\Yii::$app->db->schema->getTableSchema('form_result', true) !== null) {
                            $resultCount = (int)(new \yii\db\Query())
                                ->from('form_result')
                                ->where(['template_id' => $model->id])
                                ->count();
                        }

                        $totalUsage = $machineCount + $instanceCount + $resultCount;
                        $isLocked = $totalUsage > 0;

                        $buttons = [];

                        $buttons[] = Html::a('Preview', ['preview-form', 'id' => $model->id], [
                            'class' => 'btn btn-sm btn-primary'
                        ]);

                        if (!empty($model->source_file)) {
                            $buttons[] = Html::a('⬇ Template Asli', ['download', 'id' => $model->id], [
                                'class' => 'btn btn-sm btn-outline-secondary'
                            ]);
                        }

                        if ($isLocked || (string)$model->status === 'active') {
                            $buttons[] = Html::beginForm(['revise', 'id' => $model->id], 'post')
                                . Html::submitButton('Revisi', [
                                    'class' => 'btn btn-sm btn-outline-warning'
                                ])
                                . Html::endForm();
                        }

                        if ($totalUsage > 0) {
                            $buttons[] = Html::beginForm(['delete', 'id' => $model->id], 'post')
                                . Html::submitButton('🗑 Hapus', [
                                    'class' => 'btn btn-sm btn-outline-danger',
                                ])
                                . Html::endForm();
                        } else {
                            $buttons[] = Html::beginForm(['delete', 'id' => $model->id], 'post')
                                . Html::submitButton('🗑 Hapus', [
                                    'class' => 'btn btn-sm btn-danger',
                                ])
                                . Html::endForm();
                        }

                        return '<div class="d-flex flex-wrap gap-1">' . implode('', $buttons) . '</div>';
                    },
                ],

            ],
        ]); ?>

    </div>
</div>