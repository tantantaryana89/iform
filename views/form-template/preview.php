<?php
use yii\helpers\Html;
use yii\helpers\Json;

$this->title = "Preview: " . $model->name;
?>

<h1><?= Html::encode($this->title) ?></h1>
<hr>

<?php if ($extension === 'json'): ?>

    <h4>JSON Content:</h4>
    <pre style="background:#111; color:#0f0; padding:15px; border-radius:6px;"><?= 
        Json::encode(json_decode($content), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    ?></pre>

<?php elseif ($extension === 'html'): ?>

    <h4>HTML Preview:</h4>
    <iframe srcdoc="<?= Html::encode($content) ?>" style="width:100%; height:80vh; border:1px solid #ccc; border-radius:6px;"></iframe>

<?php elseif ($extension === 'md'): ?>

    <h4>Markdown Preview:</h4>
    <div style="padding:10px; border:1px solid #ddd; border-radius:6px;">
        <?= \yii\helpers\Markdown::process($content) ?>
    </div>

<?php else: ?>

    <h4>Plain Text:</h4>
    <pre style="white-space:pre-wrap; background:#f8f8f8; padding:15px; border-radius:6px;">
        <?= Html::encode($content) ?>
    </pre>

<?php endif; ?>
