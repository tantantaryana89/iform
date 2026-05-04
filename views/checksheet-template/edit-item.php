<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;

/** @var $item app\models\ChecksheetItem */
/** @var $symbols app\models\ChecksheetSymbol[] */

$this->title = 'Edit Item Checksheet';
?>

<h3><?= Html::encode($this->title) ?></h3>

<?php $form = ActiveForm::begin(); ?>

<hr>

<!-- ===============================
LABEL
=============================== -->
<?= $form->field($item, 'label')->textInput() ?>

<!-- ===============================
TYPE
=============================== -->
<?= $form->field($item, 'type')->dropDownList([
    'checklist' => 'Check (Centang)',
    'number'    => 'Angka',
    'text_input' => 'Input Teks',
], ['prompt' => '-- Pilih Type --']) ?>

<hr>

<!-- ===============================
SIMBOL
=============================== -->
<h4>Simbol</h4>

<div class="row">
    <!-- SIMBOL UTAMA -->
    <div class="col-md-6">
        <?= $form->field($item, 'symbol_id')->dropDownList(
            ArrayHelper::map($symbols, 'id', 'name'),
            [
                'prompt' => '-- Tanpa Simbol --',
                'class'  => 'form-select symbol-select',
                'data-preview' => '#symbol-preview-1',
            ]
        ) ?>

        <div id="symbol-preview-1" class="mt-2">
            <?php if ($item->symbol): ?>
                <img src="<?= $item->symbol->image_path ?>" width="36">
            <?php endif ?>
        </div>
    </div>

    <!-- SIMBOL TAMBAHAN -->
    <div class="col-md-6">
        <?= $form->field($item, 'symbol_id_2')->dropDownList(
            ArrayHelper::map($symbols, 'id', 'name'),
            [
                'prompt' => '-- Simbol Tambahan --',
                'class'  => 'form-select symbol-select',
                'data-preview' => '#symbol-preview-2',
            ]
        ) ?>

        <div id="symbol-preview-2" class="mt-2">
            <?php if ($item->symbol2 ?? null): ?>
                <img src="<?= $item->symbol2->image_path ?>" width="36">
            <?php endif ?>
        </div>
    </div>
</div>

<hr>

<!-- ===============================
SHIFT
=============================== -->
<h4>Shift</h4>

<?php $shiftSelected = $item->getShiftArray(); ?>

<div class="d-flex gap-3">
    <?php foreach ([1,2,3] as $shift): ?>
        <label>
            <input type="checkbox" name="shift[]" value="<?= $shift ?>"
                <?= in_array((string)$shift, $shiftSelected) ? 'checked' : '' ?>>
            Shift <?= $shift ?>
        </label>
    <?php endforeach ?>
</div>

<hr>

<!-- ===============================
INSTRUCTION
=============================== -->
<?php $instruction = $item->getInstruction(); ?>
<?php $conditionRows = $item->getConditionRows(); ?>

<h4>Instruksi</h4>

<div class="alert alert-info">
    Satu item bisa memiliki beberapa kondisi cek. Setiap baris di bawah mewakili satu kondisi lengkap:
    standar, cara cek, frekuensi, dan catatan.
</div>

