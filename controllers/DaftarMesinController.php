<?php

namespace app\controllers;

use Yii;
use app\models\DaftarMesin;
use app\models\DaftarMesinSearch;
use app\models\FormTemplate;
use app\models\MachineTemplate;
use app\models\ChecksheetTemplate;
use app\models\ChecksheetSection;
use app\models\ChecksheetItem;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\db\Query;

class DaftarMesinController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['delete' => ['POST']],
            ],
        ];
    }

    public function actionIndex()
    {
        $searchModel  = new DaftarMesinSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', compact('searchModel', 'dataProvider'));
    }

    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    public function actionCreate()
    {
        $model = new DaftarMesin();

        if ($model->load(Yii::$app->request->post())) {
            $templateId = Yii::$app->request->post('template_id');

            // Auto-konversi jika user memilih Form Builder langsung
            if (str_starts_with((string)$templateId, 'builder:')) {
                $builderId = (int)substr((string)$templateId, 8);
                $resolved = $this->ensureFormTemplateFromBuilder($builderId);
                if ($resolved === false) {
                    Yii::$app->session->setFlash('danger', 'Gagal membuat Form Template dari Form Builder. Pastikan builder sudah punya section dan item.');
                    return $this->render('create', ['model' => $model]);
                }
                $templateId = $resolved;
            }

            if (!$this->validateTemplateAssignment($templateId)) {
                return $this->render('create', [
                    'model' => $model,
                ]);
            }

            if (!$model->save()) {
                return $this->render('create', [
                    'model' => $model,
                ]);
            }

            // QR Code
            $model->refresh();
            $model->generateQr();

            // Simpan / update / hapus mapping template mesin sesuai input
            $this->syncMachineTemplateMapping($model->no_mesin, $templateId, null);

            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $oldNoMesin = (string)$model->no_mesin;

        if ($model->load(Yii::$app->request->post())) {
            $templateId = Yii::$app->request->post('template_id');

            // Auto-konversi jika user memilih Form Builder langsung
            if (str_starts_with((string)$templateId, 'builder:')) {
                $builderId = (int)substr((string)$templateId, 8);
                $resolved = $this->ensureFormTemplateFromBuilder($builderId);
                if ($resolved === false) {
                    Yii::$app->session->setFlash('danger', 'Gagal membuat Form Template dari Form Builder. Pastikan builder sudah punya section dan item.');
                    return $this->render('update', ['model' => $model]);
                }
                $templateId = $resolved;
            }

            if (!$this->validateTemplateAssignment($templateId)) {
                return $this->render('update', [
                    'model' => $model,
                ]);
            }

            if (!$model->save()) {
                return $this->render('update', [
                    'model' => $model,
                ]);
            }

            // regenerate QR jika no_mesin berubah (opsional)
            $model->refresh();
            $model->generateQr();

            // Sinkronkan mapping jika no_mesin berubah / template diganti / template dikosongkan
            $this->syncMachineTemplateMapping($model->no_mesin, $templateId, $oldNoMesin);

            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionRegenerateQr($id)
    {
        $model = $this->findModel($id);
        $model->generateQr();

        Yii::$app->session->setFlash('success', 'QR Code berhasil di-generate ulang.');
        return $this->redirect(['view', 'id' => $id]);
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $noMesin = (string)$model->no_mesin;

        // Cek relasi assignment template ke mesin ini
        $mappingCount = (new Query())
            ->from('machine_template')
            ->where(['no_mesin' => $noMesin])
            ->count();

        // Cek histori result mesin ini
        $resultCount = 0;
        if (Yii::$app->db->schema->getTableSchema('form_result', true) !== null) {
            $resultCount = (new Query())
                ->from('form_result')
                ->where(['no_mesin' => $noMesin])
                ->count();
        }

        // Jika masih punya histori/assignment, jangan hard delete: ubah jadi non-aktif.
        if ((int)$mappingCount > 0 || (int)$resultCount > 0) {
            $model->status = 'inactive';
            $model->updated_at = time();
            $model->save(false, ['status', 'updated_at']);

            Yii::$app->session->setFlash(
                'warning',
                'Mesin tidak dihapus permanen karena masih punya relasi data '
                . '(mapping: ' . (int)$mappingCount . ', result: ' . (int)$resultCount . '). '
                . 'Status mesin diubah menjadi Non-Aktif.'
            );
            return $this->redirect(['index']);
        }

        MachineTemplate::deleteAll(['no_mesin' => $noMesin]);
        $model->delete();

        Yii::$app->session->setFlash('success', 'Mesin berhasil dihapus.');
        return $this->redirect(['index']);
    }

    /**
     * Temukan atau buat Form Template aktif dari Form Builder (checksheet_template).
     * Mengembalikan form_template.id, atau false jika gagal.
     */
    private function ensureFormTemplateFromBuilder(int $builderId): int|false
    {
        // Cari form_template aktif yang sudah di-generate dari builder ini
        $existing = FormTemplate::find()
            ->where(['status' => 'active'])
            ->andWhere(['like', 'schema_json', '"builder_template_id":' . $builderId . ','])
            ->one();
        if ($existing !== null) {
            return (int)$existing->id;
        }

        // Cari draft dan aktifkan
        $draft = FormTemplate::find()
            ->where(['status' => 'draft'])
            ->andWhere(['like', 'schema_json', '"builder_template_id":' . $builderId . ','])
            ->one();
        if ($draft !== null) {
            $draft->status = 'active';
            $draft->updated_at = time();
            $draft->save(false, ['status', 'updated_at']);
            return (int)$draft->id;
        }

        // Buat baru dari builder
        $builderTemplate = ChecksheetTemplate::findOne($builderId);
        if ($builderTemplate === null) {
            return false;
        }

        $sections = ChecksheetSection::find()
            ->where(['template_id' => $builderId])
            ->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC])
            ->all();
        if (empty($sections)) {
            return false;
        }

        $items = [];
        $itemCounter = 1;
        $usedRows = [];

        foreach ($sections as $section) {
            $sectionItems = ChecksheetItem::find()
                ->where(['section_id' => $section->id])
                ->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC])
                ->all();

            foreach ($sectionItems as $sItem) {
                $instruction = $sItem->getInstruction();
                $standard = implode(', ', array_filter($instruction['standard'] ?? [])) ?: '-';
                $cara = implode(', ', array_filter($instruction['cara'] ?? [])) ?: '-';

                $rowNo = (int)($sItem->excel_row_base ?? 0) > 0
                    ? (int)$sItem->excel_row_base
                    : ((int)($sItem->sort_order ?? 0) > 0 ? (int)$sItem->sort_order : $itemCounter);

                while (isset($usedRows[$rowNo])) {
                    $rowNo++;
                }
                $usedRows[$rowNo] = 1;

                $items[] = [
                    'item_id'            => 'CHK-' . str_pad((string)$itemCounter, 3, '0', STR_PAD_LEFT),
                    'builder_item_code'  => (string)$sItem->item_code,
                    'no'                 => $itemCounter,
                    'section'            => (string)$section->title,
                    'label'              => trim((string)$sItem->label),
                    'standard'           => $standard,
                    'cara'               => $cara,
                    'source_row'         => $rowNo,
                    'required'           => false,
                    'excel'              => [
                        'sheet'            => 'Sheet1',
                        'row'              => $rowNo,
                        'source_cell'      => 'B' . $rowNo,
                        'cell'             => 'E' . $rowNo,
                        'mapping_strategy' => 'auto_from_builder',
                    ],
                    'frequency'  => 'per_shift',
                    'input_type' => 'check',
                ];
                $itemCounter++;
            }
        }

        if (empty($items)) {
            return false;
        }

        $schema = [
            'version'              => 1,
            'generated_at'         => date('Y-m-d H:i:s'),
            'source'               => 'builder',
            'builder_template_id'  => $builderId,
            'builder_template_name' => (string)$builderTemplate->name,
            'items'                => $items,
        ];

        $tpl = new FormTemplate();
        $tpl->name        = (string)$builderTemplate->name . ' [Builder Auto]';
        $tpl->status      = 'active';
        $tpl->version     = 1;
        $tpl->schema_json = json_encode($schema, JSON_UNESCAPED_UNICODE);
        $tpl->source_file = null;
        $tpl->created_at  = time();
        $tpl->updated_at  = time();

        if (!$tpl->save(false)) {
            return false;
        }

        Yii::$app->session->setFlash(
            'success',
            'Form Template "' . $tpl->name . '" berhasil dibuat otomatis dari Form Builder.'
        );

        return (int)$tpl->id;
    }

    private function validateTemplateAssignment($templateId): bool
    {
        if ($templateId === null || $templateId === '') {
            return true;
        }

        $template = FormTemplate::findOne((int)$templateId);
        if (!$template) {
            Yii::$app->session->setFlash('danger', 'Template yang dipilih tidak ditemukan.');
            return false;
        }

        if ((string)$template->status !== 'active') {
            Yii::$app->session->setFlash('danger', 'Template yang dipilih belum aktif.');
            return false;
        }

        $summary = $template->getSchemaValidationSummary();
        if (!$summary['is_valid']) {
            Yii::$app->session->setFlash('danger', 'Template yang dipilih belum valid untuk produksi.');
            return false;
        }

        return true;
    }

    private function syncMachineTemplateMapping(string $newNoMesin, $templateId, ?string $oldNoMesin): void
    {
        if ($oldNoMesin !== null && $oldNoMesin !== '' && $oldNoMesin !== $newNoMesin) {
            // Jika kode mesin berubah, pindahkan mapping lama ke no_mesin baru.
            MachineTemplate::updateAll(['no_mesin' => $newNoMesin], ['no_mesin' => $oldNoMesin]);
        }

        // Jika template dikosongkan, hapus assignment untuk mesin ini.
        if ($templateId === null || $templateId === '') {
            MachineTemplate::deleteAll(['no_mesin' => $newNoMesin]);
            return;
        }

        $rows = MachineTemplate::find()
            ->where(['no_mesin' => $newNoMesin])
            ->orderBy(['id' => SORT_ASC])
            ->all();

        if (empty($rows)) {
            $mapping = new MachineTemplate();
            $mapping->no_mesin = $newNoMesin;
            $mapping->template_id = (int)$templateId;
            $mapping->save(false);
            return;
        }

        $primary = $rows[0];
        $primary->template_id = (int)$templateId;
        $primary->updated_at = time();
        $primary->save(false);

        // Bersihkan duplicate mapping jika pernah terbentuk sebelumnya.
        if (count($rows) > 1) {
            $duplicateIds = [];
            for ($i = 1; $i < count($rows); $i++) {
                $duplicateIds[] = (int)$rows[$i]->id;
            }
            if (!empty($duplicateIds)) {
                MachineTemplate::deleteAll(['id' => $duplicateIds]);
            }
        }
    }

    public function actionExportPdf()
    {
        $models = DaftarMesin::find()->all();

        if (empty($models)) {
            throw new NotFoundHttpException('Data mesin tidak ditemukan.');
        }

        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4-L',
            'margin_left' => 5,
            'margin_right' => 5,
            'margin_top' => 5,
            'margin_bottom' => 5,
        ]);
        $mpdf->SetTitle('QR Mesin');
        $html = $this->renderPartial('pdf-all', [
            'models' => $models
        ]);

        $mpdf->WriteHTML($html);

        $pdfContent = $mpdf->Output('', 'S');
        $fileName = 'QR-Mesin-' . date('Y-m-d') . '.pdf';
        return Yii::$app->response->sendContentAsFile(
            $pdfContent,
            $fileName,
            [
                'mimeType' => 'application/pdf',
                'inline' => true,
            ]
        );
    }

    protected function findModel($id)
    {
        if (($model = DaftarMesin::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Data mesin tidak ditemukan.');
    }
}
