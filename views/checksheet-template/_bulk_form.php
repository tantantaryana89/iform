<?php
use yii\widgets\ActiveForm;

$form = ActiveForm::begin([
  'action' => ['checksheet-template/bulk-add', 'template_id'=>$template->id]
]);

echo '<label>Bulk Paste ( | separated )</label>';
echo '<textarea name="bulk_text" class="form-control" rows="5"></textarea>';

echo '<button class="btn btn-warning mt-2">Import Bulk</button>';

ActiveForm::end();
