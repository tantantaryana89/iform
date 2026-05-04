<?php
use yii\helpers\Html;
?>

<h2 style="text-align:center;">CHECKSHEET RESULT</h2>

<table border="1" cellpadding="6" cellspacing="0" width="100%">
    <tr>
        <th>Mesin</th>
        <th>Shift</th>
        <th>Tanggal</th>
    </tr>
    <tr>
        <td><?= Html::encode($model->mesin) ?></td>
        <td><?= Html::encode($model->shift) ?></td>
        <td><?= Html::encode($model->submitted_at) ?></td>
    </tr>
</table>

<br>

<table border="1" cellpadding="5" cellspacing="0" width="100%">
    <thead>
        <tr style="background:#eee;">
            <th style="width:5%">No</th>
            <th>Item</th>
            <th style="width:20%">Hasil</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($model->items as $i => $item): ?>
        <tr>
            <td><?= $i + 1 ?></td>
            <td><?= Html::encode($item->item->label ?? '-') ?></td>
            <td style="text-align:center;">
                <?= Html::encode($item->raw_value) ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>