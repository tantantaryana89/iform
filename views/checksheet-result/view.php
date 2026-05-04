<?php
use yii\helpers\Html;

/** @var app\models\ChecksheetResult $model */
/** @var array $answerMapById */
/** @var array $answerMapByCode */
/** @var app\models\ChecksheetResult[] $historyResults */

$isChecked = static function ($value): bool {
    if ($value === null) {
        return false;
    }

    $v = strtoupper(trim((string)$value));
    return in_array($v, ['OK', '1', 'TRUE', 'YES', 'Y', 'CHECKED'], true);
};

$renderInstructionList = static function (array $items): string {
    if (empty($items)) {
        return '<span class="text-muted">-</span>';
    }

    $html = '<ul class="mb-0 ps-3">';
    foreach ($items as $row) {
        $html .= '<li>' . Html::encode((string)$row) . '</li>';
    }
    $html .= '</ul>';

    return $html;
};
?>

<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0">Checksheet Result #<?= Html::encode($model->id) ?></h4>
        <div class="d-flex gap-2">
            <?= Html::a('Export Transaksi Ini', ['export-original', 'id' => $model->id], ['class' => 'btn btn-primary btn-sm', 'target' => '_blank', 'rel' => 'noopener', 'title' => 'Download hanya transaksi result yang sedang dibuka']) ?>
            <?= Html::a('Export Bulanan Mesin', ['export-excel', 'id' => $model->id], ['class' => 'btn btn-success btn-sm', 'title' => 'Download rekap semua transaksi mesin ini di bulan yang sama']) ?>
        </div>
    </div>

    <div class="card-body">
        <div class="row g-3 mb-4">
            <div class="col-md-3"><strong>Mesin:</strong><br><?= Html::encode($model->mesin) ?></div>
            <div class="col-md-3"><strong>Shift:</strong><br><?= Html::encode($model->shift) ?></div>
            <div class="col-md-3"><strong>Tanggal Submit:</strong><br><?= Html::encode($model->submitted_at) ?></div>
            <div class="col-md-3"><strong>Template:</strong><br><?= Html::encode($model->template->name ?? '-') ?></div>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    
    <h4 class="mb-0">Approval Progress</h4>

    <div class="approval-toolbar">
        <?= Html::a(
            'Review',
            ['view', 'id' => $model->id],
            [
                'class' => 'btn btn-sm btn-info text-white'
            ]
        ) ?>

        <?php if (
            in_array(Yii::$app->user->identity->role, ['foreman', 'subforeman']) &&
            $model->approval_status === 'submitted'
        ): ?>
            <?= Html::a(
                'Approve',
                ['approve-foreman', 'id' => $model->id],
                [
                    'class' => 'btn btn-sm btn-success',
                    'data-confirm' => 'Approve checksheet ini?'
                ]
            ) ?>
        <?php endif; ?>

        <?php if (
            Yii::$app->user->identity->role === 'chief' &&
            $model->approval_status === 'leader_approved'
        ): ?>
            <?= Html::a(
                'Approve',
                ['approve-chief', 'id' => $model->id],
                [
                    'class' => 'btn btn-sm btn-success',
                    'data-confirm' => 'Approve checksheet ini?'
                ]
            ) ?>
        <?php endif; ?>

        <?php if (
            Yii::$app->user->identity->role === 'manager' &&
            $model->approval_status === 'chief_approved'
        ): ?>
            <?= Html::a(
                'Approve',
                ['approve-manager', 'id' => $model->id],
                [
                    'class' => 'btn btn-sm btn-success',
                    'data-confirm' => 'Approve checksheet ini?'
                ]
            ) ?>
        <?php endif; ?>

        <?php if (
            in_array(
                Yii::$app->user->identity->role,
                ['foreman', 'subforeman', 'chief', 'manager']
            )
        ): ?>
            <?= Html::a(
                'Reject',
                ['reject-workflow', 'id' => $model->id],
                [
                    'class' => 'btn btn-sm btn-danger',
                    'data-confirm' => 'Reject checksheet ini?'
                ]
            ) ?>

            <?= Html::a(
                'Rollback',
                ['rollback-workflow', 'id' => $model->id],
                [
                    'class' => 'btn btn-sm btn-warning',
                    'data-confirm' => 'Rollback approval ini?'
                ]
            ) ?>
        <?php endif; ?>

    </div>
