<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use app\models\ChecksheetSymbol;

/** @var $this yii\web\View */
/** @var $model app\models\ChecksheetTemplate */
/** @var $sections app\models\ChecksheetSection[] */

$this->title = 'Edit Template: ' . $model->name;

// master simbol (untuk preview)
$symbols = ChecksheetSymbol::find()
    ->where(['is_active' => 1])
    ->orderBy(['name' => SORT_ASC])
    ->all();

$symbolMap = ArrayHelper::map($symbols, 'id', 'image_path');
?>

<h3><?= Html::encode($this->title) ?></h3>

<div class="alert alert-warning">
    Anda sedang mengedit <b>template manual legacy</b>. Perubahan di sini mengikuti modul lama,
    bukan alur <b>Form Template Aktif</b> yang dipakai runtime Android saat ini.
</div>

<hr>

<!-- ==================================================
TEMPLATE META
================================================== -->
<?php $form = ActiveForm::begin(); ?>

<?= $form->field($model, 'name')->textInput() ?>
<?= $form->field($model, 'version')->textInput() ?>

<div class="form-group">
    <?= Html::submitButton('Simpan Template', ['class' => 'btn btn-primary']) ?>
</div>

<?php ActiveForm::end(); ?>

<hr>

<!-- ==================================================
SECTION LIST
================================================== -->
<h4>Section</h4>

<?php foreach ($sections as $section): ?>

    <div class="card mb-3">

        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center gap-2">
                <strong><?= Html::encode($section->title) ?></strong>
                <?= Html::a(
                    'Hapus Section',
                    ['delete-section', 'id' => $section->id],
                    [
                        'class' => 'btn btn-sm btn-outline-danger',
                        'data' => [
                            'method' => 'post',
                            'confirm' => 'Hapus section ini beserta semua item di dalamnya?',
                        ],
                    ]
                ) ?>
            </div>
        </div>

        <div class="card-body">

            <!-- ===============================
            ITEM LIST
            ================================ -->
            <?php foreach ($section->items as $item): ?>

                <div class="border rounded p-2 mb-2">

                    <div class="d-flex justify-content-between align-items-center">

                        <!-- LEFT: ICON + LABEL -->
                        <div class="d-flex align-items-center">

                            <!-- ICONS -->
                            <div class="d-flex align-items-center me-2">

                                <?php if ($item->symbol): ?>
                                    <img src="<?= $item->symbol->image_path ?>"
                                         width="22"
                                         title="Simbol Utama"
                                         style="margin-right:4px">
                                <?php endif; ?>

                                <?php if ($item->symbol2): ?>
                                    <img src="<?= $item->symbol2->image_path ?>"
                                         width="22"
                                         title="Simbol Kedua"
                                         style="margin-right:6px">
                                <?php endif; ?>

                            </div>

                            <!-- LABEL & META -->
                            <div>
                                <strong><?= Html::encode($item->label) ?></strong><br>
                                <small class="text-muted">
                                    Shift: <?= implode(',', $item->getShiftArray()) ?>
                                    | Type: <?= Html::encode($item->type) ?>
                                    | Kondisi: <?= count($item->getConditionRows()) ?>
                                </small>
                            </div>

                        </div>

                        <!-- RIGHT: ACTION -->
                        <div class="d-flex gap-2">
                            <?= Html::a(
                                'Edit',
                                ['edit-item', 'id' => $item->id],
                                ['class' => 'btn btn-sm btn-outline-primary']
                            ) ?>
                            <?= Html::a(
                                'Hapus',
                                ['delete-item', 'id' => $item->id],
                                [
                                    'class' => 'btn btn-sm btn-outline-danger',
                                    'data' => [
                                        'method' => 'post',
                                        'confirm' => 'Hapus item ini dari builder?',
                                    ],
                                ]
                            ) ?>
                        </div>

                    </div>

                </div>

            <?php endforeach; ?>

            <!-- ===============================
            ADD ITEM
            ================================ -->
            <?php $itemForm = ActiveForm::begin([
                'action' => ['add-item', 'template_id' => $model->id, 'section_id' => $section->id],
                'method' => 'post'
            ]); ?>

            <div class="input-group mt-2">
                <input type="text"
                       name="label"
                       class="form-control"
                       placeholder="Tambah item baru...">
                <button class="btn btn-success" type="submit">+</button>
            </div>

            <?php ActiveForm::end(); ?>

        </div>
    </div>

<?php endforeach; ?>

<!-- ==================================================
ADD SECTION
================================================== -->
<h4>Tambah Section</h4>

<?php $sectionForm = ActiveForm::begin([
    'action' => ['add-section', 'template_id' => $model->id],
    'method' => 'post'
]); ?>

<div class="input-group mb-3">
    <input type="text"
           name="title"
           class="form-control"
           placeholder="Judul section baru">
    <button class="btn btn-success" type="submit">+</button>
</div>

<?php ActiveForm::end(); ?>

<hr>

<?= Html::a('Kembali ke daftar template', ['index'], ['class' => 'btn btn-secondary']) ?>
