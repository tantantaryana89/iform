<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use app\models\ChecksheetInstance;
use app\models\FormTemplate;
use app\models\ChecksheetAnswer;

class ChecksheetController extends Controller
{


    /**
     * VIEW FORM TERISI (1 INSTANCE)
     */
    public function actionViewForm($id)
    {
        $instance = ChecksheetInstance::findOne($id);
        if (!$instance) {
            throw new NotFoundHttpException('Checksheet tidak ditemukan');
        }

        $template = FormTemplate::findOne($instance->template_id);
        if (!$template) {
            throw new NotFoundHttpException('Template tidak ditemukan');
        }

        $schema = $template->getSchema();
        $items  = $schema['items'] ?? [];

        $answers = ChecksheetAnswer::find()
            ->where(['instance_id' => $instance->id])
            ->indexBy('item_id')
            ->all();

        // render ke folder form-template (ABSOLUTE PATH)
        return $this->render('//form-template/view-form', [
            'instance' => $instance,
            'template' => $template,
            'items'    => $items,
            'answers'  => $answers,
        ]);
    }

    public function actionPdf($id)
  {
      $instance = ChecksheetInstance::findOne($id);
      if (!$instance) {
          throw new NotFoundHttpException('Checksheet tidak ditemukan');
      }

      $template = FormTemplate::findOne($instance->template_id);
      if (!$template) {
          throw new NotFoundHttpException('Template tidak ditemukan');
      }

      // ===== 1. AMBIL PDF MASTER =====
      $masterPdf = Yii::getAlias('@webroot/' . $template->master_pdf_path);
      if (!is_file($masterPdf)) {
          throw new NotFoundHttpException('PDF master tidak ditemukan');
      }

      // ===== 2. AMBIL SCHEMA + ANSWER =====
      $schema = $template->getSchema();
      $items  = $schema['items'] ?? [];

      $answers = ChecksheetAnswer::find()
          ->where(['instance_id' => $instance->id])
          ->indexBy('item_id')
          ->all();

      // ===== 3. INIT mPDF =====
      $mpdf = new \Mpdf\Mpdf([
          'mode' => 'utf-8',
          'format' => 'A4',
          'margin_left' => 0,
          'margin_right' => 0,
          'margin_top' => 0,
          'margin_bottom' => 0,
      ]);

      // ===== 4. PAKAI PDF MASTER SEBAGAI BACKGROUND =====
      $pageCount = $mpdf->SetSourceFile($masterPdf);

      for ($page = 1; $page <= $pageCount; $page++) {
          $tplId = $mpdf->ImportPage($page);
          $mpdf->AddPage();
          $mpdf->UseTemplate($tplId);

          // ===== 5. TULIS DATA DI ATAS PDF MASTER =====
          foreach ($items as $item) {

              if (empty($item['pdf'])) {
                  continue; // item tidak punya mapping → dilewati
              }

              $itemId = $item['item_id'];
              $ans    = $answers[$itemId] ?? null;

              // hanya isi kalau ada jawaban
              if (!$ans) {
                  continue;
              }

              $value = (int)$ans->value;
              if ($value !== 1) {
                  continue; // hanya ✓ (bisa dikembangkan)
              }

              // pastikan halaman cocok
              if ((int)$item['pdf']['page'] !== $page) {
                  continue;
              }

              $x = (float)$item['pdf']['x'];
              $y = (float)$item['pdf']['y'];

              // ===== 6. TULIS ✓ =====
              $mpdf->SetFont('dejavusans', 'B', 12);
              $mpdf->SetXY($x, $y);
              $mpdf->WriteCell(5, 5, '✓', 0, 0, 'C');
          }
      }

      // ===== 7. OUTPUT PDF =====
      $filename = 'Checksheet_' .
          $instance->mesin->no_mesin . '_' .
          $instance->tanggal . '_S' .
          $instance->shift . '.pdf';

      return $this->asPdfResponse($mpdf, $filename);
  }

