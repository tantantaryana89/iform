<h1>Buat Checksheet Manual (Legacy)</h1>

<div class="alert alert-warning">
    <strong>Catatan:</strong><br>
    Form di halaman ini dibuat manual satu per satu dan memakai relasi mesin versi lama.<br>
    Jika tujuan Anda adalah template form untuk Android/runtime aktif, gunakan menu <b>Form Template Aktif</b>.
</div>

<?= $this->render('_form', [
    'model' => $model,
    'mesinList' => $mesinList,
]) ?>
