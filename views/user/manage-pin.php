<?php
use yii\helpers\Html;
use yii\helpers\Url;

/** @var \app\models\User $model */
$this->title = 'Kelola PIN: ' . $model->fullname;
$this->params['breadcrumbs'][] = ['label' => 'User Management', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="user-manage-pin card shadow-sm p-3">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">
            <i class="bi bi-key-fill me-2"></i>
            <?= Html::encode($this->title) ?>
        </h3>
        <?= Html::a('<i class="bi bi-arrow-left"></i> Kembali', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php foreach (Yii::$app->session->getAllFlashes() as $type => $message): ?>
        <?php $bsClass = $type === 'error' ? 'danger' : ($type === 'warning' ? 'warning' : 'success'); ?>
        <div class="alert alert-<?= $bsClass ?>"> <?= Html::encode($message) ?> </div>
    <?php endforeach; ?>

    <p class="mb-3">Pilih aksi untuk mengatur PIN user <strong><?= Html::encode($model->fullname) ?></strong>:</p>

    <div>
        <?= Html::beginForm(['manage-pin', 'id' => $model->id], 'post') ?>

        <div class="mb-2 form-check">
            <input class="form-check-input" type="radio" id="type-generate" name="type" value="generate" checked>
            <label class="form-check-label" for="type-generate">Generate PIN acak (6 digit)</label>
        </div>

        <div class="mb-2 form-check">
            <input class="form-check-input" type="radio" id="type-manual" name="type" value="manual">
            <label class="form-check-label" for="type-manual">Atur PIN manual</label>
        </div>

        <div class="mb-3" id="manual-pin-box" style="display:none;">
            <label class="form-label">PIN manual</label>
            <input type="text" name="manual_pin" class="form-control" placeholder="Masukkan PIN (mis. 123456)">
            <div class="form-text">PIN disarankan 4-6 digit angka.</div>
        </div>

        <div class="d-flex gap-2">
            <?= Html::submitButton('Simpan', ['class' => 'btn btn-primary']) ?>
            <?= Html::a('Batal', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
        </div>

        <?= Html::endForm() ?>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
    var els = document.getElementsByName('type');
    var box = document.getElementById('manual-pin-box');
    if (!els || els.length === 0) return;

    // attach listeners using a traditional loop for maximum compatibility
    for (var i = 0; i < els.length; i++) {
        (function(el){
            if (!el.addEventListener) return;
            el.addEventListener('change', function(){
                if (!box) return;
                box.style.display = (this.value === 'manual') ? '' : 'none';
            });
        })(els[i]);
    }

    // set initial visibility based on checked radio (in case of page restore)
    try {
        for (var j = 0; j < els.length; j++) {
            if (els[j].checked) {
                if (box) box.style.display = (els[j].value === 'manual') ? '' : 'none';
                break;
            }
        }
    } catch (e) {
        // ignore
    }
});
</script>