</div>
        <?php
        $approvalSteps = [
            [
                'label' => 'OPERATOR',
                'name' => $model->created_by,
                'date' => $model->submitted_at,
                'approved' => true
            ],
            [
                'label' => 'FOREMAN / SUBFOREMAN',
                'name' => $model->leader->fullname ?? null,
                'date' => $model->leader_approved_at,
                'approved' => !empty($model->leader_approved_at)
            ],
            [
                'label' => 'CHIEF',
                'name' => $model->chief->fullname ?? null,
                'date' => $model->chief_approved_at,
                'approved' => !empty($model->chief_approved_at)
            ],
            [
                'label' => 'MANAGER',
                'name' => $model->manager->fullname ?? null,
                'date' => $model->manager_approved_at,
                'approved' => !empty($model->manager_approved_at)
            ],
        ];
        ?>

        <div class="approval-wrapper">
            <?php foreach ($approvalSteps as $step): ?>
                <div class="approval-box">

                    <div class="approval-header">
                        <?= $step['label'] ?>
                    </div>

                    <div class="approval-body">

                        <?php if ($step['approved']): ?>
                            <div class="approval-status">
                                <?= $step['label'] === 'OPERATOR' ? 'SUBMITTED' : 'APPROVED' ?>
                            </div>

                            <div class="approval-name">
                                <?= strtoupper($step['name']) ?>
                            </div>

                            <div class="approval-date">
                                <?= date(
                                    'Y-m-d H:i:s',
                                    strtotime($step['date'])
                                ) ?>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <h5>Riwayat Submit Mesin Ini</h5>
        <?php if (empty($historyResults)): ?>
            <p class="text-muted">Belum ada riwayat lain untuk mesin ini.</p>
        <?php else: ?>
            <div class="table-responsive mb-4">
                <table class="table table-sm table-bordered align-middle">
                    <thead>
                        <tr>
                            <th style="width:80px">ID</th>
                            <th style="width:180px">Submitted At</th>
                            <th style="width:100px">Shift</th>
                            <th style="width:140px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($historyResults as $h): ?>
                            <tr>
                                <td><?= Html::encode($h->id) ?></td>
                                <td><?= Html::encode($h->submitted_at) ?></td>
                                <td><?= Html::encode($h->shift) ?></td>
                                <td>
                                    <?= Html::a('Lihat', ['view', 'id' => $h->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                                    <?= Html::a('Export', ['export-original', 'id' => $h->id], ['class' => 'btn btn-sm btn-outline-success', 'target' => '_blank', 'rel' => 'noopener']) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <hr>

        <?php if (!$model->template || empty($model->template->sections)): ?>
            <div class="alert alert-warning mb-0">
                Struktur template tidak ditemukan. Menampilkan data mentah.
            </div>

            <div class="table-responsive mt-3">
                <table class="table table-bordered table-sm align-middle">
                    <thead>
                        <tr>
                            <th>Item ID</th>
                            <th>Item Code</th>
                            <th>Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($model->items as $row): ?>
                            <tr>
                                <td><?= Html::encode($row->item_id) ?></td>
                                <td><?= Html::encode($row->item_code) ?></td>
                                <td><?= Html::encode($row->raw_value) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <?php foreach ($model->template->sections as $section): ?>
                <h5 class="section-title mt-4 mb-2">- <?= Html::encode($section->title) ?> -</h5>

                <?php if (empty($section->items)): ?>
                    <p class="text-muted">Tidak ada item pada section ini.</p>
                <?php endif; ?>

                <?php foreach ($section->items as $templateItem): ?>
                    <?php
                    $resultItem = $answerMapById[(int)$templateItem->id]
                        ?? ($answerMapByCode[(string)$templateItem->item_code] ?? null);
                    $rawValue = $resultItem->raw_value ?? null;
                    $instruction = $templateItem->getInstruction();
                    ?>

                    <div class="item-card border rounded p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1 me-3">
                                <div class="mb-2">
                                    <?php if ($templateItem->type === 'checklist'): ?>
                                        <label class="form-check d-flex align-items-center gap-2 mb-0">
                                            <input
                                                type="checkbox"
                                                class="form-check-input"
                                                disabled
                                                <?= $isChecked($rawValue) ? 'checked' : '' ?>
                                            >
                                            <span class="fw-semibold"><?= Html::encode($templateItem->label) ?></span>
                                        </label>
                                    <?php elseif ($templateItem->type === 'number'): ?>
                                        <div class="fw-semibold mb-1"><?= Html::encode($templateItem->label) ?></div>
                                        <input type="number" class="form-control form-control-sm" value="<?= Html::encode((string)$rawValue) ?>" disabled>
                                    <?php elseif ($templateItem->type === 'text_input'): ?>
                                        <div class="fw-semibold mb-1"><?= Html::encode($templateItem->label) ?></div>
                                        <textarea class="form-control form-control-sm" rows="2" disabled><?= Html::encode((string)$rawValue) ?></textarea>
                                    <?php else: ?>
                                        <div class="fw-semibold"><?= Html::encode($templateItem->label) ?></div>
                                        <div class="result-pill mt-1"><?= Html::encode((string)($rawValue ?? '-')) ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="row g-2 instruction-grid small">
                                    <div class="col-md-3">
                                        <div class="instruction-title">Standard</div>
                                        <div class="instruction-body"><?= $renderInstructionList($instruction['standard'] ?? []) ?></div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="instruction-title">Cara</div>
                                        <div class="instruction-body"><?= $renderInstructionList($instruction['cara'] ?? []) ?></div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="instruction-title">Frekuensi</div>
                                        <div class="instruction-body"><?= $renderInstructionList($instruction['frekuensi'] ?? []) ?></div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="instruction-title">Catatan</div>
                                        <div class="instruction-body"><?= $renderInstructionList($instruction['note'] ?? []) ?></div>
                                    </div>
                                </div>

                                <div class="text-muted small mt-2">
                                    Item Code: <?= Html::encode($templateItem->item_code) ?>
                                </div>
                            </div>

                            <div class="d-flex align-items-start flex-shrink-0 gap-2">
                                <?php if ($templateItem->symbol): ?>
                                    <img
                                        src="<?= Html::encode($templateItem->symbol->image_path) ?>"
                                        width="28"
                                        alt="<?= Html::encode($templateItem->symbol->name) ?>"
                                        title="<?= Html::encode($templateItem->symbol->name) ?>"
                                    >
                                <?php endif; ?>

                                <?php if ($templateItem->symbol2): ?>
                                    <img
                                        src="<?= Html::encode($templateItem->symbol2->image_path) ?>"
                                        width="28"
                                        alt="<?= Html::encode($templateItem->symbol2->name) ?>"
                                        title="<?= Html::encode($templateItem->symbol2->name) ?>"
                                    >
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<style>
.section-title {
    padding: 8px 10px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
}

.item-card {
    background: #fff;
}

.instruction-title {
    font-weight: 600;
    margin-bottom: 4px;
}

.instruction-body {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 6px 8px;
    min-height: 48px;
}

.result-pill {
    display: inline-block;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 999px;
    padding: 2px 10px;
}
.approval-wrapper {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 15px;
}

.approval-box {
    border: 1px solid #dcdcdc;
    border-radius: 6px;
    overflow: hidden;
}

.approval-header {
    background: #0b6b61;
    color: white;
    text-align: center;
    font-weight: 600;
    font-size: 13px;
    padding: 10px;
    letter-spacing: 0.5px;
}

.approval-body {
    background: #0d1b2a;
    min-height: 150px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    color: white;
    padding: 15px;
}

.approval-status {
    font-size: 20px;
    font-weight: 700;
}

.approval-name {
    font-size: 14px;
    font-weight: 500;
    margin-top: 8px;
    text-transform: uppercase;
    text-align: center;
}

.approval-date {
    font-size: 11px;
    margin-top: 10px;
    opacity: 0.9;
    text-align: center;
}

@media (max-width: 768px) {
    .approval-wrapper {
        grid-template-columns: repeat(2, 1fr);
    }
}

</style>
