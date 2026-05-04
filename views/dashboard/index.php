<?php

use yii\helpers\Html;

/** @var array $stats */

$this->title = 'Dashboard';
?>

<div class="container-fluid p-0">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1"><strong>Check Sheet Digital Dashboard</strong></h1>
            <div class="text-muted small">
                Real-time monitoring checksheet, approval, machine activity
            </div>
        </div>

        <div>
            <?= Html::a(
                'View All Results',
                ['/checksheet-result/index'],
                ['class' => 'btn btn-primary']
            ) ?>
        </div>
    </div>


    <!-- KPI ROW -->
    <div class="row">

        <div class="col-md-3 mb-3">
            <div class="card dashboard-card bg-primary text-white h-100">
                <div class="card-body">
                    <div class="small">Total Submit Today</div>
                    <h2><?= $stats['submit_today'] ?? 0 ?></h2>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card dashboard-card bg-success text-white h-100">
                <div class="card-body">
                    <div class="small">Fully Approved</div>
                    <h2><?= $stats['approved'] ?? 0 ?></h2>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card dashboard-card bg-warning text-dark h-100">
                <div class="card-body">
                    <div class="small">Pending Approval</div>
                    <h2><?= $stats['pending'] ?? 0 ?></h2>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card dashboard-card bg-danger text-white h-100">
                <div class="card-body">
                    <div class="small">Rejected / Rollback</div>
                    <h2><?= $stats['rejected'] ?? 0 ?></h2>
                </div>
            </div>
        </div>

    </div>


    <!-- APPROVAL PIPELINE -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <strong>Approval Pipeline</strong>
        </div>

        <div class="card-body">
            <div class="row text-center">

                <div class="col-md-3">
                    <h4><?= $stats['submitted'] ?? 0 ?></h4>
                    <small class="text-muted">Submitted</small>
                </div>

                <div class="col-md-3">
                    <h4><?= $stats['leader_approved'] ?? 0 ?></h4>
                    <small class="text-muted">Foreman Approved</small>
                </div>

                <div class="col-md-3">
                    <h4><?= $stats['chief_approved'] ?? 0 ?></h4>
                    <small class="text-muted">Chief Approved</small>
                </div>

                <div class="col-md-3">
                    <h4><?= $stats['final_approved'] ?? 0 ?></h4>
                    <small class="text-muted">Manager Approved</small>
                </div>

            </div>
        </div>
    </div>


    <!-- TEMPLATE HEALTH -->
    <div class="row">

        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white">
                    <strong>Template Health</strong>
                </div>

                <div class="card-body">

                    <div class="mb-3">
                        Total Template:
                        <strong><?= $stats['total'] ?? 0 ?></strong>
                    </div>

                    <div class="mb-3 text-success">
                        Valid Mapping:
                        <strong><?= $stats['valid'] ?? 0 ?></strong>
                    </div>

                    <div class="mb-3 text-danger">
                        Invalid Mapping:
                        <strong><?= $stats['invalid'] ?? 0 ?></strong>
                    </div>

                    <div class="text-primary">
                        Active Template:
                        <strong><?= $stats['active'] ?? 0 ?></strong>
                    </div>

                </div>
            </div>
        </div>


        <!-- MACHINE STATUS -->
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white">
                    <strong>Machine Monitoring</strong>
                </div>

                <div class="card-body">

                    <div class="d-flex justify-content-between mb-3">
                        <span>🟢 Normal Submit</span>
                        <strong><?= $stats['machine_normal'] ?? 0 ?></strong>
                    </div>

                    <div class="d-flex justify-content-between mb-3">
                        <span>🟡 Late Submit</span>
                        <strong><?= $stats['machine_late'] ?? 0 ?></strong>
                    </div>

                    <div class="d-flex justify-content-between">
                        <span>🔴 No Submit</span>
                        <strong><?= $stats['machine_missing'] ?? 0 ?></strong>
                    </div>

                </div>
            </div>
        </div>

    </div>


    <!-- QUICK ACTION -->
    <div class="card border-0 shadow-sm">
        <div class="card-body d-flex flex-wrap gap-2">

            <?= Html::a(
                'Checksheet Results',
                ['/checksheet-result/index'],
                ['class' => 'btn btn-primary']
            ) ?>

            <?= Html::a(
                'Pending Approval',
                ['/checksheet-result/pending-approval'],
                ['class' => 'btn btn-warning']
            ) ?>

            <?= Html::a(
                'Approval History',
                ['/checksheet-result/approval-history'],
                ['class' => 'btn btn-success']
            ) ?>

            <?= Html::a(
                'Manage Templates',
                ['/form-template/index'],
                ['class' => 'btn btn-outline-dark']
            ) ?>

        </div>
    </div>

</div>


<style>
.dashboard-card {
    border: none;
    border-radius: 12px;
}

.dashboard-card h2 {
    font-weight: 700;
    margin-top: 10px;
}
</style>