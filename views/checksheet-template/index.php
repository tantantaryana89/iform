<?php
use yii\helpers\Html;

/** @var $this yii\web\View */
/** @var $templates app\models\ChecksheetTemplate[] */

$this->title = 'Form Builder (Legacy)';
?>

<h1><?= Html::encode($this->title) ?></h1>

<div class="alert alert-warning">
    <strong>Perhatian:</strong><br>
    Halaman ini adalah <b>builder manual versi lama</b>. Template yang dibuat di sini mengikuti alur lama
    dan langsung terikat ke mesin saat create/edit.<br>
    Untuk alur aktif yang dipakai Android saat ini, gunakan menu <b>Form Template Aktif</b> lalu assign ke mesin di halaman <b>Daftar Mesin</b>.
</div>

<p>
    <?= Html::a('+ Buat Form Manual', ['create'], ['class' => 'btn btn-outline-warning']) ?>
</p>

<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Nama</th>
            <th>Versi</th>
            <th>Status</th>
            <th width="200">Aksi</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($templates as $t): ?>
        <tr>
            <td><?= Html::encode($t->name) ?></td>
            <td><?= Html::encode($t->version) ?></td>
            <td><?= Html::encode($t->status) ?></td>
            <td>
                <!-- VIEW -->
                <?= Html::a(
                    'View',
                    ['view', 'id' => $t->id],
                    ['class' => 'btn btn-sm btn-info']
                ) ?>

                <!-- UPDATE -->
                <?= Html::a(
                    'Edit',
                    ['update', 'id' => $t->id],
                    ['class' => 'btn btn-sm btn-warning']
                ) ?>

                <!-- DELETE -->
                <?= Html::a(
                    'Hapus',
                    ['delete', 'id' => $t->id],
                    [
                        'class' => 'btn btn-sm btn-danger',
                        'data' => [
                            'confirm' => 'Yakin ingin menghapus data ini?',
                            'method' => 'post',
                        ],
                    ]
                ) ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
