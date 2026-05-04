<?php
use yii\helpers\Html;

/** @var app\models\ChecksheetTemplate $model */
/** @var app\models\ChecksheetSection[] $sections */
?>

<h1>Preview Checksheet</h1>

<div class="alert alert-warning">
    Ini adalah preview dari <b>builder manual legacy</b>. Template ini berbeda dari template hasil upload Excel
    pada menu <b>Form Template Aktif</b>.
</div>

<div class="alert alert-info">
    <strong><i class="bi bi-info-circle me-1"></i> Cara agar Builder ini bisa digunakan mesin:</strong>
    <ol class="mb-1 mt-1">
        <li>Klik tombol <b>"Buat Form Template dari Builder Ini"</b> di bawah</li>
        <li>Simpan Form Template baru (pilih status <b>Active</b> agar langsung siap)</li>
        <li>Buka halaman <b>Daftar Mesin</b> → Edit mesin yang diinginkan → pilih template baru dari dropdown</li>
    </ol>
    Builder hanya menjadi sumber item. Assignment ke mesin dilakukan melalui <b>Form Template Aktif</b>.
</div>

<div class="mb-3">
    <p><b>Template:</b> <?= Html::encode($model->name) ?></p>
    <p><b>Mesin (legacy, hanya referensi):</b> <?= Html::encode($model->mesin->nama_mesin ?? '-') ?></p>
    <p><b>Versi:</b> <?= Html::encode($model->version) ?></p>
    <p><b>Status:</b> <?= Html::encode($model->status) ?></p>
</div>

<div class="mb-3 d-flex gap-2">
    <?= Html::a(
        '<i class="bi bi-arrow-right-circle me-1"></i> Buat Form Template dari Builder Ini',
        ['/form-template/create', 'source_mode' => 'builder', 'builder_template_id' => $model->id],
        ['class' => 'btn btn-primary']
    ) ?>
    <?= Html::a(
        '<i class="bi bi-pencil me-1"></i> Edit Builder',
        ['update', 'id' => $model->id],
        ['class' => 'btn btn-outline-warning']
    ) ?>
    <?= Html::a(
        '<i class="bi bi-arrow-left me-1"></i> Kembali',
        ['index'],
        ['class' => 'btn btn-outline-secondary']
    ) ?>
</div>

<hr>

<?php if (empty($sections)): ?>
    <p class="text-muted">Belum ada section & item.</p>
<?php endif; ?>

<?php foreach ($sections as $section): ?>
    <h4 class="mt-4">— <?= Html::encode($section->title) ?> —</h4>

    <?php if (empty($section->items)): ?>
        <p class="text-muted">Tidak ada item</p>
    <?php endif; ?>

    <?php foreach ($section->items as $item): ?>

        <div class="border rounded p-3 mb-3">

            <div class="d-flex justify-content-between align-items-start">

                <!-- ===============================
                LEFT: CONTENT
                ================================ -->
                <div class="flex-grow-1 me-3">

                    <div class="form-check mb-1">
                        <input type="checkbox" disabled class="form-check-input">
                        <label class="form-check-label fw-semibold">
                            <?= Html::encode($item->label) ?>
                        </label>
                    </div>

                    <?php
                    $instruction = $item->getInstruction();
                    ?>

                    <?php if (!empty($item->getConditionRows())): ?>
                        <div class="table-responsive mt-2">
                            <table class="table table-sm table-bordered mb-1">
                                <thead>
                                    <tr>
                                        <th>Standar Kondisi</th>
                                        <th>Cara Cek</th>
                                        <th>Frekuensi</th>
                                        <th>Catatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($item->getConditionRows() as $condition): ?>
                                        <tr>
                                            <td><?= Html::encode($condition['standard'] ?? '-') ?></td>
                                            <td><?= Html::encode($condition['cara'] ?? '-') ?></td>
                                            <td><?= Html::encode($condition['frekuensi'] ?? '-') ?></td>
                                            <td><?= Html::encode($condition['note'] ?? '-') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>

                    <small class="text-muted">
                        Shift: <?= Html::encode(implode(', ', $item->getShift())) ?>
                        | Type: <?= Html::encode($item->type) ?>
                    </small>

                </div>

                <!-- ===============================
                RIGHT: SYMBOL (1 / 2)
                ================================ -->
                <div class="d-flex align-items-center flex-shrink-0">

                    <?php if ($item->symbol): ?>
                        <img
                            src="<?= Html::encode($item->symbol->image_path) ?>"
                            width="26"
                            alt="<?= Html::encode($item->symbol->name) ?>"
                            title="<?= Html::encode($item->symbol->name) ?>"
                            class="ms-2"
                        >
                    <?php endif; ?>

                    <?php if ($item->symbol2): ?>
                        <img
                            src="<?= Html::encode($item->symbol2->image_path) ?>"
                            width="26"
                            alt="<?= Html::encode($item->symbol2->name) ?>"
                            title="<?= Html::encode($item->symbol2->name) ?>"
                            class="ms-2"
                        >
                    <?php endif; ?>

                </div>

            </div>

        </div>

    <?php endforeach; ?>
<?php endforeach; ?>

<hr>

<p>
    <?= Html::a('Kembali Edit', ['update', 'id' => $model->id], [
        'class' => 'btn btn-secondary'
    ]) ?>
</p>