<div class="table-responsive">
    <table class="table table-bordered align-middle" id="condition-table">
        <thead>
            <tr>
                <th style="width: 25%;">Standar Kondisi</th>
                <th style="width: 25%;">Cara Cek</th>
                <th style="width: 20%;">Frekuensi</th>
                <th>Catatan</th>
                <th style="width: 1%;"></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($conditionRows as $index => $condition): ?>
                <tr>
                    <td><input type="text" name="conditions[<?= $index ?>][standard]" class="form-control" value="<?= Html::encode($condition['standard'] ?? '') ?>" placeholder="Contoh: Bersih dari kotoran"></td>
                    <td><input type="text" name="conditions[<?= $index ?>][cara]" class="form-control" value="<?= Html::encode($condition['cara'] ?? '') ?>" placeholder="Contoh: Lihat & bersihkan"></td>
                    <td><input type="text" name="conditions[<?= $index ?>][frekuensi]" class="form-control" value="<?= Html::encode($condition['frekuensi'] ?? '') ?>" placeholder="Contoh: 1x / shift"></td>
                    <td><input type="text" name="conditions[<?= $index ?>][note]" class="form-control" value="<?= Html::encode($condition['note'] ?? '') ?>" placeholder="Catatan tambahan"></td>
                    <td><button type="button" class="btn btn-sm btn-outline-danger remove-condition">Hapus</button></td>
                </tr>
            <?php endforeach; ?>

            <?php if (empty($conditionRows)): ?>
                <tr>
                    <td><input type="text" name="conditions[0][standard]" class="form-control" placeholder="Contoh: Bersih dari kotoran"></td>
                    <td><input type="text" name="conditions[0][cara]" class="form-control" placeholder="Contoh: Lihat & bersihkan"></td>
                    <td><input type="text" name="conditions[0][frekuensi]" class="form-control" placeholder="Contoh: 1x / shift"></td>
                    <td><input type="text" name="conditions[0][note]" class="form-control" placeholder="Catatan tambahan"></td>
                    <td><button type="button" class="btn btn-sm btn-outline-danger remove-condition">Hapus</button></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<button type="button" class="btn btn-outline-primary btn-sm" id="add-condition-row">Tambah Kondisi</button>

<hr>

<!-- ===============================
SUBMIT
=============================== -->
<div class="form-group">
    <?= Html::submitButton('Simpan Item', ['class' => 'btn btn-success']) ?>
    <?= Html::a('Kembali', ['update', 'id' => $item->template_id], ['class' => 'btn btn-secondary']) ?>
    <?= Html::a('Hapus Item', ['delete-item', 'id' => $item->id], [
        'class' => 'btn btn-danger',
        'data' => [
            'method' => 'post',
            'confirm' => 'Hapus item ini dari builder?',
        ],
    ]) ?>
</div>

<?php ActiveForm::end(); ?>

<!-- ===============================
JS PREVIEW SIMBOL (REUSABLE)
=============================== -->
<script>
const symbolMap = <?= json_encode(
    ArrayHelper::map($symbols, 'id', 'image_path')
) ?>;

document.querySelectorAll('.symbol-select').forEach(select => {
    select.addEventListener('change', function () {
        const preview = document.querySelector(this.dataset.preview);
        const img = symbolMap[this.value];

        preview.innerHTML = img
            ? '<img src="' + img + '" width="36">'
            : '';
    });
});

const conditionTableBody = document.querySelector('#condition-table tbody');
const addConditionButton = document.getElementById('add-condition-row');

function nextConditionIndex() {
    return conditionTableBody.querySelectorAll('tr').length;
}

function buildConditionRow(index) {
    const row = document.createElement('tr');
    row.innerHTML = `
        <td><input type="text" name="conditions[${index}][standard]" class="form-control" placeholder="Contoh: Bersih dari kotoran"></td>
        <td><input type="text" name="conditions[${index}][cara]" class="form-control" placeholder="Contoh: Lihat & bersihkan"></td>
        <td><input type="text" name="conditions[${index}][frekuensi]" class="form-control" placeholder="Contoh: 1x / shift"></td>
        <td><input type="text" name="conditions[${index}][note]" class="form-control" placeholder="Catatan tambahan"></td>
        <td><button type="button" class="btn btn-sm btn-outline-danger remove-condition">Hapus</button></td>
    `;

    return row;
}

addConditionButton.addEventListener('click', function () {
    conditionTableBody.appendChild(buildConditionRow(nextConditionIndex()));
});

conditionTableBody.addEventListener('click', function (event) {
    if (!event.target.classList.contains('remove-condition')) {
        return;
    }

    const rows = conditionTableBody.querySelectorAll('tr');
    if (rows.length === 1) {
        rows[0].querySelectorAll('input').forEach(input => {
            input.value = '';
        });
        return;
    }

    event.target.closest('tr')?.remove();
});
</script>