    /**
     * DUMMY DATA (DEBUG ONLY)
     * JANGAN DIPAKAI PRODUKSI
     */
    public function actionDummy()
{
    $template = FormTemplate::find()->one();
    if (!$template) {
        return 'Template belum ada';
    }

    $instance = new ChecksheetInstance();
    $instance->template_id = $template->id;
    $instance->mesin_id    = $template->mesin_id;
    $instance->tanggal     = date('Y-m-d');
    $instance->shift       = 1;
    $instance->operator_id = 'OP-TEST';
    $instance->status      = 'submitted';

    if (!$instance->save()) {
        return $instance->errors;
    }

    $schema = $template->getSchema();
    $items  = $schema['items'] ?? [];

    foreach ($items as $item) {

        // item_id di schema = item_code (contoh: CHK-001)
        if (empty($item['item_id'])) {
            continue;
        }

        $checksheetItem = \app\models\ChecksheetItem::find()
            ->where(['item_code' => $item['item_id']])
            ->one();

        if (!$checksheetItem) {
            continue;
        }

        $ans = new ChecksheetAnswer();
        $ans->instance_id = $instance->id;
        $ans->item_id     = $checksheetItem->id; // ✅ FK BENAR
        $ans->value       = 1;                    // ✓
        $ans->save(false);
    }

    return $this->redirect(['view-form', 'id' => $instance->id]);
}

    protected function responsePdf($mpdf, $filename)
  {
      Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
      Yii::$app->response->headers->add('Content-Type', 'application/pdf');
      Yii::$app->response->headers->add(
          'Content-Disposition',
          'inline; filename="' . $filename . '"'
      );

      return $mpdf->Output($filename, \Mpdf\Output\Destination::STRING_RETURN);
  }
  protected function asPdfResponse(\Mpdf\Mpdf $mpdf, $filename)
  {
      Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
      Yii::$app->response->headers->add('Content-Type', 'application/pdf');
      Yii::$app->response->headers->add(
          'Content-Disposition',
          'inline; filename="' . $filename . '"'
      );

      return $mpdf->Output($filename, \Mpdf\Output\Destination::STRING_RETURN);
  }

  public function actionExcel($id)
{
    $instance = ChecksheetInstance::findOne($id);
    if (!$instance) {
        throw new NotFoundHttpException('Checksheet tidak ditemukan');
    }

    $template = FormTemplate::findOne($instance->template_id);
    if (!$template) {
        throw new NotFoundHttpException('Template tidak ditemukan');
    }

    // ===== 1. LOAD EXCEL MASTER =====
    $masterExcel = Yii::getAlias('@webroot/' . $template->source_file);
    if (!is_file($masterExcel)) {
        throw new NotFoundHttpException('Excel master tidak ditemukan');
    }

    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($masterExcel);

    // ===== 2. SCHEMA =====
    $schema = $template->getSchema();
    $items  = $schema['items'] ?? [];

    // DEBUG (boleh dihapus nanti)
    Yii::debug(count($items), 'excel.debug.item_count');

    // ===== 3. ANSWERS (KEY = item_code) =====
    $answers = ChecksheetAnswer::find()
        ->joinWith('item')
        ->where(['instance_id' => $instance->id])
        ->indexBy('item.item_code') // CHK-001
        ->all();

    // ===== 4. HEADER =====
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('C5', $instance->mesin->nama_mesin ?? '');
    $sheet->setCellValue('C6', $instance->tanggal);
    $sheet->setCellValue('F6', $instance->shift);
    $sheet->setCellValue('F5', $instance->operator_id);

    // ===== 5. ISI ITEM =====
    foreach ($items as $item) {

        if (empty($item['excel'])) {
            continue;
        }

        $itemCode = $item['item_id']; // CHK-001
        $ans = $answers[$itemCode] ?? null;

        if (!$ans || (int)$ans->value !== 1) {
            continue;
        }

        $sheetName = $item['excel']['sheet'] ?? null;
        $cell      = $item['excel']['cell'] ?? null;

        if (!$sheetName || !$cell) {
            continue;
        }

        $sheetTarget = $spreadsheet->getSheetByName($sheetName);
        if (!$sheetTarget) {
            continue;
        }

        $sheetTarget->setCellValue($cell, '✓');
    }

    // ===== 6. SAVE & DOWNLOAD =====
    $exportDir = Yii::getAlias('@runtime/export');
    if (!is_dir($exportDir)) {
        mkdir($exportDir, 0777, true);
    }

    $filename = 'Checksheet_' .
        $instance->mesin->no_mesin . '_' .
        $instance->tanggal . '_S' .
        $instance->shift . '.xlsx';

    $tmpFile = $exportDir . '/' . $filename;

    (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save($tmpFile);

    return Yii::$app->response->sendFile($tmpFile, $filename);
}

}
