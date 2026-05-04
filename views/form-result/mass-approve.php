<?php
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

$this->title = 'Mass Approval';
$this->params['breadcrumbs'][] = ['label' => 'Daftar Form Results', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="form-result-mass-approve card shadow-sm p-3">
    <div class="mb-3">
        <h3 class="mb-0"><i class="bi bi-check2-square me-2"></i> <?= Html::encode($this->title) ?></h3>
        <p class="text-muted">Setujui beberapa notifikasi sekaligus dengan PIN Anda.</p>
    </div>

    <?php if (empty($selectedForms)): ?>
        <div class="alert alert-warning">
            Tidak ada notifikasi yang dapat diproses. Kembali ke <strong><?= Html::a('Daftar Form Results', ['index']) ?></strong> untuk melihat notifikasi yang tersedia.
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            <?= Html::encode('Akan disetujui ' . count($selectedForms) . ' notifikasi.') ?>
        </div>

        <div class="table-responsive mb-4">
            <table class="table table-bordered table-striped align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nomor Mesin</th>
                        <th>Operator</th>
                        <th>Status Saat Ini</th>
                        <th>Next Approver</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($selectedForms as $form): ?>
                        <tr>
                            <td><?= Html::encode($form->id) ?></td>
                            <td><?= Html::encode($form->no_mesin) ?></td>
                            <td><?= Html::encode($form->operator) ?></td>
                            <td><?= Html::encode($form->getApprovalStatusLabel()) ?></td>
                            <td><?= Html::encode($form->getNextApprovalRoleLabel() ?? '-') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php $form = ActiveForm::begin(['id' => 'mass-approval-form']); ?>
            <?php foreach ($selectedForms as $formModel): ?>
                <?= Html::hiddenInput('selectedIds[]', $formModel->id) ?>
            <?php endforeach; ?>

            <?= $form->errorSummary($model) ?>
            <?= $form->field($model, 'pin')->passwordInput(['autocomplete' => 'off'])->label('PIN Persetujuan') ?>

            <div class="d-flex justify-content-end gap-2">
                <?= Html::a('Batal', ['index'], ['class' => 'btn btn-secondary']) ?>
                <?= Html::submitButton('Approve Sekaligus', ['class' => 'btn btn-success']) ?>
            </div>
        <?php ActiveForm::end(); ?>
    <?php endif; ?>
</div>
