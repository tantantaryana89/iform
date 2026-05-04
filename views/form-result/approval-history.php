<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use app\models\FormResult;
use app\models\User;

$this->title = 'Riwayat Approval Lengkap';
$this->params['breadcrumbs'][] = ['label' => 'Form Result', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="approval-history-container">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">
                <i class="bi bi-clock-history"></i> <?= Html::encode($this->title) ?>
            </h4>
        </div>

        <div class="card-body">
            <!-- Filters -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <form method="get" class="form-inline gap-2">
                        <div class="form-group me-2">
                            <label for="status-filter" class="me-2">Status:</label>
                            <select id="status-filter" name="status" class="form-control form-control-sm">
                                <option value="">Semua Status</option>
                                <option value="<?= FormResult::STATUS_APPROVED ?>" <?= $statusFilter === FormResult::STATUS_APPROVED ? 'selected' : '' ?>>Approved</option>
                                <option value="<?= FormResult::STATUS_CHIEF_APPROVED ?>" <?= $statusFilter === FormResult::STATUS_CHIEF_APPROVED ? 'selected' : '' ?>>Chief Approved</option>
                                <option value="<?= FormResult::STATUS_SUPERVISOR_APPROVED ?>" <?= $statusFilter === FormResult::STATUS_SUPERVISOR_APPROVED ? 'selected' : '' ?>>Supervisor Approved</option>
                                <option value="<?= FormResult::STATUS_LEADER_APPROVED ?>" <?= $statusFilter === FormResult::STATUS_LEADER_APPROVED ? 'selected' : '' ?>>Leader Approved</option>
                            </select>
                        </div>

                        <div class="form-group me-2">
                            <label for="date-from" class="me-2">Dari Tanggal:</label>
                            <input type="date" id="date-from" name="date_from" class="form-control form-control-sm" value="<?= Html::encode($dateFromFilter) ?>">
                        </div>

                        <div class="form-group me-2">
                            <label for="date-to" class="me-2">Sampai Tanggal:</label>
                            <input type="date" id="date-to" name="date_to" class="form-control form-control-sm" value="<?= Html::encode($dateToFilter) ?>">
                        </div>

                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="bi bi-search"></i> Filter
                        </button>
                        <a href="<?= Url::to(['form-result/approval-history']) ?>" class="btn btn-sm btn-secondary">
                            <i class="bi bi-arrow-clockwise"></i> Reset
                        </a>
                    </form>
                </div>
            </div>

            <!-- Approval Records Table -->
            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">No.</th>
                            <th width="15%">Form ID / No Mesin</th>
                            <th width="15%">Operator</th>
                            <th width="20%">Status</th>
                            <th width="20%">Last Approver</th>
                            <th width="15%">Tanggal</th>
                            <th width="10%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        if (empty($formResults)):
                        ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox"></i> Tidak ada data approval
                                </td>
                            </tr>
                        <?php
                        else:
                            foreach ($formResults as $form):
                                $lastApprover = '-';
                                $lastApprovedDate = '-';

                                // Determine last approver based on approval_status
                                if ($form->approval_status === FormResult::STATUS_APPROVED && $form->manager_id) {
                                    $user = User::findOne($form->manager_id);
                                    $lastApprover = $user ? $user->fullname : 'Unknown';
                                    $lastApprovedDate = Yii::$app->formatter->asDateTime($form->manager_approved_at);
                                } elseif ($form->approval_status === FormResult::STATUS_CHIEF_APPROVED && $form->chief_id) {
                                    $user = User::findOne($form->chief_id);
                                    $lastApprover = $user ? $user->fullname : 'Unknown';
                                    $lastApprovedDate = Yii::$app->formatter->asDateTime($form->chief_approved_at);
                                } elseif ($form->approval_status === FormResult::STATUS_SUPERVISOR_APPROVED && $form->supervisor_id) {
                                    $user = User::findOne($form->supervisor_id);
                                    $lastApprover = $user ? $user->fullname : 'Unknown';
                                    $lastApprovedDate = Yii::$app->formatter->asDateTime($form->supervisor_approved_at);
                                } elseif ($form->approval_status === FormResult::STATUS_LEADER_APPROVED && $form->leader_id) {
                                    $user = User::findOne($form->leader_id);
                                    $lastApprover = $user ? $user->fullname : 'Unknown';
                                    $lastApprovedDate = Yii::$app->formatter->asDateTime($form->leader_approved_at);
                                }

                                // Get operator name from string field
                                $operatorName = !empty($form->operator) ? Html::encode($form->operator) : '-';

                                // Status badge
                                $statusLabel = $form->getApprovalStatusLabel();
                                $statusColors = [
                                    FormResult::STATUS_APPROVED => 'success',
                                    FormResult::STATUS_CHIEF_APPROVED => 'info',
                                    FormResult::STATUS_SUPERVISOR_APPROVED => 'primary',
                                    FormResult::STATUS_LEADER_APPROVED => 'warning',
                                ];
                                $colorClass = $statusColors[$form->approval_status] ?? 'secondary';
                        ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td>
                                    <strong><?= Html::encode($form->no_mesin) ?></strong><br>
                                    <small class="text-muted">#<?= $form->id ?></small>
                                </td>
                                <td><?= Html::encode($operatorName) ?></td>
                                <td>
                                    <span class="badge bg-<?= $colorClass ?>">
                                        <?= Html::encode($statusLabel) ?>
                                    </span>
                                </td>
                                <td><?= Html::encode($lastApprover) ?></td>
                                <td><?= $lastApprovedDate ?></td>
                                <td>
                                    <?= Html::a(
                                        '<i class="bi bi-eye"></i>',
                                        ['form-result/view', 'id' => $form->id],
                                        [
                                            'class' => 'btn btn-sm btn-info',
                                            'title' => 'Detail Approval'
                                        ]
                                    ) ?>
                                </td>
                            </tr>
                        <?php
                            endforeach;
                        endif;
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer bg-light">
            <small class="text-muted">
                Total records: <strong><?= count($formResults) ?></strong> | 
                Menampilkan data yang sudah di-approve dari semua user
            </small>
        </div>
    </div>
</div>

<style>
    .approval-history-container {
        margin-bottom: 20px;
    }

    .table-hover tbody tr:hover {
        background-color: #f9f9f9;
    }

    .form-inline {
        display: flex;
        flex-wrap: wrap;
    }

    .form-inline .form-group {
        display: flex;
        align-items: center;
        margin-bottom: 0;
    }

    @media (max-width: 768px) {
        .form-inline {
            flex-direction: column;
        }

        .form-inline .form-group {
            margin-bottom: 10px;
            width: 100%;
        }

        .form-inline .form-group label {
            margin-right: 10px;
        }

        .form-inline .form-group input,
        .form-inline .form-group select {
            flex: 1;
        }
    }
</style>
