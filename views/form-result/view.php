<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$renderValue = function ($value) use (&$renderValue) {
    if (is_array($value)) {
        if (empty($value)) {
            return '<span class="text-muted">(kosong)</span>';
        }

        $isAssoc = array_keys($value) !== range(0, count($value) - 1);
        $html = '<div class="value-tree">';
        foreach ($value as $k => $v) {
            $label = $isAssoc ? Html::encode((string)$k) : ('#' . ((int)$k + 1));
            $html .= '<div class="value-row">';
            $html .= '<div class="value-key">' . $label . '</div>';
            $html .= '<div class="value-content">' . $renderValue($v) . '</div>';
            $html .= '</div>';
        }
        $html .= '</div>';
        return $html;
    }

    if (is_bool($value)) {
        return $value ? 'true' : 'false';
    }

    if ($value === null || $value === '') {
        return '<span class="text-muted">(kosong)</span>';
    }

    if (is_numeric($value)) {
        return Html::encode((string)$value);
    }

    $stringValue = trim((string)$value);

    // Render data URI image from Android payload as thumbnail.
    if (preg_match('/^data:image\/(png|jpe?g|webp|gif);base64,/i', $stringValue)) {
        return '<img src="' . Html::encode($stringValue) . '" alt="image" class="img-thumbnail" style="max-width:220px; max-height:160px;">';
    }

    if (filter_var($stringValue, FILTER_VALIDATE_URL)) {
        return Html::a(Html::encode($stringValue), $stringValue, ['target' => '_blank', 'rel' => 'noopener']);
    }

    return nl2br(Html::encode($stringValue));
};

