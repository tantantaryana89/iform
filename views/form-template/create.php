<?php

use yii\widgets\ActiveForm;
use yii\helpers\Html;

/** @var app\models\FormTemplate $model */
/** @var array $builderTemplates */

$this->title = 'Buat Form Template';
?>

<h3><?= Html::encode($this->title) ?></h3>

<div class="alert alert-primary">
    <strong>📋 Alur Pembuatan Template:</strong>
    <ol class="mb-0">
        <li>Upload file Excel asli (untuk referensi struktur cell/layout)</li>
        <li>Pilih Form Builder yang sudah dibuat (sumber item checklist)</li>
        <li>Sistem akan menampilkan halaman mapping untuk menghubungkan item → cell Excel</li>
        <li>Setelah mapping selesai, template bisa diaktifkan dan dipakai di Android</li>
    </ol>
</div>

<div class="card shadow-sm p-4">
    <?php $form = ActiveForm::begin([
        'options' => ['enctype' => 'multipart/form-data']
    ]) ?>

    <div class="row">
        <div class="col-md-8">
            <?= $form->field($model, 'name')->textInput([
                'placeholder' => 'Contoh: BRACKET_AUTO_01 v2',
                'maxlength' => 255,
            ]) ?>
        </div>
    </div>

    <hr>

    <h5 class="mb-3"><i class="bi bi-file-earmark-excel"></i> 1. Upload File Excel Asli</h5>
    
    <div class="row">
        <div class="col-md-8">
            <?= $form->field($model, 'file')->fileInput([
                'accept' => '.xlsx',
                'required' => true,
            ]) ?>
            
            <div class="alert alert-info small">
                <strong>Catatan:</strong>
                <ul class="mb-0 ps-3">
                    <li>Upload file Excel yang akan dipakai untuk tracking hasil check di lapangan</li>
                    <li>Format: <code>.xlsx</code> (Microsoft Excel 2007+)</li>
                    <li>File akan disimpan sebagai referensi untuk mapping cell</li>
                    <li>Anda bisa download file ini nanti dari halaman template list</li>
                </ul>
            </div>
        </div>
    </div>

    <hr>

    <h5 class="mb-3"><i class="bi bi-list-check"></i> 2. Pilih Form Builder (Sumber Item)</h5>
    
    <div class="row">
        <div class="col-md-8">
            <div class="mb-3">
                <label class="form-label"><strong>Template Form Builder *</strong></label>
                <?= Html::dropDownList('builder_template_id', null, 
                    ['' => '-- Pilih Form Builder --'] + $builderTemplates, 
                    [
                        'class' => 'form-select',
                        'id' => 'builder-template-id',
                        'required' => true,
                    ]
                ) ?>
                <small class="text-muted d-block mt-2">
                    Pilih Form Builder yang sudah dibuat di halaman Checksheet Builder.
                    Semua section dan item dari builder ini akan menjadi struktur form.
                </small>
            </div>
        </div>
    </div>

    <div class="alert alert-success small">
        <strong>✓ Catatan:</strong>
        <ul class="mb-0 ps-3">
            <li>Item checklist wajib sudah didefinisikan di Form Builder</li>
            <li>Setiap item akan di-mapping ke cell Excel untuk tracking hasil</li>
            <li>Mapping bisa diubah di halaman preview/editing</li>
        </ul>
    </div>

    <div class="form-group mt-4">
        <?= Html::submitButton(
            '<i class="bi bi-upload me-1"></i> Lanjut ke Mapping',
            ['class' => 'btn btn-primary btn-lg']
        ) ?>
        <?= Html::a('Batal', ['index'], [
            'class' => 'btn btn-secondary btn-lg'
        ]) ?>
    </div>

    <?php ActiveForm::end() ?>
</div>
