<?php

namespace app\modules\api\controllers;

use Yii;

class TemplateController extends BaseApiController
{
    public function actionIndex()
  {
      $no_mesin = Yii::$app->request->get('no_mesin');

      if (!$no_mesin) {
          return $this->error('Parameter "no_mesin" wajib diisi.', 422);
      }

      // Cek mesin ada apa tidak
      $mesin = \app\models\DaftarMesin::findOne(['no_mesin' => $no_mesin]);

      if (!$mesin) {
          return $this->error("Mesin dengan no_mesin $no_mesin tidak ditemukan.", 404);
      }

      // Cari mapping template-nya
      $mapping = \app\models\MachineTemplate::findOne(['no_mesin' => $no_mesin]);

      if (!$mapping) {
          return $this->error('Mesin ini belum memiliki template form.', 404);
      }

      // Ambil JSON schema dari tabel form_template
      $template = \app\models\FormTemplate::findOne($mapping->template_id);

      if (!$template) {
          return $this->error("Template dengan ID {$mapping->template_id} tidak ditemukan.", 404);
      }

      if ((string)$template->status !== 'active') {
          return $this->error('Template belum aktif untuk produksi.', 409);
      }

      $mappingValidation = $template->getSchemaValidationSummary();
      if (!$mappingValidation['is_valid']) {
          return $this->error('Template aktif tetapi mapping belum valid untuk produksi.', 409);
      }

      return $this->ok([
          'no_mesin' => $no_mesin,
          'template_id' => $template->id,
          'template_name' => $template->name,
          'schema' => json_decode($template->schema_json, true),
          'mapping_validation' => $mappingValidation,
      ], 'Template siap dipakai');
  }
}
