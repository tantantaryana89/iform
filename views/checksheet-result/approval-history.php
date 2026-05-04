<?php

use yii\helpers\Html;

$this->title = 'Approval History';
?>

<div class="container-fluid">
    <h3 class="mb-3"><?= Html::encode($this->title) ?></h3>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Mesin</th>
                <th>Shift</th>
                <th>Submitted</th>
                <th>Status</th>
                <th>Leader</th>
                <th>Chief</th>
                <th>Manager</th>
                <th>Aksi</th>
            </tr>
        </thead>

        <tbody>
            <?php if (empty($results)): ?>
                <tr>
                    <td colspan="9" class="text-center text-muted">
                        Belum ada history approval
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($results as $row): ?>
                    <tr>
                        <td><?= $row->id ?></td>
                        <td><?= $row->mesin ?></td>
                        <td><?= $row->shift ?></td>
                        <td><?= $row->submitted_at ?></td>
                        <td>
                            <span class="badge bg-success">
                                <?= $row->approval_status ?>
                            </span>
                        </td>
                        <td>
                            <?= $row->leader ? $row->leader->fullname : '-' ?>
                        </td>

                        <td>
                            <?= $row->chief ? $row->chief->fullname : '-' ?>
                        </td>

                        <td>
                            <?= $row->manager ? $row->manager->fullname : '-' ?>
                        </td>
                        <td>
                            <?= Html::a(
                                'View',
                                ['view', 'id' => $row->id],
                                ['class' => 'btn btn-sm btn-primary']
                            ) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>