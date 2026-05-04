<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\UploadedFile;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\FormTemplate;
use app\models\ChecksheetTemplate;
use app\models\ChecksheetSection;
use app\models\ChecksheetItem;
use app\models\ChecksheetTemplateMap;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class FormTemplateController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'activate' => ['post'],
                    'update-mapping' => ['post'],
                    'revise' => ['post'],
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    /**
     * ===============================
     * LIST TEMPLATE
     * ===============================
     */
    public function actionIndex()
    {
        $searchModel = new \app\models\FormTemplateSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', compact('searchModel', 'dataProvider'));
    }

    /**
     * ===============================
     * VIEW TEMPLATE
     * ===============================
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $this->syncBuilderSchemaFromSource($model);

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * ===============================
     * DELETE TEMPLATE
     * ===============================
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        try {
            $usage = $this->getTemplateUsageSummary((int)$model->id);
            $totalUsage = $usage['machine_count'] + $usage['instance_count'] + $usage['result_count'] + (int)($usage['checksheet_result_count'] ?? 0);

            // Jika template sudah dipakai di data relasional, jangan hard delete.
            // Ubah menjadi archived agar histori tetap aman dan tidak menabrak FK.
            if ($totalUsage > 0) {
                if ((string)$model->status !== 'archived') {
                    $model->status = 'archived';
                    $model->updated_at = time();
                    $model->save(false, ['status', 'updated_at']);
                }

                Yii::$app->session->setFlash(
                    'warning',
                    'Template tidak dihapus permanen karena masih dipakai data (mesin: '
                    . $usage['machine_count'] . ', instance: '
                    . $usage['instance_count'] . ', result: '
                    . $usage['result_count'] . ', checksheet result: '
                    . (int)($usage['checksheet_result_count'] ?? 0) . '). Status diubah menjadi ARCHIVED.'
                );

                return $this->redirect(['index']);
            }

            if ($model->delete() === false) {
                Yii::$app->session->setFlash('danger', 'Gagal menghapus template.');
                return $this->redirect(['index']);
            }

            Yii::$app->session->setFlash('success', 'Template berhasil dihapus permanen.');
        } catch (\Throwable $e) {
            Yii::error($e->getMessage(), 'template');
            Yii::$app->session->setFlash(
                'danger',
                'Aksi hapus gagal karena template masih dipakai data relasional. Gunakan arsip (ARCHIVED).'
            );
        }

        return $this->redirect(['index']);
    }

    public function actionRevise($id)
    {
        $model = $this->findModel($id);

        $revision = new FormTemplate();
        $revision->name = $this->buildRevisionName((string)$model->name, (int)$model->version + 1);
        $revision->description = $model->description;
        if ($revision->hasAttribute('mesin_id')) {
            $revision->mesin_id = null;
        }
        $revision->source_file = $model->source_file;
        $revision->master_pdf_path = $model->master_pdf_path;
        $revision->schema_json = $this->buildRevisionSchema($model);
        $revision->status = 'draft';
        $revision->version = max(1, (int)$model->version + 1);
        $revision->created_at = time();
        $revision->updated_at = time();

        if (!$revision->save(false)) {
            Yii::$app->session->setFlash('danger', 'Gagal membuat revisi template.');
            return $this->redirect(['preview-form', 'id' => $id]);
        }

        Yii::$app->session->setFlash(
            'success',
            'Revisi template berhasil dibuat sebagai draft v' . (int)$revision->version . '.'
        );

        return $this->redirect(['preview-form', 'id' => $revision->id]);
    }

    private function getTemplateUsageSummary(int $templateId): array
    {
        $db = Yii::$app->db;
        $schema = $db->schema;
        $template = FormTemplate::findOne($templateId);
        $templateSchema = $template ? $template->getSchema() : [];
        $builderTemplateId = (int)($templateSchema['builder_template_id'] ?? 0);

        $machineCount = (int)(new \yii\db\Query())
            ->from('machine_template')
            ->where(['template_id' => $templateId])
            ->count();

        $instanceCount = 0;
        if ($schema->getTableSchema('checksheet_instance', true) !== null) {
            $instanceCount = (int)(new \yii\db\Query())
                ->from('checksheet_instance')
                ->where(['template_id' => $templateId])
                ->count();
        }

        $resultCount = 0;
        if ($schema->getTableSchema('form_result', true) !== null) {
            $resultCount = (int)(new \yii\db\Query())
                ->from('form_result')
                ->where(['template_id' => $templateId])
                ->count();
        }

        $checksheetResultCount = 0;
        if ($builderTemplateId > 0 && $schema->getTableSchema('checksheet_result', true) !== null) {
            $checksheetResultCount = (int)(new \yii\db\Query())
                ->from('checksheet_result')
                ->where(['template_id' => $builderTemplateId])
                ->count();
        }

        return [
            'machine_count' => $machineCount,
            'instance_count' => $instanceCount,
            'result_count' => $resultCount,
            'checksheet_result_count' => $checksheetResultCount,
        ];
    }

    private function getTemplateLockState(FormTemplate $model): array
    {
        $usage = $this->getTemplateUsageSummary((int)$model->id);
        // Assignment ke mesin tidak lagi mengunci edit. Lock hanya saat sudah ada histori hasil.
        $lockedByAssignment = false;
        $lockedByResult = (int)$usage['result_count'] > 0;
        $isLocked = $lockedByAssignment || $lockedByResult;

        $reason = null;
        if ($lockedByResult) {
            $reason = 'Template sudah memiliki histori hasil submit, sehingga menjadi immutable.';
        }

        return [
            'is_locked' => $isLocked,
            'locked_by_assignment' => $lockedByAssignment,
            'locked_by_result' => $lockedByResult,
            'usage' => $usage,
            'reason' => $reason,
        ];
    }

    private function buildRevisionSchema(FormTemplate $model): string
    {
        $schema = $model->getSchema();
        if (!is_array($schema)) {
            $schema = [];
        }

        $schema['revised_from_template_id'] = (int)$model->id;
        $schema['revised_from_version'] = (int)$model->version;
        $schema['revised_at'] = date('Y-m-d H:i:s');

        return json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    private function buildRevisionName(string $currentName, int $newVersion): string
    {
        $baseName = preg_replace('/\s+\[Rev\s+\d+\]$/i', '', trim($currentName));
        $baseName = $baseName !== '' ? $baseName : 'Template';

        return $baseName . ' [Rev ' . $newVersion . ']';
    }

    /**
     * ===============================
     * CREATE / UPLOAD TEMPLATE
     * ===============================
     */
    public function actionCreate()
    {
        $model = new FormTemplate();
        $builderTemplates = $this->getBuilderTemplateOptions();

        if ($model->load(Yii::$app->request->post())) {
            // Wajib: upload file Excel
            $model->file = UploadedFile::getInstance($model, 'file');
            if (!$model->file) {
                Yii::$app->session->setFlash('danger', 'File Excel wajib diupload.');
                return $this->render('create', [
                    'model' => $model,
                    'builderTemplates' => $builderTemplates,
                ]);
            }

            // Wajib: pilih Form Builder
            $builderTemplateId = (int)Yii::$app->request->post('builder_template_id', 0);
            if ($builderTemplateId <= 0) {
                Yii::$app->session->setFlash('danger', 'Pilih Form Builder terlebih dahulu.');
                return $this->render('create', [
                    'model' => $model,
                    'builderTemplates' => $builderTemplates,
                ]);
            }

            // Cek builder template ada
            $builderTemplate = ChecksheetTemplate::findOne($builderTemplateId);
            if (!$builderTemplate) {
                Yii::$app->session->setFlash('danger', 'Form Builder tidak ditemukan.');
                return $this->render('create', [
                    'model' => $model,
                    'builderTemplates' => $builderTemplates,
                ]);
            }

            // Cek builder punya items
            $builderItems = ChecksheetItem::find()
                ->where(['section_id' => ChecksheetSection::find()
                    ->select('id')
                    ->where(['template_id' => $builderTemplateId])])
                ->count();
            if ($builderItems <= 0) {
                Yii::$app->session->setFlash('danger', 'Form Builder belum memiliki item. Tambah item terlebih dahulu.');
                return $this->render('create', [
                    'model' => $model,
                    'builderTemplates' => $builderTemplates,
                ]);
            }

            // Save file Excel
            $realPath = $this->saveUploadedFile($model->file);

            // Parse Excel untuk detect struktur cell
            $excelStructure = $this->detectExcelStructure($realPath);
            if ($excelStructure === false) {
                @unlink($realPath);
                Yii::$app->session->setFlash('danger', 'File Excel tidak bisa dibaca.');
                return $this->render('create', [
                    'model' => $model,
                    'builderTemplates' => $builderTemplates,
                ]);
            }

            // Set default status & version
            $model->status = 'draft';
            $model->version = 1;

            // Simpan source_file path
            $model->source_file = str_replace(
                Yii::getAlias('@webroot') . '/',
                '',
                $realPath
            );

            // Buat schema dari builder + Excel structure
            $schema = $this->buildSchemaFromBuilder($builderTemplateId, $excelStructure);
            if ($schema === false || empty($schema['items'])) {
                @unlink($realPath);
                Yii::$app->session->setFlash('danger', 'Gagal membuat schema dari Form Builder.');
                return $this->render('create', [
                    'model' => $model,
                    'builderTemplates' => $builderTemplates,
                ]);
            }

            $model->schema_json = json_encode(
                $schema,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
            );

            if (!$model->save(false)) {
                @unlink($realPath);
                throw new \RuntimeException('Gagal menyimpan template.');
            }

            Yii::$app->session->setFlash(
                'success',
                'Template berhasil dibuat (status: Draft). Lanjutkan ke halaman mapping untuk menghubungkan item ke cell Excel.'
            );

            return $this->redirect(['preview-form', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
            'builderTemplates' => $builderTemplates,
        ]);
    }

    private function getBuilderTemplateOptions(): array
    {
        $rows = ChecksheetTemplate::find()
            ->select(['id', 'name', 'version', 'status'])
            ->where(['status' => ['draft', 'active']])
            ->orderBy(['updated_at' => SORT_DESC, 'id' => SORT_DESC])
            ->asArray()
            ->all();

        $options = [];
        foreach ($rows as $row) {
            $options[(int)$row['id']] = sprintf(
                '#%d - %s (v%s, %s)',
                (int)$row['id'],
                (string)$row['name'],
                (string)$row['version'],
                (string)$row['status']
            );
        }

        return $options;
    }

    private function parseTemplateFromBuilder(int $builderTemplateId): array|false
    {
        $builderTemplate = ChecksheetTemplate::findOne($builderTemplateId);
        if ($builderTemplate === null) {
            return false;
        }

        $sections = ChecksheetSection::find()
            ->where(['template_id' => $builderTemplateId])
            ->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC])
            ->all();

        if (empty($sections)) {
            return false;
        }

        $mappings = ChecksheetTemplateMap::find()
            ->where(['form_template_id' => $builderTemplateId])
            ->indexBy('item_code')
            ->asArray()
            ->all();

        $items = [];
        $itemCounter = 1;
        $rowCursor = 18;

        foreach ($sections as $section) {
            $sectionItems = ChecksheetItem::find()
                ->where(['section_id' => $section->id])
                ->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC])
                ->all();

            foreach ($sectionItems as $builderItem) {
                $instruction = $builderItem->getInstruction();
                $conditions = $this->normalizeBuilderConditions($builderItem->getConditionRows());
                $standard = $this->firstNonEmptyString($instruction['standard'] ?? []) ?: '-';
                $cara = $this->firstNonEmptyString($instruction['cara'] ?? []) ?: '-';
                $frekuensi = $this->firstNonEmptyString($instruction['frekuensi'] ?? []) ?: 'per_shift';

                if (!empty($conditions)) {
                    $standard = $this->summarizeConditionField($conditions, 'standard');
                    $cara = $this->summarizeConditionField($conditions, 'cara');
                    $frekuensi = $this->summarizeConditionField($conditions, 'frekuensi', 'per_shift');
                }

                $map = $mappings[$builderItem->item_code] ?? null;
                $builderBaseRow = (int)$builderItem->excel_row_base > 0
                    ? (int)$builderItem->excel_row_base
                    : ((int)$builderItem->sort_order > 0 ? (int)$builderItem->sort_order : $itemCounter);
                $rowSpan = max(1, count($conditions)) * 3;

                $mappedCell = strtoupper(trim((string)($map['excel_cell'] ?? '')));
                $mappedSheet = trim((string)($map['sheet_name'] ?? ''));

                if (preg_match('/^[A-Z]+[1-9][0-9]*$/', $mappedCell)) {
                    $targetCell = $mappedCell;
                    $targetRow = $this->extractRowFromCell($targetCell) ?: max(1, $rowCursor);
                } else {
                    $targetRow = max($rowCursor, $builderBaseRow);
                    $targetCell = 'E' . $targetRow;
                }

                $conditionMappings = $this->buildConditionMappings(
                    $conditions,
                    $mappedSheet !== '' ? $mappedSheet : 'Sheet1',
                    $targetCell,
                    $targetRow
                );

                $inputType = 'check';
                if ((string)$builderItem->type === 'number') {
                    $inputType = 'number';
                } elseif ((string)$builderItem->type === 'text_input') {
                    $inputType = 'text';
                }

                $label = trim((string)$builderItem->label);

                $items[] = [
                    'item_id' => 'CHK-' . str_pad((string)$itemCounter, 3, '0', STR_PAD_LEFT),
                    'builder_item_code' => (string)$builderItem->item_code,
                    'no' => (int)($builderItem->sort_order ?: $itemCounter),
                    'section' => (string)$section->title,
                    'label' => $label,
                    'standard' => $standard,
                    'cara' => $cara,
                    'conditions' => $conditionMappings,
                    'source_row' => $targetRow,
                    'required' => $this->inferRequired($label, $standard, $cara),
                    'excel' => [
                        'sheet' => $mappedSheet !== '' ? $mappedSheet : 'Sheet1',
                        'row' => $targetRow,
                        'source_cell' => 'B' . $targetRow,
                        'cell' => $targetCell,
                        'mapping_strategy' => $map ? 'builder_map' : 'builder_default',
                    ],
                    'frequency' => $frekuensi,
                    'input_type' => $inputType,
                ];

                $rowCursor = max($rowCursor, $targetRow) + $rowSpan;
                $itemCounter++;
            }
        }

        if (empty($items)) {
            return false;
        }

        return [
            'version' => 1,
            'generated_at' => date('Y-m-d H:i:s'),
            'source' => 'builder',
            'builder_template_id' => $builderTemplateId,
            'builder_template_name' => (string)$builderTemplate->name,
            'items' => $items,
        ];
    }

    private function firstNonEmptyString(array $values): ?string
    {
        foreach ($values as $value) {
            $text = trim((string)$value);
            if ($text !== '') {
                return $text;
            }
        }

        return null;
    }

    private function normalizeBuilderConditions(array $rows): array
    {
        $conditions = [];

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $condition = [
                'standard' => trim((string)($row['standard'] ?? '')),
                'cara' => trim((string)($row['cara'] ?? '')),
                'frekuensi' => trim((string)($row['frekuensi'] ?? '')),
                'note' => trim((string)($row['note'] ?? '')),
            ];

            if (implode('', $condition) === '') {
                continue;
            }

            $conditions[] = $condition;
        }

        return $conditions;
    }

    private function summarizeConditionField(array $conditions, string $field, string $fallback = '-'): string
    {
        $values = [];
        foreach ($conditions as $condition) {
            $text = trim((string)($condition[$field] ?? ''));
            if ($text !== '') {
                $values[] = $text;
            }
        }

        if (empty($values)) {
            return $fallback;
        }

        return implode(' | ', array_values(array_unique($values)));
    }

    private function buildConditionMappings(array $conditions, string $sheetName, string $baseCell, int $fallbackRow): array
    {
        if (empty($conditions)) {
            return [];
        }

        $normalizedBaseCell = strtoupper(trim($baseCell));
        if (!preg_match('/^([A-Z]+)([1-9][0-9]*)$/', $normalizedBaseCell, $matches)) {
            $baseColumn = 'E';
            $baseRow = $fallbackRow > 0 ? $fallbackRow : 1;
            $normalizedBaseCell = $baseColumn . $baseRow;
        } else {
            $baseColumn = $matches[1];
            $baseRow = (int)$matches[2];
        }

        $maxShiftRows = 3;
        $mappedConditions = [];
        foreach ($conditions as $index => $condition) {
            $row = $baseRow + ($index * $maxShiftRows);
            $condition['excel'] = [
                'sheet' => $sheetName,
                'row' => $row,
                'cell' => $baseColumn . $row,
                'mapping_strategy' => $index === 0 ? 'condition_default' : 'condition_stacked_default',
            ];
            $mappedConditions[] = $condition;
        }

        return $mappedConditions;
    }

    private function extractRowFromCell(string $cell): int
    {
        if (preg_match('/^[A-Z]+([1-9][0-9]*)$/', strtoupper($cell), $matches)) {
            return (int)$matches[1];
        }

        return 0;
    }

    private function syncBuilderSchemaFromSource(FormTemplate $model): void
    {
        $lockState = $this->getTemplateLockState($model);

        if ($lockState['is_locked']) {
            return;
        }

        $schema = $model->getSchema();

        if ((string)($schema['source'] ?? '') !== 'builder') {
            return;
        }

        $builderTemplateId = (int)($schema['builder_template_id'] ?? 0);

        if ($builderTemplateId <= 0) {
            return;
        }

        $latestSchema = $this->parseTemplateFromBuilder($builderTemplateId);

        if ($latestSchema === false || empty($latestSchema['items'])) {
            return;
        }

        /**
         * ==========================================
         * RETAIN EXCEL STRUCTURE
         * ==========================================
         */
        if (
            !isset($latestSchema['excel_structure']) &&
            !empty($schema['excel_structure']) &&
            is_array($schema['excel_structure'])
        ) {
            $latestSchema['excel_structure'] = $schema['excel_structure'];
        }

        /**
         * ==========================================
         * RETAIN META MAPPING (FIX BUG)
         * supaya operator/leader/chief/manager
         * tidak hilang setelah save
         * ==========================================
         */
        if (
            !isset($latestSchema['meta_mapping']) &&
            !empty($schema['meta_mapping']) &&
            is_array($schema['meta_mapping'])
        ) {
            $latestSchema['meta_mapping'] = $schema['meta_mapping'];
        }

        /**
         * fallback daftar sheet
         */
        $sheetNames = [];

        foreach (($schema['items'] ?? []) as $existingItem) {
            $sheet = trim((string)($existingItem['excel']['sheet'] ?? ''));

            if ($sheet !== '') {
                $sheetNames[$sheet] = true;
            }
        }

        if (!empty($sheetNames)) {
            if (
                !isset($latestSchema['excel_structure']) ||
                !is_array($latestSchema['excel_structure'])
            ) {
                $latestSchema['excel_structure'] = [];
            }

            if (empty($latestSchema['excel_structure']['sheets'])) {
                $latestSchema['excel_structure']['sheets'] = array_keys($sheetNames);
            }
        }

        /**
         * retain item mapping
         */
        $existingByCode = [];

        foreach (($schema['items'] ?? []) as $existingItem) {
            $code = trim((string)($existingItem['builder_item_code'] ?? ''));

            if ($code !== '') {
                $existingByCode[$code] = $existingItem;
            }
        }

        foreach ($latestSchema['items'] as &$latestItem) {
            $code = trim((string)($latestItem['builder_item_code'] ?? ''));

            if ($code === '' || !isset($existingByCode[$code])) {
                continue;
            }

            $oldItem = $existingByCode[$code];

            // retain required
            $latestItem['required'] = !empty($oldItem['required']);

            // retain excel mapping
            if (!empty($oldItem['excel']) && is_array($oldItem['excel'])) {
                $latestItem['excel'] = array_merge(
                    $latestItem['excel'] ?? [],
                    $oldItem['excel']
                );
            }

            /**
             * retain condition mapping
             */
            if (
                !empty($oldItem['conditions']) &&
                is_array($oldItem['conditions']) &&
                !empty($latestItem['conditions']) &&
                is_array($latestItem['conditions'])
            ) {
                foreach (
                    $latestItem['conditions'] as $conditionIndex => &$latestCondition
                ) {
                    $oldCondition = $oldItem['conditions'][$conditionIndex] ?? null;

                    if (!is_array($oldCondition)) {
                        continue;
                    }

                    if (
                        !empty($oldCondition['excel']) &&
                        is_array($oldCondition['excel'])
                    ) {
                        $latestCondition['excel'] = array_merge(
                            $latestCondition['excel'] ?? [],
                            $oldCondition['excel']
                        );
                    }
                }

                unset($latestCondition);
            }
        }

        unset($latestItem);

        /**
         * prevent duplicate target
         */
        if (!empty($latestSchema['items'])) {
            [$latestSchema['items']] = $this->ensureUniqueMappingTargets(
                $latestSchema['items']
            );
        }

        /**
         * save only if changed
         */
        $currentJson = json_encode($schema, JSON_UNESCAPED_UNICODE);
        $latestJson = json_encode($latestSchema, JSON_UNESCAPED_UNICODE);

        if ($currentJson === $latestJson) {
            return;
        }

        $model->schema_json = json_encode(
            $latestSchema,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        );

        $model->updated_at = time();

        $model->save(false, [
            'schema_json',
            'updated_at'
        ]);
    }

    /**
     * ===============================
     * PREVIEW TEMPLATE
     * ===============================
     */
    public function actionPreviewForm($id)
    {
        $model = $this->findModel($id);
        $this->syncBuilderSchemaFromSource($model);
        $lockState = $this->getTemplateLockState($model);

        if (empty($model->schema_json)) {
            Yii::$app->session->setFlash('warning', 'Schema belum tersedia.');
            return $this->redirect(['index']);
        }

        return $this->render('preview-form', compact('model', 'lockState'));
    }

    public function actionMappingStatus($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = $this->findModel($id);
        $this->syncBuilderSchemaFromSource($model);
        $lockState = $this->getTemplateLockState($model);
        $summary = $model->getSchemaValidationSummary();

        return [
            'status' => $summary['is_valid'] ? 'ok' : 'error',
            'template_id' => $model->id,
            'template_name' => $model->name,
            'lock_state' => $lockState,
            'mapping' => $summary,
        ];
    }

    public function actionUpdateMapping($id)
    {
        $model = $this->findModel($id);
        $lockState = $this->getTemplateLockState($model);
        if ($lockState['is_locked']) {
            Yii::$app->session->setFlash(
                'warning',
                $lockState['reason']
                ?? 'Template ini sudah terkunci karena dipakai proses produksi. Buat versi template baru jika ingin perubahan.'
            );
            return $this->redirect(['preview-form', 'id' => $id]);
        }

        $schema = $model->getSchema();
        $items = $schema['items'] ?? [];

        $mappingPayload = Yii::$app->request->post('mappings', []);
        $metaMappingPayload = Yii::$app->request->post('meta_mapping', []);

        // hanya validasi kalau schema item benar-benar kosong
        if (empty($items)) {
            Yii::$app->session->setFlash('danger', 'Item template tidak ditemukan.');
            return $this->redirect(['preview-form', 'id' => $id]);
        }

        // kalau mapping item kosong, tetap allow save meta_mapping
        if (!is_array($mappingPayload)) {
            $mappingPayload = [];
        }

        if (!is_array($metaMappingPayload)) {
            $metaMappingPayload = [];
        }

        foreach ($items as &$item) {
            $itemId = (string)($item['item_id'] ?? '');
            if ($itemId === '' || !isset($mappingPayload[$itemId]) || !is_array($mappingPayload[$itemId])) {
                continue;
            }

            $payload = $mappingPayload[$itemId];
            $item['excel'] = $item['excel'] ?? [];

            $sheet = trim((string)($payload['sheet'] ?? ($item['excel']['sheet'] ?? '')));
            $row = (int)($payload['row'] ?? ($item['excel']['row'] ?? 0));
            $cell = strtoupper(trim((string)($payload['cell'] ?? ($item['excel']['cell'] ?? ''))));

            [$cell, $row] = $this->normalizeCellAndRow($cell, $row);

            if ($sheet !== '') {
                $item['excel']['sheet'] = $sheet;
            }
            if ($row > 0) {
                $item['excel']['row'] = $row;
                if (empty($item['source_row'])) {
                    $item['source_row'] = $row;
                }
            }
            if ($cell !== '') {
                $item['excel']['cell'] = $cell;
            }

            $item['required'] = !empty($payload['required']);
            $item['excel']['mapping_strategy'] = 'manual_override';

            if (!empty($item['conditions']) && is_array($item['conditions'])) {

                preg_match('/^([A-Z]+)([0-9]+)$/', $cell, $matches);

                $baseColumn = $matches[1] ?? 'E';
                $baseRow = (int)($matches[2] ?? $row);

                foreach ($item['conditions'] as $conditionIndex => &$condition) {

                    $condition['excel'] = $condition['excel'] ?? [];

                    $conditionRow = $baseRow + ($conditionIndex * 3);

                    $condition['excel']['sheet'] = $sheet;
                    $condition['excel']['row'] = $conditionRow;
                    $condition['excel']['cell'] = $baseColumn . $conditionRow;
                    $condition['excel']['mapping_strategy'] = 'manual_condition_override';
                }

                unset($condition);
            }
        }
        unset($item);

        [$items, $resolvedCount] = $this->ensureUniqueMappingTargets($items);

        $errors = $this->validateItemMappings($items);
        if (!empty($errors)) {
            Yii::$app->session->setFlash(
                'danger',
                "Mapping tidak valid. " . implode(' | ', array_slice($errors, 0, 3))
            );
            return $this->redirect(['preview-form', 'id' => $id]);
        }

        /**
         * ==========================================
         * SAVE META APPROVAL MAPPING
         * ==========================================
         */
        $schema['meta_mapping'] = [
            'operator_shift_1' => strtoupper(trim(
                $metaMappingPayload['operator_shift_1'] ?? ''
            )),

            'operator_shift_2' => strtoupper(trim(
                $metaMappingPayload['operator_shift_2'] ?? ''
            )),

            'operator_shift_3' => strtoupper(trim(
                $metaMappingPayload['operator_shift_3'] ?? ''
            )),

            'leader_shift_1' => strtoupper(trim(
                $metaMappingPayload['leader_shift_1'] ?? ''
            )),

            'leader_shift_2' => strtoupper(trim(
                $metaMappingPayload['leader_shift_2'] ?? ''
            )),

            'leader_shift_3' => strtoupper(trim(
                $metaMappingPayload['leader_shift_3'] ?? ''
            )),

            'chief' => strtoupper(trim(
                $metaMappingPayload['chief'] ?? ''
            )),

            'manager' => strtoupper(trim(
                $metaMappingPayload['manager'] ?? ''
            )),
        ];

        $schema['items'] = $items;
        $schema['updated_at'] = date('Y-m-d H:i:s');
        $model->schema_json = json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        if (!$model->save(false)) {
            Yii::$app->session->setFlash('danger', 'Gagal menyimpan mapping template.');
            return $this->redirect(['preview-form', 'id' => $id]);
        }

        if ($resolvedCount > 0) {
            Yii::$app->session->setFlash('warning', 'Mapping bentrok terdeteksi dan diperbaiki otomatis (' . $resolvedCount . ' target digeser).');
        } else {
            Yii::$app->session->setFlash('success', 'Mapping template berhasil diperbarui.');
        }

        return $this->redirect(['preview-form', 'id' => $id]);
    }

    private function normalizeCellAndRow(string $cell, int $row): array
    {
        $cell = strtoupper(trim($cell));

        // Input lengkap seperti E33
        if (preg_match('/^([A-Z]+)([1-9][0-9]*)$/', $cell, $m)) {
            $column = $m[1];
            $rowFromCell = (int)$m[2];
            return [$column . $rowFromCell, $row > 0 ? $row : $rowFromCell];
        }

        // Input kolom saja seperti E + row terpisah
        if (preg_match('/^[A-Z]+$/', $cell) && $row > 0) {
            return [$cell . $row, $row];
        }

        // Input angka saja (mis. user salah isi cell=33), pakai kolom default E
        if (preg_match('/^[1-9][0-9]*$/', $cell)) {
            $resolvedRow = (int)$cell;
            return ['E' . $resolvedRow, $resolvedRow];
        }

        return [$cell, $row];
    }

    /**
     * ===============================
     * FIND MODEL
     * ===============================
     */
    protected function findModel($id)
    {
        if (($model = FormTemplate::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('Data tidak ditemukan.');
    }

    /**
     * ===============================
     * DOWNLOAD FILE EXCEL TEMPLATE
     * ===============================
     */
    public function actionDownload($id)
    {
        $model = $this->findModel($id);

        if (empty($model->source_file)) {
            Yii::$app->session->setFlash('warning', 'Template ini tidak memiliki file Excel (dibuat dari Form Builder).');
            return $this->redirect(['index']);
        }

        $filePath = Yii::getAlias('@webroot') . '/' . ltrim($model->source_file, '/');

        if (!is_file($filePath)) {
            Yii::$app->session->setFlash('danger', 'File tidak ditemukan di server.');
            return $this->redirect(['index']);
        }

        $originalName = basename($filePath);
        // Hilangkan prefix timestamp agar nama download lebih bersih
        $cleanName = preg_replace('/^\d+_/', '', $originalName);

        return Yii::$app->response->sendFile($filePath, $cleanName, [
            'mimeType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'inline'   => false,
        ]);
    }

    /**
     * ===============================
     * SAVE UPLOADED FILE
     * ===============================
     */
    protected function saveUploadedFile(UploadedFile $file): string
    {
        $dir = Yii::getAlias('@webroot/uploads/templates');
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $safeName = preg_replace('/[^A-Za-z0-9\-_\.]/', '_', $file->name);
        $path = $dir . '/' . time() . '_' . $safeName;

        if (!$file->saveAs($path)) {
            throw new \RuntimeException('Gagal menyimpan file upload.');
        }

        return $path;
    }

    /**
     * ===============================
     * PARSE EXCEL → TEMPLATE SCHEMA
     * ===============================
     */
    protected function detectExcelStructure(string $filePath): array|false
    {
        try {
            $reader = new Xlsx();
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($filePath);
            $sheets = $spreadsheet->getAllSheets();

            $sheetList = [];
            $cellRanges = [];

            foreach ($sheets as $sheet) {
                $sheetName = (string)$sheet->getTitle();
                $sheetList[] = $sheetName;

                // Detect used cell range
                $maxRow = $sheet->getHighestDataRow();
                $maxCol = $sheet->getHighestDataColumn();

                $cellRanges[$sheetName] = [
                    'max_row' => $maxRow,
                    'max_col' => $maxCol,
                ];
            }

            return [
                'sheets' => $sheetList,
                'cell_ranges' => $cellRanges,
                'detected_at' => date('Y-m-d H:i:s'),
            ];

        } catch (\Throwable $e) {
            Yii::error('EXCEL DETECT ERROR: ' . $e->getMessage(), 'template');
            return false;
        }
    }

    protected function buildSchemaFromBuilder(int $builderTemplateId, array $excelStructure): array|false
    {
        try {
            $builderTemplate = ChecksheetTemplate::findOne($builderTemplateId);
            if (!$builderTemplate) {
                return false;
            }

            $sections = ChecksheetSection::find()
                ->where(['template_id' => $builderTemplateId])
                ->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC])
                ->all();

            if (empty($sections)) {
                return false;
            }

            $items = [];
            $itemCounter = 1;
            $rowCursor = 18;

            foreach ($sections as $section) {
                $sectionItems = ChecksheetItem::find()
                    ->where(['section_id' => $section->id])
                    ->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC])
                    ->all();

                foreach ($sectionItems as $sItem) {
                    $instruction = $sItem->getInstruction();
                    $conditions = $this->normalizeBuilderConditions($sItem->getConditionRows());
                    $standard = implode(', ', array_filter($instruction['standard'] ?? [])) ?: '-';
                    $cara = implode(', ', array_filter($instruction['cara'] ?? [])) ?: '-';

                    if (!empty($conditions)) {
                        $standard = $this->summarizeConditionField($conditions, 'standard');
                        $cara = $this->summarizeConditionField($conditions, 'cara');
                    }

                    // Default cell mapping (will be refined in preview)
                    $rowSpan = max(1, count($conditions)) * 3;
                    $defaultRow = $rowCursor;
                    $defaultCell = 'E' . $defaultRow;
                    $defaultSheet = $excelStructure['sheets'][0] ?? 'Sheet1';
                    $conditionMappings = $this->buildConditionMappings(
                        $conditions,
                        $defaultSheet,
                        $defaultCell,
                        $defaultRow
                    );

                    $items[] = [
                        'item_id'    => 'CHK-' . str_pad((string)$itemCounter, 3, '0', STR_PAD_LEFT),
                        'builder_item_code' => (string)$sItem->item_code,
                        'no'         => $itemCounter,
                        'section'    => (string)$section->title,
                        'label'      => trim((string)$sItem->label),
                        'standard'   => $standard,
                        'cara'       => $cara,
                        'conditions' => $conditionMappings,
                        'required'   => false,
                        'excel'      => [
                            'sheet' => $defaultSheet,
                            'row'   => $defaultRow,
                            'cell'  => $defaultCell,
                            'mapping_strategy' => 'manual',
                        ],
                        'frequency'  => 'per_shift',
                        'input_type' => 'check',
                    ];
                    $rowCursor += $rowSpan;
                    $itemCounter++;
                }
            }

            [$items] = $this->ensureUniqueMappingTargets($items);

            if (empty($items)) {
                return false;
            }

            return [
                'version'              => 1,
                'generated_at'         => date('Y-m-d H:i:s'),
                'source'               => 'builder',
                'builder_template_id'  => $builderTemplateId,
                'builder_template_name' => (string)$builderTemplate->name,
                'excel_dynamic'        => [
                    'enabled' => true,
                    'mode' => 'matrix_shift_day',
                    'base_day' => 1,
                    'max_shift_rows' => 3,
                ],
                'excel_structure'      => $excelStructure,
                'items'                => $items,
            ];

        } catch (\Throwable $e) {
            Yii::error('BUILD SCHEMA ERROR: ' . $e->getMessage(), 'template');
            return false;
        }
    }

    protected function parseTemplateFromExcel(string $filePath): array|false
    {
        try {
            $reader = new Xlsx();
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($filePath);
            $sheets = $spreadsheet->getAllSheets();

            $items = [];
            $itemCounter = 1;
            $processedSheets = [];

            $blacklist = [
                'START CHECK SHEET',
                'NO.DOK',
                'NO.KONTROL',
                'REVISI',
                'HAL',
                'Standard nilai',
                'Cara Penulisan',
                'Frekuensi pengecheckan',
            ];

            foreach ($sheets as $sheet) {
                $sheetTitle = (string)$sheet->getTitle();
                $rows = $sheet->toArray(null, true, true, true);
                $currentSection = null;
                $sheetHasItems = false;

                foreach ($rows as $rowIndex => $row) {
                    $A = trim((string)($row['A'] ?? '')); // No / Section
                    $B = trim((string)($row['B'] ?? '')); // Item
                    $C = trim((string)($row['C'] ?? '')); // Standard
                    $D = trim((string)($row['D'] ?? '')); // Cara

                    if ($A === '' && $B === '') {
                        continue;
                    }

                    foreach ($blacklist as $blk) {
                        if (stripos($B, $blk) === 0) {
                            continue 2;
                        }
                    }

                    // SECTION
                    if ($A !== '' && !preg_match('/^\d+$/', $A) && $B === '') {
                        $currentSection = $A;
                        continue;
                    }

                    // ITEM
                    if (preg_match('/^\d+$/', $A) && $B !== '') {
                        $defaultResultCell = $this->buildDefaultResultCell($row, (int)$rowIndex);
                        $items[] = [
                            'item_id'    => 'CHK-' . str_pad($itemCounter++, 3, '0', STR_PAD_LEFT),
                            'no'         => (int)$A,
                            'section'    => $currentSection,
                            'label'      => $B,
                            'standard'   => $C,
                            'cara'       => $D,
                            'source_row' => (int)$rowIndex,
                            'required'   => $this->inferRequired($B, $C, $D),
                            'excel'      => [
                                'sheet' => $sheetTitle,
                                'row' => (int)$rowIndex,
                                'source_cell' => 'B' . (int)$rowIndex,
                                'cell' => $defaultResultCell,
                                'mapping_strategy' => 'next_empty_from_E',
                            ],
                            'frequency'  => 'per_shift',
                            'input_type' => 'check',
                        ];
                        $sheetHasItems = true;
                    }
                }

                if ($sheetHasItems) {
                    $processedSheets[] = $sheetTitle;
                }
            }

            if (empty($items)) {
                return false;
            }

            return [
                'version' => 1,
                'generated_at' => date('Y-m-d H:i:s'),
                'source_sheets' => $processedSheets,
                'items' => $items,
            ];

        } catch (\Throwable $e) {
            Yii::error('TEMPLATE PARSER ERROR: ' . $e->getMessage(), 'template');
            return false;
        }
    }

    private function buildDefaultResultCell(array $row, int $rowIndex): string
    {
        $startColumnIndex = Coordinate::columnIndexFromString('E');
        $maxColumns = 80;

        for ($columnIndex = $startColumnIndex; $columnIndex <= $maxColumns; $columnIndex++) {
            $column = Coordinate::stringFromColumnIndex($columnIndex);
            $value = trim((string)($row[$column] ?? ''));
            if ($value === '') {
                return $column . $rowIndex;
            }
        }

        return 'E' . $rowIndex;
    }

    private function inferRequired(string $label, string $standard, string $cara): bool
    {
        $text = strtolower(trim($label . ' ' . $standard . ' ' . $cara));
        if ($text === '') {
            return false;
        }

        if (strpos($label, '*') !== false) {
            return true;
        }

        $keywords = ['wajib', 'mandatory', 'must', 'harus'];
        foreach ($keywords as $keyword) {
            if (strpos($text, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }
    //--activate--//
    public function actionActivate($id)
    {
        $model = $this->findModel($id);

        $lockState = $this->getTemplateLockState($model);
        if (!empty($lockState['is_locked'])) {
            Yii::$app->session->setFlash(
                'warning',
                'Template tidak bisa diubah status karena terkunci. '
                . ($lockState['reason'] ?? 'Template sudah dipakai proses produksi.')
            );
            return $this->redirect(['preview-form', 'id' => $id]);
        }

        if ($model->status === 'active') {
            Yii::$app->session->setFlash('info', 'Template ini sudah aktif.');
            return $this->redirect(['preview-form', 'id' => $id]);
        }

        $mappingErrors = $this->validateSchemaMappings($model);
        
        if (!empty($mappingErrors)) {
            $rules = [
                'excel.sheet wajib terisi',
                'excel.row harus > 0',
                'excel.cell harus format A1 (contoh: E33)',
                'target cell tidak boleh duplicate dalam sheet yang sama',
            ];

            Yii::$app->session->setFlash(
                'danger',
                "Template belum bisa diaktifkan karena mapping Excel tidak valid.\n"
                . "Rule: " . implode('; ', $rules) . "\n"
                . "Error: " . implode(' | ', array_slice($mappingErrors, 0, 8))
            );
            return $this->redirect(['preview-form', 'id' => $id]);
        }

        $tx = Yii::$app->db->beginTransaction();
        try {
            // Aktivasi template tanpa mempengaruhi template lain
            // (berbeda dengan lama yang langsung deactivate template di mesin yang sama)
            $model->status = 'active';
            $model->updated_at = time();

            if (!$model->save(false, ['status', 'updated_at'])) {
                Yii::error($model->errors, 'template');
                throw new \RuntimeException('Gagal menyimpan perubahan status template.');
            }

            $tx->commit();

            Yii::$app->session->setFlash(
                'success',
                'Template berhasil diaktifkan.'
            );

        } catch (\Throwable $e) {
            $tx->rollBack();
            Yii::error($e->getMessage(), 'template');

            $message = trim((string)$e->getMessage());
            if ($message === '') {
                $message = 'Unknown error';
            }

            Yii::$app->session->setFlash(
                'danger',
                'Gagal mengaktifkan template. Alasan: ' . $message
            );
        }

        return $this->redirect(['preview-form', 'id' => $id]);
    }

    private function validateSchemaMappings(FormTemplate $model): array
    {
        return $model->validateSchemaMappings();
    }

    private function validateItemMappings(array $items): array
    {
        $tmpTemplate = new FormTemplate();
        $tmpTemplate->schema_json = json_encode(['items' => $items], JSON_UNESCAPED_UNICODE);
        return $tmpTemplate->validateSchemaMappings();
    }

    private function ensureUniqueMappingTargets(array $items): array
    {
        $occupied = [];
        $resolvedCount = 0;

        foreach ($items as &$item) {
            $itemId = (string)($item['item_id'] ?? 'unknown');

            if (!empty($item['conditions']) && is_array($item['conditions'])) {
                foreach ($item['conditions'] as $index => &$condition) {
                    $excel = is_array($condition['excel'] ?? null) ? $condition['excel'] : [];
                    [$excel, $changed] = $this->resolveDuplicateExcelTarget(
                        $excel,
                        $occupied,
                        $itemId . ' kondisi #' . ($index + 1)
                    );

                    if ($changed) {
                        $resolvedCount++;
                    }

                    $condition['excel'] = $excel;
                }
                unset($condition);

                if (!empty($item['conditions'][0]['excel']) && is_array($item['conditions'][0]['excel'])) {
                    $item['excel'] = array_merge($item['excel'] ?? [], $item['conditions'][0]['excel']);
                }

                continue;
            }

            $excel = is_array($item['excel'] ?? null) ? $item['excel'] : [];
            [$excel, $changed] = $this->resolveDuplicateExcelTarget($excel, $occupied, $itemId);
            if ($changed) {
                $resolvedCount++;
            }
            $item['excel'] = $excel;
        }
        unset($item);

        return [$items, $resolvedCount];
    }

    private function resolveDuplicateExcelTarget(array $excel, array &$occupied, string $label): array
    {
        $sheet = trim((string)($excel['sheet'] ?? ''));
        $cell = strtoupper(trim((string)($excel['cell'] ?? '')));
        $row = (int)($excel['row'] ?? 0);

        [$cell, $row] = $this->normalizeCellAndRow($cell, $row);
        if ($sheet === '' || !preg_match('/^([A-Z]+)([1-9][0-9]*)$/', $cell, $matches)) {
            return [$excel, false];
        }

        $column = $matches[1];
        $currentRow = (int)$matches[2];
        $key = $sheet . '::' . $column . $currentRow;
        $changed = false;

        while (isset($occupied[$key])) {
            $currentRow += 3;
            $key = $sheet . '::' . $column . $currentRow;
            $changed = true;
        }

        $occupied[$key] = $label;
        $excel['row'] = $currentRow;
        $excel['cell'] = $column . $currentRow;

        if ($changed) {
            $excel['mapping_strategy'] = 'auto_dedup';
        }

        return [$excel, $changed];
    }
}