$this->title = 'Form Result #' . $formResult->id;
$this->params['breadcrumbs'][] = ['label' => 'Form Results', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="form-result-view">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title"><?= $this->title ?></h3>
            <?= Html::a('📥 Download Excel', ['download', 'id' => $formResult->id], ['class' => 'btn btn-success btn-sm', 'target' => '_blank']) ?>
        </div>
        <div class="card-body">
            <!-- Info Utama -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <p>
                        <strong>Template:</strong><br>
                        <?= Html::encode($formResult->template->name ?? '-') ?>
                    </p>
                    <p>
                        <strong>Nomor Mesin:</strong><br>
                        <?= Html::encode($formResult->no_mesin) ?>
                    </p>
                    <p>
                        <strong>Operator:</strong><br>
                        <?= Html::encode($formResult->operator) ?>
                    </p>
                </div>
                <div class="col-md-6">
                    <p>
                        <strong>Tanggal:</strong><br>
                        <?= $formResult->tanggal ?>
                    </p>
                    <p>
                        <strong>Shift:</strong><br>
                        <?= Html::encode($formResult->shift) ?>
                    </p>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <h5>Status Approval</h5>
                    <p>
                        <strong>Status:</strong>
                        <?= Html::encode($formResult->getApprovalStatusLabel()) ?>
                    </p>
                    <?php if ($formResult->getNextApprovalRoleLabel() !== null): ?>
                        <p>
                            <strong>Menunggu persetujuan:</strong>
                            <?= Html::encode($formResult->getNextApprovalRoleLabel()) ?>
                        </p>
                    <?php endif; ?>

                    <div class="approval-timeline mt-3">
                        <div class="row g-3">
                            <?php foreach ($formResult->getApprovalHistory() as $history): ?>
                                <div class="col-lg-4 col-md-6">
                                    <div class="approval-card border rounded-3 p-3 h-100 bg-light">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="mb-0 fw-bold"><?= Html::encode($history['label']) ?></h6>
                                            <?php if ($history['approved_at']): ?>
                                                <span class="badge bg-success">APPROVED</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark">PENDING</span>
                                            <?php endif; ?>
                                        </div>
                                        <hr class="my-2">
                                        <div>
                                            <p class="mb-1 text-muted small">
                                                <strong>Disetujui oleh:</strong><br>
                                                <?php if ($history['user']): ?>
                                                    <?= Html::encode($history['user']->fullname) ?>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </p>
                                            <p class="mb-0 text-muted small">
                                                <strong>Tanggal & Waktu:</strong><br>
                                                <?php if ($history['approved_at']): ?>
                                                    <?= date('d/m/Y', $history['approved_at']) ?><br><?= date('H:i:s', $history['approved_at']) ?>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($formResult->canBeApprovedBy(Yii::$app->user->identity)): ?>
                <div class="card mb-4 border-warning">
                    <div class="card-body">
                        <h5 class="card-title">Approve sebagai <?= Html::encode($formResult->getNextApprovalRoleLabel()) ?></h5>
                        <p class="text-muted">Masukkan PIN Anda untuk menyetujui form ini.</p>

                        <?php $form = ActiveForm::begin(['action' => ['approve', 'id' => $formResult->id]]); ?>
                            <?= $form->field($approvalForm, 'pin')->passwordInput(['autocomplete' => 'off'])->label('PIN Persetujuan') ?>
                            <div class="d-flex justify-content-end">
                                <?= Html::submitButton('Approve', ['class' => 'btn btn-success']) ?>
                            </div>
                        <?php ActiveForm::end(); ?>
                    </div>
                </div>
            <?php endif; ?>

            <hr>

            <!-- Detail Data -->
            <h5>Detail Template</h5>
            <?php if (empty($orderedDetails)): ?>
                <p class="text-muted">Tidak ada detail data.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th width="12%">No</th>
                                <th width="18%">Item ID</th>
                                <th width="35%">Item</th>
                                <th width="35%">Hasil</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orderedDetails as $entry): ?>
                                <?php
                                $item = $entry['item'];
                                $detail = $entry['detail'];
                                ?>
                                <tr>
                                    <td><?= Html::encode($item['no'] ?? '-') ?></td>
                                    <td>
                                        <strong><?= Html::encode($item['item_id'] ?? '-') ?></strong>
                                        <?php if (!empty($item['section'])): ?>
                                            <div class="text-muted small mt-1"><?= Html::encode($item['section']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= Html::encode($item['label'] ?? '-') ?>
                                        <?php if (!empty($item['standard'])): ?>
                                            <div class="text-muted small mt-1">Standar: <?= Html::encode($item['standard']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $rawValue = $detail ? $detail->field_value : null;
                                        $decoded = null;

                                        if (is_string($rawValue)) {
                                            $trimmed = trim($rawValue);
                                            if ($trimmed !== '' && in_array($trimmed[0], ['{', '[', '"'], true)) {
                                                $decodedCandidate = json_decode($rawValue, true);
                                                if (json_last_error() === JSON_ERROR_NONE) {
                                                    $decoded = $decodedCandidate;
                                                }
                                            }
                                        }

                                        $valueToRender = $decoded !== null ? $decoded : $rawValue;
                                        ?>
                                        <div class="field-value"><?= $renderValue($valueToRender) ?></div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <hr>

            <!-- Meta Info -->
            <div class="text-muted small">
                <p>
                    <strong>Dibuat:</strong> <?= date('d/m/Y H:i:s', $formResult->created_at) ?>
                </p>
                <?php if ($formResult->updated_at): ?>
                    <p>
                        <strong>Diupdate:</strong> <?= date('d/m/Y H:i:s', $formResult->updated_at) ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.field-value {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 10px;
}

.value-tree {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.value-row {
    display: grid;
    grid-template-columns: minmax(120px, 180px) 1fr;
    gap: 10px;
    align-items: start;
}

.value-key {
    font-weight: 600;
    color: #334155;
    word-break: break-word;
}

.value-content {
    color: #0f172a;
    word-break: break-word;
}

@media (max-width: 768px) {
    .value-row {
        grid-template-columns: 1fr;
        gap: 4px;
    }
}
</style>
