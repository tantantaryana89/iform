<?php

use yii\helpers\Html;

/** @var $instance app\models\ChecksheetInstance */
/** @var $template app\models\FormTemplate */
/** @var $items array */
/** @var $answers app\models\ChecksheetAnswer[] */

$this->title = 'Checksheet Terisi';
?>

<style>
.table-checksheet {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
}
.table-checksheet th,
.table-checksheet td {
    border: 1px solid #000;
    padding: 6px;
}
.table-checksheet th {
    text-align: center;
    background: #f2f2f2;
}
.text-center {
    text-align: center;
}
</style>

<h3 class="text-center">START CHECK SHEET</h3>

<table width="100%" style="margin-bottom:10px; font-size:12px;">
    <tr>
        <td><strong>Mesin</strong></td>
        <td><?= Html::encode($instance->mesin->nama_mesin ?? '-') ?></td>
        <td><strong>Tanggal</strong></td>
        <td><?= Html::encode($instance->tanggal) ?></td>
    </tr>
    <tr>
        <td><strong>Shift</strong></td>
        <td><?= Html::encode($instance->shift) ?></td>
        <td><strong>Operator</strong></td>
        <td><?= Html::encode($instance->operator_id ?? '-') ?></td>
    </tr>
</table>

<table class="table-checksheet">
    <thead>
        <tr>
            <th width="40">No</th>
            <th>Item Check</th>
            <th width="200">Standard</th>
            <th width="120">Cara</th>
            <th width="60">Hasil</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($items as $item): ?>
        <?php
            $answer = $answers[$item['item_id']] ?? null;
            $checked = $answer && (int)$answer->value === 1;
            $conditions = is_array($item['conditions'] ?? null) ? $item['conditions'] : [];
        ?>
        <tr>
            <td class="text-center">
                <?= Html::encode($item['no']) ?>
            </td>
            <td>
                <?php if (!empty($item['section'])): ?>
                    <div style="font-size:11px;color:#555;">
                        <?= Html::encode($item['section']) ?>
                    </div>
                <?php endif; ?>
                <?= Html::encode($item['label']) ?>
            </td>
            <td>
                <?php if (!empty($conditions)): ?>
                    <?php foreach ($conditions as $condition): ?>
                        <div><?= Html::encode($condition['standard'] ?? '-') ?></div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <?= Html::encode($item['standard'] ?? '-') ?>
                <?php endif; ?>
            </td>
            <td class="text-center">
                <?php if (!empty($conditions)): ?>
                    <?php foreach ($conditions as $condition): ?>
                        <div><?= Html::encode($condition['cara'] ?? '-') ?></div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <?= Html::encode($item['cara'] ?? '-') ?>
                <?php endif; ?>
            </td>
            <td class="text-center" style="font-size:16px;">
                <?= $checked ? '✓' : '' ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<br>

<table width="100%" style="margin-top:30px; font-size:12px;">
    <tr>
        <td width="33%" class="text-center">
            Dibuat Oleh<br><br><br>
            ( Operator )
        </td>
        <td width="33%" class="text-center">
            Diperiksa<br><br><br>
            ( Leader )
        </td>
        <td width="33%" class="text-center">
            Disetujui<br><br><br>
            ( Supervisor )
        </td>
    </tr>
</table>
