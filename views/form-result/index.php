<?php
use yii\helpers\Html;

$this->title = 'Daftar Form Results';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="form-result-index card shadow-sm p-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">
            <i class="bi bi-clipboard-data me-2"></i>
            <?= Html::encode($this->title) ?>
        </h3>
        <?= Html::a('<i class="bi bi-download me-1"></i> Download Semua', ['download-all'], ['class' => 'btn btn-success', 'target' => '_blank']) ?>
    </div>

    <?php if (empty($formResults)): ?>
        <p class="text-muted">Belum ada form yang disubmit.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead>
                    <tr>
                                <th>ID</th>
                        <th>Nomor Mesin</th>
                        <th>Operator</th>
                        <th>Tanggal</th>
                        <th>Shift</th>
                        <th>Status Approval</th>
                        <th>Next Approver</th>
                        <th>Dibuat</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($formResults as $result): ?>
                        <tr>
                            <td><?= $result->id ?></td>
                            <td><strong><?= Html::encode($result->no_mesin) ?></strong></td>
                            <td><?= Html::encode($result->operator) ?></td>
                            <td><?= $result->tanggal ?></td>
                            <td><?= Html::encode($result->shift) ?></td>
                            <td><?= Html::encode($result->getApprovalStatusLabel()) ?></td>
                            <td><?= Html::encode($result->getNextApprovalRoleLabel() ?? '-') ?></td>
                            <td><?= date('d/m/Y H:i', $result->created_at) ?></td>
                            <td class="text-center">
                                <?= Html::a('<i class="fas fa-eye"></i>', ['view', 'id' => $result->id], [
                                    'title' => 'Lihat Detail',
                                    'class' => 'btn btn-sm btn-primary me-1'
                                ]) ?>
                                <?= Html::a('<i class="fas fa-download"></i>', ['download', 'id' => $result->id], [
                                    'title' => 'Download Excel',
                                    'class' => 'btn btn-sm btn-success me-1',
                                    'target' => '_blank'
                                ]) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
