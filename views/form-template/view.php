<?php
use yii\helpers\Html;
?>

<h3>Detail Template</h3>

<p><b>Nama:</b> <?= $model->name ?></p>
<p><b>File:</b> <?= $model->source_file ?></p>

<h4>Schema JSON</h4>
<pre><?= $model->schema_json ?></pre>

<a href="index" class="btn btn-secondary">Back</a>
<br><br>
<a href="delete?id=<?= $model->id ?>" class="btn btn-danger"
   data-confirm="Hapus template ini?"
   data-method="post">Delete</a>
