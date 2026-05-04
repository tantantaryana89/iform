<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\DaftarMesin;
use app\models\FormTemplate;
use app\models\MachineTemplate;
use app\models\ChecksheetTemplate;

/** @var yii\web\View $this */
/** @var app\models\DaftarMesin $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="daftar-mesin-form card shadow-sm p-3">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">
            <i class="bi bi-cpu-fill me-2"></i>
            Data Mesin
        </h3>

        <?= Html::a(
            '<i class="bi bi-arrow-left"></i> Kembali',
            ['index'],
            ['class' => 'btn btn-outline-secondary']
        ) ?>
    </div>

    <?php $form = ActiveForm::begin(); ?>

    <?php
    // Ambil template_id jika sudah ada (untuk update)
    $existingTemplate = MachineTemplate::findOne(['no_mesin' => $model->no_mesin]);
    $selectedTemplate = $existingTemplate ? $existingTemplate->template_id : null;

    $templateOptions = [];
    $activeTemplates = FormTemplate::find()
        ->where(['status' => 'active'])
        ->orderBy(['name' => SORT_ASC, 'id' => SORT_ASC])
        ->all();

    foreach ($activeTemplates as $template) {
        $schema = $template->getSchema();
        $builderName = trim((string)($schema['builder_template_name'] ?? ''));
        $builderPart = $builderName !== '' ? ' | Builder: ' . $builderName : '';
        $filePart = !empty($template->source_file) ? ' | Upload Excel' : ' | Builder Auto';
        $templateOptions[(string)$template->id] = $template->name . $builderPart . $filePart . ' | #' . $template->id;
    }

    if ($selectedTemplate !== null && !isset($templateOptions[(string)$selectedTemplate])) {
        $currentTemplate = FormTemplate::findOne($selectedTemplate);
        if ($currentTemplate) {
            $templateOptions[(string)$selectedTemplate] = $currentTemplate->name . ' | Current Assignment | #' . $currentTemplate->id;
        }
    }

    $builders = ChecksheetTemplate::find()
        ->where(['status' => ['draft', 'active']])
        ->orderBy(['name' => SORT_ASC, 'id' => SORT_ASC])
        ->all();
    $builderOptions = [];
    foreach ($builders as $builder) {
        $builderOptions['builder:' . $builder->id] = $builder->name . ' | Generate Builder Auto | #' . $builder->id;
    }

    $dropdownOptions = [];
    if (!empty($templateOptions)) {
        $dropdownOptions['Form Template Aktif'] = $templateOptions;
    }
        // Builder Auto option dihapus, hanya tampilkan Form Template Aktif
    ?>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'no_mesin')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'nama_mesin')->textInput(['maxlength' => true]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'kategori')->dropDownList(
                DaftarMesin::getKategoriList(),
                ['prompt' => '-- Pilih Kategori --']
            ) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'lokasi')->textInput(['maxlength' => true]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'status')->dropDownList(
                DaftarMesin::getStatusList(),
                ['prompt' => '-- Pilih Status --']
            ) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'vendor')->textInput(['maxlength' => true]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'serial_number')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'tgl_last_maintenance')->input('date') ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'next_maintenance_due')->input('date') ?>
        </div>
    </div>

    <hr>

    <!-- Template / Form Builder Assignment -->
    <div class="row mt-2">
        <div class="col-md-8">
            <label class="form-label fw-semibold">
                <i class="bi bi-file-earmark-text me-1"></i>
                Template Form untuk Mesin
            </label>

            <?= Html::dropDownList(
                'template_id',
                $selectedTemplate !== null ? (string)$selectedTemplate : null,
                $dropdownOptions,
                [
                    'class' => 'form-select',
                    'prompt' => '-- Pilih Form Template Aktif atau Generate Builder Auto --',
                ]
            ) ?>

            <small class="text-muted d-block mt-1">
                <i class="bi bi-info-circle"></i>
                Pilih <b>Form Template Aktif</b> jika ingin mesin memakai template yang jelas versinya dan bisa punya file upload Excel.
                Opsi <b>Generate Builder Auto</b> hanya membuat template otomatis tanpa file upload dan biasanya dipakai sebagai fallback awal.
            </small>
        </div>
    </div>

    <div class="form-group mt-4">
        <?= Html::submitButton(
            '<i class="bi bi-save me-1"></i> Simpan',
            ['class' => 'btn btn-success']
        ) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
