<?php
use yii\helpers\Html;
use yii\widgets\DetailView;
use app\models\MachineTemplate;
use app\models\FormTemplate;

/** @var yii\web\View $this */
/** @var app\models\DaftarMesin $model */

$this->title = $model->nama_mesin;
$this->params['breadcrumbs'][] = ['label' => 'Daftar Mesin', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$qrFull = \Yii::getAlias('@webroot/' . $model->qr_code_path);

$machineTemplate = MachineTemplate::findOne(['no_mesin' => $model->no_mesin]);
$activeTemplate = $machineTemplate
    ? FormTemplate::findOne((int)$machineTemplate->template_id)
    : null;
?>

<div class="daftar-mesin-view card shadow-sm p-3">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h3 class="mb-0">
                <i class="bi bi-cpu-fill me-2"></i>
                <?= Html::encode($model->nama_mesin) ?>
            </h3>
            <div class="text-muted">
                No Mesin: <strong><?= Html::encode($model->no_mesin) ?></strong>
            </div>
        </div>

        <div class="d-flex gap-2">
            <?= Html::a(
                '<i class="bi bi-pencil-square"></i> Update',
                ['update', 'id' => $model->id],
                ['class' => 'btn btn-warning text-white']
            ) ?>

            <?= Html::a(
                '<i class="bi bi-qr-code"></i> Regenerate QR',
                ['regenerate-qr', 'id' => $model->id],
                ['class' => 'btn btn-outline-success']
            ) ?>

            <?= Html::a(
                '<i class="bi bi-trash"></i>',
                ['delete', 'id' => $model->id],
                [
                    'class' => 'btn btn-danger',
                    'title' => 'Hapus Mesin',
                    'data' => [
                        'confirm' => 'Hapus mesin ini?',
                        'method' => 'post',
                    ],
                ]
            ) ?>
        </div>
    </div>

    <hr>

    <div class="alert alert-secondary">
        <strong>Template Aktif di Mesin Ini:</strong>
        <?php if ($activeTemplate): ?>
            <?= Html::encode($activeTemplate->name) ?>
            <span class="badge <?= (string)$activeTemplate->status === 'active' ? 'bg-success' : 'bg-warning text-dark' ?> ms-2">
                <?= Html::encode(strtoupper((string)$activeTemplate->status)) ?>
            </span>
        <?php else: ?>
            <span class="text-muted">Belum ada template yang di-assign.</span>
        <?php endif; ?>
    </div>

    <!-- CONTENT -->
    <div class="row mt-3">
        <!-- DETAIL MESIN -->
        <div class="col-md-6">
            <h5 class="mb-3">
                <i class="bi bi-info-circle me-1"></i>
                Informasi Mesin
            </h5>

            <?= DetailView::widget([
                'model' => $model,
                'options' => ['class' => 'table table-bordered table-striped'],
                'attributes' => [
                    'kategori',
                    'lokasi',
                    'vendor',
                    'serial_number',
                    'created_at:datetime',
                    'updated_at:datetime',
                ],
            ]) ?>
        </div>

        <!-- QR CODE -->
        <div class="col-md-6 text-center">
            <h5 class="mb-3">
                <i class="bi bi-qr-code-scan me-1"></i>
                QR Code Mesin
            </h5>

            <div class="p-3 border rounded bg-light d-inline-block">
                <?php if ($model->qr_code_path && file_exists($qrFull)): ?>
                    <img
                        src="<?= \Yii::getAlias('@web/' . $model->qr_code_path) ?>"
                        width="220"
                        height="220"
                        class="img-thumbnail mb-2"
                        alt="QR Code Mesin"
                    >
                    <div class="text-muted small">
                        Scan untuk akses mesin
                    </div>
                <?php else: ?>
                    <div class="text-muted py-4">
                        <i class="bi bi-exclamation-circle me-1"></i>
                        QR Code belum tersedia
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>
