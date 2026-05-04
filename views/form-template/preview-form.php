<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var app\models\FormTemplate $model */
/** @var array $lockState */

$this->title = 'Mapping Template: Item Builder ↔ Cell Excel';
$schema = $model->getSchema();
$items = $schema['items'] ?? [];
$lockState = $lockState ?? ['is_locked' => false];
$isLocked = !empty($lockState['is_locked']);
?>

<h3><?= Html::encode($this->title) ?></h3>

<div class="alert alert-success">
    <strong>Status:</strong>
    <b><?= Html::encode(strtoupper($model->status)) ?></b>
</div>

<?php if (empty($items)): ?>
    <div class="alert alert-danger">
        Template tidak memiliki item.
    </div>
<?php else: ?>

<!-- ================= FORM MAPPING ================= -->
<?php $form = ActiveForm::begin([
    'action' => ['update-mapping', 'id' => $model->id],
    'method' => 'post',
]); ?>

<div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead class="table-light">
            <tr>
                <th style="width:60px">No</th>
                <th>Item</th>
                <th style="width:100px">Wajib</th>
                <th style="width:300px">Mapping</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
                <?php
                $itemId = $item['item_id'] ?? '';
                $sheet = $item['excel']['sheet'] ?? '';
                $row   = $item['excel']['row'] ?? '';
                $cell  = strtoupper($item['excel']['cell'] ?? '');
                ?>
                <tr>
                    <td class="text-center"><?= $item['no'] ?></td>

                    <td>
                        <div class="fw-semibold"><?= Html::encode($item['label']) ?></div>
                        <small class="text-muted"><?= Html::encode($itemId) ?></small>
                    </td>

                    <td class="text-center">
                        <?= Html::checkbox("mappings[$itemId][required]", !empty($item['required']), [
                            'disabled' => $isLocked
                        ]) ?>
                    </td>

                    <td>
                        <?= Html::textInput("mappings[$itemId][sheet]", $sheet, [
                            'class' => 'form-control mb-1',
                            'placeholder' => 'Sheet1',
                            'disabled' => $isLocked
                        ]) ?>

                        <?= Html::input('number', "mappings[$itemId][row]", $row, [
                            'class' => 'form-control mb-1',
                            'placeholder' => 'Row',
                            'min' => 1,
                            'disabled' => $isLocked
                        ]) ?>

                        <?= Html::textInput("mappings[$itemId][cell]", $cell, [
                            'class' => 'form-control',
                            'placeholder' => 'E33',
                            'disabled' => $isLocked
                        ]) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<hr class="my-4">
<!-- ================= META APPROVAL MAPPING ================= -->
<h4>Approval / Initial Mapping</h4>

<?php
$metaMapping = $schema['meta_mapping'] ?? [];
?>

<div class="row">

    <!-- Operator Shift -->
    <div class="col-md-3">
        <label class="form-label fw-bold">Operator Shift 1</label>
        <?= Html::textInput(
            'meta_mapping[operator_shift_1]',
            $metaMapping['operator_shift_1'] ?? '',
            [
                'class' => 'form-control',
                'placeholder' => 'E10',
                'disabled' => $isLocked
            ]
        ) ?>
    </div>

    <div class="col-md-3">
        <label class="form-label fw-bold">Operator Shift 2</label>
        <?= Html::textInput(
            'meta_mapping[operator_shift_2]',
            $metaMapping['operator_shift_2'] ?? '',
            [
                'class' => 'form-control',
                'placeholder' => 'F10',
                'disabled' => $isLocked
            ]
        ) ?>
    </div>

    <div class="col-md-3">
        <label class="form-label fw-bold">Operator Shift 3</label>
        <?= Html::textInput(
            'meta_mapping[operator_shift_3]',
            $metaMapping['operator_shift_3'] ?? '',
            [
                'class' => 'form-control',
                'placeholder' => 'G10',
                'disabled' => $isLocked
            ]
        ) ?>
    </div>

</div>

<br>

<div class="row">

    <!-- Leader/Sub FR Shift -->
    <div class="col-md-3">
        <label class="form-label fw-bold">Sub FR / Leader Shift 1</label>
        <?= Html::textInput(
            'meta_mapping[leader_shift_1]',
            $metaMapping['leader_shift_1'] ?? '',
            [
                'class' => 'form-control',
                'placeholder' => 'E13',
                'disabled' => $isLocked
            ]
        ) ?>
    </div>

    <div class="col-md-3">
        <label class="form-label fw-bold">Sub FR / Leader Shift 2</label>
        <?= Html::textInput(
            'meta_mapping[leader_shift_2]',
            $metaMapping['leader_shift_2'] ?? '',
            [
                'class' => 'form-control',
                'placeholder' => 'F13',
                'disabled' => $isLocked
            ]
        ) ?>
    </div>

    <div class="col-md-3">
        <label class="form-label fw-bold">Sub FR / Leader Shift 3</label>
        <?= Html::textInput(
            'meta_mapping[leader_shift_3]',
            $metaMapping['leader_shift_3'] ?? '',
            [
                'class' => 'form-control',
                'placeholder' => 'G13',
                'disabled' => $isLocked
            ]
        ) ?>
    </div>

</div>

<br>

<div class="row">

    <!-- Final Approval -->
    <div class="col-md-3">
        <label class="form-label fw-bold">Chief</label>
        <?= Html::textInput(
            'meta_mapping[chief]',
            $metaMapping['chief'] ?? '',
            [
                'class' => 'form-control',
                'placeholder' => 'H20',
                'disabled' => $isLocked
            ]
        ) ?>
    </div>

    <div class="col-md-3">
        <label class="form-label fw-bold">Manager</label>
        <?= Html::textInput(
            'meta_mapping[manager]',
            $metaMapping['manager'] ?? '',
            [
                'class' => 'form-control',
                'placeholder' => 'H22',
                'disabled' => $isLocked
            ]
        ) ?>
    </div>

</div>
<!-- ================= END META APPROVAL MAPPING ================= -->

<div class="mt-3">
    <?php if (!$isLocked): ?>
        <?= Html::submitButton('💾 Simpan Mapping', ['class' => 'btn btn-primary']) ?>
    <?php endif; ?>
</div>

<?php ActiveForm::end(); ?>
<!-- ================= END FORM MAPPING ================= -->


<!-- ================= ACTION BUTTONS ================= -->
<div class="mt-3 d-flex gap-2">

    <?php if (!$isLocked): ?>
        <?= Html::beginForm(['/form-template/activate', 'id' => $model->id], 'post') ?>
            <?= Html::submitButton('✅ Aktifkan Template', [
                'class' => 'btn btn-success',
                'onclick' => "return confirm('Aktifkan template ini?');"
            ]) ?>
        <?= Html::endForm() ?>
    <?php endif; ?>

    <?= Html::beginForm(['/form-template/revise', 'id' => $model->id], 'post') ?>
        <?= Html::submitButton('📝 Buat Revisi', [
            'class' => 'btn btn-warning',
            'onclick' => "return confirm('Buat revisi baru?');"
        ]) ?>
    <?= Html::endForm() ?>

    <?= Html::a('⬅ Kembali', ['index'], ['class' => 'btn btn-secondary']) ?>

</div>
<!-- ================= END ================= -->

<?php endif; ?>