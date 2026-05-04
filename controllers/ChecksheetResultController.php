<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

use app\models\ChecksheetResult;
use app\models\FormTemplate;
use app\models\MachineTemplate;

class ChecksheetResultController extends Controller
{
    /* =========================================
       LIST RESULT
    ========================================= */
    public function actionIndex()
    {
        $results = ChecksheetResult::find()
            ->orderBy(['id' => SORT_DESC])
            ->all();

        return $this->render('index', compact('results'));
    }

    /* =========================================
       VIEW DETAIL
    ========================================= */
    public function actionView($id)
    {
        $model = ChecksheetResult::find()
            ->with([
                'items',
                'template.sections.items.symbol',
                'template.sections.items.symbol2',
            ])
            ->where(['id' => $id])
            ->one();

        if (!$model) {
            throw new NotFoundHttpException("Result tidak ditemukan.");
        }

        $answerMapById = [];
        $answerMapByCode = [];
        foreach ($model->items as $resultItem) {
            if (!empty($resultItem->item_id)) {
                $answerMapById[(int)$resultItem->item_id] = $resultItem;
            }
            if (!empty($resultItem->item_code)) {
                $answerMapByCode[(string)$resultItem->item_code] = $resultItem;
            }
        }

        $historyResults = ChecksheetResult::find()
            ->where(['mesin' => $model->mesin])
            ->andWhere(['<>', 'id', $model->id])
            ->orderBy(['submitted_at' => SORT_DESC, 'id' => SORT_DESC])
            ->limit(20)
            ->all();

        return $this->render('view', [
            'model' => $model,
            'answerMapById' => $answerMapById,
            'answerMapByCode' => $answerMapByCode,
            'historyResults' => $historyResults,
        ]);
    }

    /* =========================================
       EXPORT EXCEL (BULANAN)
    ========================================= */
    public function actionExportExcel($id)
    {
        $result = $this->findResult($id);
        $bulan = date('Y-m', strtotime($result->submitted_at));

        $allResults = ChecksheetResult::find()
            ->with(['items.item'])
            ->where(['mesin' => $result->mesin])
            ->andWhere(['like', 'submitted_at', $bulan])
            ->all();

        [$spreadsheet, $formTemplate] = $this->loadOriginalTemplateSpreadsheet($result);
        $this->fillSpreadsheetWithResults($spreadsheet, $allResults, $formTemplate);

        $fileName = 'Checksheet_' . $result->mesin . '_' . $bulan . '.xlsx';
        return $this->sendSpreadsheet($spreadsheet, $fileName);
    }

    /* =========================================
       EXPORT FORM ASLI (1 HASIL)
    ========================================= */
    public function actionExportOriginal($id)
    {
        $result = $this->findResult($id);

        // Muat relasi item agar mapping sort_order -> cell berjalan.
        $result->populateRelation('items', $result->getItems()->with('item')->all());

        [$spreadsheet, $formTemplate] = $this->loadOriginalTemplateSpreadsheet($result);
        $this->fillSpreadsheetWithResults($spreadsheet, [$result], $formTemplate);

        $tanggal = date('Ymd_His', strtotime($result->submitted_at));
        $fileName = 'Checksheet_Asli_' . $result->mesin . '_' . $tanggal . '.xlsx';
        return $this->sendSpreadsheet($spreadsheet, $fileName);
    }

    private function findResult($id): ChecksheetResult
    {
        $result = ChecksheetResult::findOne($id);
        if (!$result) {
            throw new NotFoundHttpException('Result tidak ditemukan.');
        }
        return $result;
    }

    private function loadOriginalTemplateSpreadsheet(ChecksheetResult $result): array
    {
        $machineTemplate = MachineTemplate::find()
            ->where(['no_mesin' => $result->mesin])
            ->one();

        $mappedTemplate = null;
        if ($machineTemplate) {
            $candidate = FormTemplate::findOne($machineTemplate->template_id);
            if ($candidate) {
                $mappedTemplate = $candidate;
            }
        }

        if ($mappedTemplate !== null && !empty($mappedTemplate->source_file)) {
            $templatePath = Yii::getAlias('@webroot/') . $mappedTemplate->source_file;
            if (!file_exists($templatePath)) {
                throw new NotFoundHttpException('File template tidak ada di server.');
            }

            return [\PhpOffice\PhpSpreadsheet\IOFactory::load($templatePath), $mappedTemplate];
        }

        $formTemplate = $this->findExcelBackedTemplateForResult($result, $mappedTemplate);

        if ($formTemplate === null && $mappedTemplate !== null) {
            $formTemplate = $mappedTemplate;
        }

        if ($formTemplate === null) {
            $formTemplate = $this->findSchemaBackedTemplateForResult($result, $mappedTemplate);
        }

        if ($formTemplate === null) {
            throw new NotFoundHttpException(
                'Template Excel tidak ditemukan. Mesin saat ini mungkin masih terhubung ke template builder-auto tanpa file upload.'
            );
        }

        if (!empty($formTemplate->source_file)) {
            $templatePath = Yii::getAlias('@webroot/') . $formTemplate->source_file;
            if (!file_exists($templatePath)) {
                throw new NotFoundHttpException('File template tidak ada di server.');
            }

            return [\PhpOffice\PhpSpreadsheet\IOFactory::load($templatePath), $formTemplate];
        }

        $schema = $formTemplate->getSchema();
        $hasSchemaItems = !empty($schema['items']) && is_array($schema['items']);

        if (!$hasSchemaItems) {
            throw new NotFoundHttpException(
                'Template Excel tidak ditemukan. Mesin saat ini mungkin masih terhubung ke template builder-auto tanpa file upload.'
            );
        }

        return [$this->createSpreadsheetFromSchema($schema), $formTemplate];
    }

    private function createSpreadsheetFromSchema(array $schema): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $defaultSheet = $spreadsheet->getActiveSheet();
        $defaultSheet->setTitle('Sheet1');

        $sheetMap = ['Sheet1' => $defaultSheet];

        foreach (($schema['items'] ?? []) as $item) {
            $targets = [];

            if (!empty($item['conditions']) && is_array($item['conditions'])) {
                foreach ($item['conditions'] as $condition) {
                    if (!empty($condition['excel']) && is_array($condition['excel'])) {
                        $targets[] = $condition['excel'];
                    }
                }
            }

            if (empty($targets) && !empty($item['excel']) && is_array($item['excel'])) {
                $targets[] = $item['excel'];
            }

            foreach ($targets as $target) {
                $sheetName = trim((string)($target['sheet'] ?? ''));
                if ($sheetName === '') {
                    $sheetName = 'Sheet1';
                }

                if (!isset($sheetMap[$sheetName])) {
                    $sheet = new Worksheet($spreadsheet, $sheetName);
                    $spreadsheet->addSheet($sheet);
                    $sheetMap[$sheetName] = $sheet;
                }
            }
        }

        $spreadsheet->setActiveSheetIndex(0);
        return $spreadsheet;
    }

    private function findSchemaBackedTemplateForResult(ChecksheetResult $result, ?FormTemplate $mappedTemplate = null): ?FormTemplate
    {
        $builderTemplateIds = $this->collectBuilderTemplateIds($result, $mappedTemplate);
        foreach ($builderTemplateIds as $builderTemplateId) {
            $query = FormTemplate::find();
            $this->applyBuilderTemplateIdFilter($query, $builderTemplateId);

            $candidate = $query
                ->orderBy([
                    new \yii\db\Expression("CASE WHEN status = 'active' THEN 0 WHEN status = 'draft' THEN 1 ELSE 2 END"),
                    'id' => SORT_DESC,
                ])
                ->one();

            if ($candidate !== null) {
                return $candidate;
            }
        }

        return null;
    }

    private function findExcelBackedTemplateForResult(ChecksheetResult $result, ?FormTemplate $mappedTemplate = null): ?FormTemplate
    {
        $builderTemplateIds = $this->collectBuilderTemplateIds($result, $mappedTemplate);
        foreach ($builderTemplateIds as $builderTemplateId) {
            $query = FormTemplate::find()
                ->where(['not', ['source_file' => null]])
                ->andWhere(['<>', 'source_file', '']);

            $this->applyBuilderTemplateIdFilter($query, $builderTemplateId);

            $candidate = $query
                ->orderBy([
                    new \yii\db\Expression("CASE WHEN status = 'active' THEN 0 WHEN status = 'draft' THEN 1 ELSE 2 END"),
                    'id' => SORT_DESC,
                ])
                ->one();

            if ($candidate !== null) {
                return $candidate;
            }
        }

        return null;
    }

    private function collectBuilderTemplateIds(ChecksheetResult $result, ?FormTemplate $mappedTemplate = null): array
    {
        $ids = [];

        $resultTemplateId = (int)$result->template_id;
        if ($resultTemplateId > 0) {
            $ids[] = $resultTemplateId;
        }

        if ($mappedTemplate !== null) {
            $schema = $mappedTemplate->getSchema();
            $mappedBuilderId = (int)($schema['builder_template_id'] ?? 0);
            if ($mappedBuilderId > 0) {
                $ids[] = $mappedBuilderId;
            }
        }

        $ids = array_values(array_unique(array_filter($ids, static fn($id) => (int)$id > 0)));
        return array_map('intval', $ids);
    }

    private function applyBuilderTemplateIdFilter(\yii\db\ActiveQuery $query, int $builderTemplateId): void
    {
        $pattern = '"builder_template_id"[[:space:]]*:[[:space:]]*' . $builderTemplateId . '([^0-9]|$)';
        $query->andWhere(new \yii\db\Expression('schema_json REGEXP :builderPattern', [
            ':builderPattern' => $pattern,
        ]));
    }

    private function fillSpreadsheetWithResults(
        \PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet,
        array $results,
        FormTemplate $formTemplate
    ): void {
        $schema = $formTemplate->getSchema();
        $mappingByBuilderCode = $this->buildTemplateMappingByBuilderCode($schema);
        $dynamicConfig = $this->resolveDynamicConfig($schema);

        /*
        =========================================
        WRITE OPERATOR INITIAL (META MAPPING)
        =========================================
        */
        $metaMapping = $schema['meta_mapping'] ?? [];

        if (!empty($metaMapping)) {
            foreach ($results as $res) {

                $shiftIndex = $this->extractShiftIndex(
                    (string)$res->shift,
                    3
                );

                $operatorKey = 'operator_shift_' . $shiftIndex;

                if (
                    empty($metaMapping[$operatorKey]) ||
                    empty($res->created_by)
                ) {
                    continue;
                }

                $targetCell = strtoupper(
                    trim($metaMapping[$operatorKey])
                );

                if ($targetCell === '') {
                    continue;
                }

                $sheetName = $schema['excel_structure']['sheets'][0] ?? null;

                $targetSheet = $sheetName
                    ? $spreadsheet->getSheetByName($sheetName)
                    : $spreadsheet->getActiveSheet();

                if (!$targetSheet) {
                    $targetSheet = $spreadsheet->getActiveSheet();
                }

                $operatorInitial = $this->generateOperatorInitial(
                    (string)$res->created_by
                );

                Yii::error([
                    'created_by' => $res->created_by,
                    'initial' => $operatorInitial,
                    'target_cell' => $targetCell,
                    'shift' => $res->shift,
                    'submitted_at' => $res->submitted_at,
                    'sheet' => $targetSheet->getTitle(),
                ], 'export_debug');

                $resolvedCell = $this->resolveMetaMappedCell(
                    $targetCell,
                    (string)$res->submitted_at,
                    $dynamicConfig
                );

                $targetSheet->setCellValue(
                    $resolvedCell,
                    $operatorInitial
                );
            }
        }
        /*
        =========================================
        WRITE LEADER INITIAL
        =========================================
        */
        if (!empty($metaMapping)) {
            foreach ($results as $res) {

                $shiftIndex = $this->extractShiftIndex(
                    (string)$res->shift,
                    3
                );

                $leaderKey = 'leader_shift_' . $shiftIndex;

                if (
                    empty($metaMapping[$leaderKey]) ||
                    empty($res->leader_id) ||
                    empty($res->leader_approved_at)
                ) {
                    continue;
                }

                $targetCell = strtoupper(
                    trim($metaMapping[$leaderKey])
                );

                if ($targetCell === '') {
                    continue;
                }

                $sheetName = $schema['excel_structure']['sheets'][0] ?? null;

                $targetSheet = $sheetName
                    ? $spreadsheet->getSheetByName($sheetName)
                    : $spreadsheet->getActiveSheet();

                if (!$targetSheet) {
                    $targetSheet = $spreadsheet->getActiveSheet();
                }

                $leaderInitial = $this->getApprovalInitial(
                    $res->leader_id
                );

                $resolvedCell = $this->resolveApprovalCell(
                    $targetCell,
                    (string)$res->submitted_at,
                    $dynamicConfig
                );

                $targetSheet->setCellValue(
                    $resolvedCell,
                    $leaderInitial
                );
            }
        }
        /*
        =========================================
        WRITE CHIEF INITIAL
        =========================================
        */
        if (!empty($metaMapping)) {
            foreach ($results as $res) {

                if (
                    empty($metaMapping['chief']) ||
                    empty($res->chief_id) ||
                    empty($res->chief_approved_at)
                ) {
                    continue;
                }

                $targetCell = strtoupper(
                    trim($metaMapping['chief'])
                );

                if ($targetCell === '') {
                    continue;
                }

                $sheetName = $schema['excel_structure']['sheets'][0] ?? null;

                $targetSheet = $sheetName
                    ? $spreadsheet->getSheetByName($sheetName)
                    : $spreadsheet->getActiveSheet();

                if (!$targetSheet) {
                    $targetSheet = $spreadsheet->getActiveSheet();
                }

                $chiefInitial = $this->getApprovalInitial(
                    $res->chief_id
                );

                $resolvedCell = $this->resolveApprovalCell(
                    $targetCell,
                    (string)$res->submitted_at,
                    $dynamicConfig,
                    $metaMapping['chief_frequency'] ?? 'weekly'
                );

                $targetSheet->setCellValue(
                    $resolvedCell,
                    $chiefInitial
                );
            }
        }
        /*
        =========================================
        WRITE MANAGER INITIAL
        =========================================
        */
        if (!empty($metaMapping)) {
            foreach ($results as $res) {

                if (
                    empty($metaMapping['manager']) ||
                    empty($res->manager_id) ||
                    empty($res->manager_approved_at)
                ) {
                    continue;
                }

                $targetCell = strtoupper(
                    trim($metaMapping['manager'])
                );

                if ($targetCell === '') {
                    continue;
                }

                $sheetName = $schema['excel_structure']['sheets'][0] ?? null;

                $targetSheet = $sheetName
                    ? $spreadsheet->getSheetByName($sheetName)
                    : $spreadsheet->getActiveSheet();

                if (!$targetSheet) {
                    $targetSheet = $spreadsheet->getActiveSheet();
                }

                $managerInitial = $this->getApprovalInitial(
                    $res->manager_id
                );

                $resolvedCell = $this->resolveApprovalCell(
                    $targetCell,
                    (string)$res->submitted_at,
                    $dynamicConfig,
                    $metaMapping['manager_frequency'] ?? 'monthly'
                );

                $targetSheet->setCellValue(
                    $resolvedCell,
                    $managerInitial
                );
            }
        }

        /*
        =========================================
        EXPORT SUMMARY SHEET (ONLY BUILDER MODE)
        =========================================
        */
        if (empty($formTemplate->source_file)) {
            $this->writeSchemaExportSummarySheet(
                $spreadsheet,
                $results,
                $mappingByBuilderCode,
                $dynamicConfig
            );
        }

        /*
        =========================================
        WRITE CHECKSHEET ITEM VALUES
        =========================================
        */
        foreach ($results as $res) {
            foreach ($res->items as $itemResult) {

                $builderCode = trim(
                    (string)($itemResult->item_code ?? '')
                );

                $mappedItem = $builderCode !== ''
                    ? ($mappingByBuilderCode[$builderCode] ?? null)
                    : null;

                $rawValue = trim(
                    (string)$itemResult->raw_value
                );

                $formattedValue = $this->formatChecksheetValue(
                    $rawValue,
                    $mappedItem
                );

                if ($mappedItem !== null) {
                    $success = $this->writeMappedValueToTemplate(
                        $spreadsheet,
                        $mappedItem,
                        (string)$res->submitted_at,
                        (string)$res->shift,
                        $dynamicConfig,
                        $formattedValue
                    );

                    if ($success) {
                        continue;
                    }
                }

                $this->writeLegacyFallbackCell(
                    $spreadsheet,
                    $res,
                    $itemResult,
                    $formattedValue
                );
            }
        }
    }

    private function buildTemplateMappingByBuilderCode(array $schema): array
    {
        $map = [];
        foreach (($schema['items'] ?? []) as $item) {
            $builderCode = trim((string)($item['builder_item_code'] ?? ''));
            if ($builderCode !== '') {
                $map[$builderCode] = $item;
            }
        }

        return $map;
    }

    private function writeSchemaExportSummarySheet(
        Spreadsheet $spreadsheet,
        array $results,
        array $mappingByBuilderCode,
        array $dynamicConfig
    ): void {
        $sheetName = 'Export_Data';
        $sheet = $spreadsheet->getSheetByName($sheetName);
        if ($sheet === null) {
            $sheet = new Worksheet($spreadsheet, $sheetName);
            $spreadsheet->addSheet($sheet, 0);
        }

        $sheet->setCellValue('A1', 'Mesin');
        $sheet->setCellValue('B1', 'Shift');
        $sheet->setCellValue('C1', 'Submitted At');
        $sheet->setCellValue('D1', 'Item Code');
        $sheet->setCellValue('E1', 'Label Item');
        $sheet->setCellValue('F1', 'Raw Value');
        $sheet->setCellValue('G1', 'Formatted Value');
        $sheet->setCellValue('H1', 'Target Cell');

        $rowCursor = 2;
        foreach ($results as $res) {
            foreach ($res->items as $itemResult) {
                $builderCode = trim((string)($itemResult->item_code ?? ''));
                $mappedItem = $builderCode !== '' ? ($mappingByBuilderCode[$builderCode] ?? null) : null;
                $rawValue = trim((string)$itemResult->raw_value);
                $formattedValue = $this->formatChecksheetValue($rawValue, $mappedItem);

                $targetCell = '';
                if ($mappedItem !== null) {
                    $excelTarget = [];
                    if (!empty($mappedItem['excel']) && is_array($mappedItem['excel'])) {
                        $excelTarget = $mappedItem['excel'];
                    }

                    if (!empty($excelTarget['cell'])) {
                        $targetCell = $this->resolveMappedCell(
                            strtoupper(trim((string)$excelTarget['cell'])),
                            (string)$res->submitted_at,
                            (string)$res->shift,
                            $dynamicConfig
                        );

                        $targetSheet = trim((string)($excelTarget['sheet'] ?? ''));
                        if ($targetSheet !== '') {
                            $targetCell = $targetSheet . '!' . $targetCell;
                        }
                    }
                }

                $sheet->setCellValue('A' . $rowCursor, (string)$res->mesin);
                $sheet->setCellValue('B' . $rowCursor, (string)$res->shift);
                $sheet->setCellValue('C' . $rowCursor, (string)$res->submitted_at);
                $sheet->setCellValue('D' . $rowCursor, $builderCode);
                $sheet->setCellValue('E' . $rowCursor, (string)($mappedItem['label'] ?? ''));
                $sheet->setCellValue('F' . $rowCursor, $rawValue);
                $sheet->setCellValue('G' . $rowCursor, $formattedValue);
                $sheet->setCellValue('H' . $rowCursor, $targetCell);
                $rowCursor++;
            }
        }

        foreach (['A' => 18, 'B' => 10, 'C' => 20, 'D' => 18, 'E' => 32, 'F' => 14, 'G' => 16, 'H' => 20] as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        $spreadsheet->setActiveSheetIndexByName($sheetName);
    }

    private function writeMappedValueToTemplate(
        \PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet,
        array $mappedItem,
        string $submittedAt,
        string $shiftValue,
        array $dynamicConfig,
        string $formattedValue
    ): bool {
        $targets = [];

        if (!empty($mappedItem['conditions']) && is_array($mappedItem['conditions'])) {
            foreach ($mappedItem['conditions'] as $condition) {
                if (!empty($condition['excel']) && is_array($condition['excel'])) {
                    $targets[] = $condition['excel'];
                }
            }
        }

        if (empty($targets) && !empty($mappedItem['excel']) && is_array($mappedItem['excel'])) {
            $targets[] = $mappedItem['excel'];
        }

        $wrote = false;
        foreach ($targets as $target) {
            $sheetName = trim((string)($target['sheet'] ?? ''));
            $baseCell = strtoupper(trim((string)($target['cell'] ?? '')));

            $targetSheet = $sheetName !== '' ? $spreadsheet->getSheetByName($sheetName) : null;
            if ($targetSheet === null && $sheetName === '') {
                $targetSheet = $spreadsheet->getActiveSheet();
            }

            if ($targetSheet === null || $baseCell === '') {
                continue;
            }

            $resolvedCell = $this->resolveMappedCell($baseCell, $submittedAt, $shiftValue, $dynamicConfig);
            $targetSheet->setCellValue($resolvedCell, $formattedValue);
            $wrote = true;
        }

        return $wrote;
    }

    private function resolveDynamicConfig(array $schema): array
    {
        $cfg = (array)($schema['excel_dynamic'] ?? []);

        return [
            'enabled' => array_key_exists('enabled', $cfg) ? (bool)$cfg['enabled'] : true,
            'mode' => (string)($cfg['mode'] ?? 'matrix_shift_day'),
            'base_day' => max(1, (int)($cfg['base_day'] ?? 1)),
            'max_shift_rows' => max(1, (int)($cfg['max_shift_rows'] ?? 3)),
        ];
    }

    private function resolveMappedCell(string $baseCell, string $submittedAt, string $shiftValue, array $dynamicConfig): string
    {
        if (!preg_match('/^([A-Z]+)([1-9][0-9]*)$/', strtoupper($baseCell), $matches)) {
            return strtoupper($baseCell);
        }

        $baseColumn = $matches[1];
        $baseRow = (int)$matches[2];
        if (empty($dynamicConfig['enabled']) || (string)$dynamicConfig['mode'] !== 'matrix_shift_day') {
            return $baseColumn . $baseRow;
        }

        $timestamp = strtotime($submittedAt);
        if ($timestamp === false) {
            return $baseColumn . $baseRow;
        }

        $dayOffset = max(0, (int)date('j', $timestamp) - max(1, (int)$dynamicConfig['base_day']));
        $shiftIndex = $this->extractShiftIndex($shiftValue, (int)$dynamicConfig['max_shift_rows']);

        $columnIndex = Coordinate::columnIndexFromString($baseColumn) + $dayOffset;
        $targetColumn = Coordinate::stringFromColumnIndex($columnIndex);
        $targetRow = $baseRow + max(0, $shiftIndex - 1);

        return $targetColumn . $targetRow;
    }

    private function resolveMetaMappedCell(
    string $baseCell,
    string $submittedAt,
    array $dynamicConfig
    ): string {
        if (!preg_match('/^([A-Z]+)([1-9][0-9]*)$/', strtoupper($baseCell), $matches)) {
            return strtoupper($baseCell);
        }

        $baseColumn = $matches[1];
        $baseRow = (int)$matches[2];

        if (
            empty($dynamicConfig['enabled']) ||
            (string)$dynamicConfig['mode'] !== 'matrix_shift_day'
        ) {
            return $baseColumn . $baseRow;
        }

        $timestamp = strtotime($submittedAt);

        if ($timestamp === false) {
            return $baseColumn . $baseRow;
        }

        // hanya geser kolom berdasarkan tanggal
        $dayOffset = max(
            0,
            (int)date('j', $timestamp) - max(1, (int)$dynamicConfig['base_day'])
        );

        $columnIndex = Coordinate::columnIndexFromString($baseColumn) + $dayOffset;
        $targetColumn = Coordinate::stringFromColumnIndex($columnIndex);

        return $targetColumn . $baseRow;
    }
    private function resolveApprovalCell(
    string $baseCell,
    string $submittedAt,
    array $dynamicConfig,
    string $frequency = 'daily'
    ): string {
        if (!preg_match('/^([A-Z]+)([1-9][0-9]*)$/', strtoupper($baseCell), $matches)) {
            return strtoupper($baseCell);
        }

        $baseColumn = $matches[1];
        $baseRow = (int)$matches[2];

        if (
            empty($dynamicConfig['enabled']) ||
            (string)$dynamicConfig['mode'] !== 'matrix_shift_day'
        ) {
            return $baseColumn . $baseRow;
        }

        $timestamp = strtotime($submittedAt);

        if ($timestamp === false) {
            return $baseColumn . $baseRow;
        }

        /*
        DAILY → geser per hari
        */
        if ($frequency === 'daily') {
            $dayOffset = max(
                0,
                (int)date('j', $timestamp) - max(1, (int)$dynamicConfig['base_day'])
            );

            $columnIndex = Coordinate::columnIndexFromString($baseColumn) + $dayOffset;
            $targetColumn = Coordinate::stringFromColumnIndex($columnIndex);

            return $targetColumn . $baseRow;
        }

        /*
        WEEKLY → tetap di base cell minggu tsb
        */
        if ($frequency === 'weekly') {
            $weekOffset = floor(((int)date('j', $timestamp) - 1) / 7);

            $columnIndex = Coordinate::columnIndexFromString($baseColumn) + ($weekOffset * 7);
            $targetColumn = Coordinate::stringFromColumnIndex($columnIndex);

            return $targetColumn . $baseRow;
        }

        /*
        MONTHLY → tetap di base cell awal bulan
        */
        if ($frequency === 'monthly') {
            return $baseColumn . $baseRow;
        }

        return $baseColumn . $baseRow;
    }

    private function extractShiftIndex(string $shiftValue, int $maxShiftRows): int
    {
        $shift = strtolower(trim($shiftValue));
        if ($shift === '') {
            return 1;
        }

        if (preg_match('/(\d+)/', $shift, $matches)) {
            $number = (int)$matches[1];
            if ($number > 0) {
                return min($maxShiftRows, $number);
            }
        }

        if (strpos($shift, 'pagi') !== false) {
            return 1;
        }
        if (strpos($shift, 'siang') !== false) {
            return min($maxShiftRows, 2);
        }
        if (strpos($shift, 'malam') !== false) {
            return min($maxShiftRows, 3);
        }

        return 1;
    }

    private function formatChecksheetValue(string $rawValue, ?array $mappedItem): string
    {
        $inputType = (string)($mappedItem['input_type'] ?? 'check');
        $normalized = strtoupper(trim($rawValue));

        if ($inputType === 'check') {
            return in_array($normalized, ['OK', '1', 'TRUE', 'YES', 'Y', 'CHECKED'], true) ? '✓' : $rawValue;
        }

        return $rawValue;
    }

    private function writeLegacyFallbackCell(\PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet, ChecksheetResult $result, $itemResult, string $value): void
    {
        $sheet = $spreadsheet->getActiveSheet();
        $startColumnIndex = Coordinate::columnIndexFromString('P');
        $day = (int)date('j', strtotime((string)$result->submitted_at));
        $shiftNumber = $this->extractShiftIndex((string)$result->shift, 3);

        if (!$itemResult->item) {
            return;
        }

        $sortOrder = (int)$itemResult->item->sort_order;
        $baseRow = 9 + (($sortOrder - 1) * 3);
        $rowFinal = $baseRow + ($shiftNumber - 1);
        $columnLetter = Coordinate::stringFromColumnIndex($startColumnIndex + max(0, $day - 1));
        $sheet->setCellValue($columnLetter . $rowFinal, $value);
    }

    private function sendSpreadsheet(\PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet, string $fileName)
    {
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        Yii::$app->end();
    }

    public function actionExportPdf($id)
    {
        $result = $this->findResult($id);

        [$spreadsheet, $formTemplate] = $this->loadOriginalTemplateSpreadsheet($result);
        $this->fillSpreadsheetWithResults($spreadsheet, [$result], $formTemplate);

        $tempExcel = Yii::getAlias('@runtime/checksheet_' . $id . '.xlsx');
        $tempPdf   = Yii::getAlias('@runtime/checksheet_' . $id . '.pdf');

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($tempExcel);

        $this->convertExcelToPdf($tempExcel, $tempPdf);

        if (!file_exists($tempPdf)) {
            throw new \Exception('PDF gagal dibuat di semua engine.');
        }

        return Yii::$app->response->sendFile($tempPdf, 'checksheet.pdf');
    }

    private function convertExcelToPdf($excelPath, $pdfPath)
    {
        // ====== 1. WINDOWS (PAKAI EXCEL) ======
        if (stripos(PHP_OS, 'WIN') === 0) {

            $excelPath = str_replace('/', '\\', $excelPath);
            $pdfPath   = str_replace('/', '\\', $pdfPath);

            $vbs = '
            Set objExcel = CreateObject("Excel.Application")
            objExcel.Visible = False
            Set objWorkbook = objExcel.Workbooks.Open("' . $excelPath . '")
            objWorkbook.ExportAsFixedFormat 0, "' . $pdfPath . '"
            objWorkbook.Close False
            objExcel.Quit
            ';

            $vbsFile = sys_get_temp_dir() . '\\excel_pdf.vbs';
            file_put_contents($vbsFile, $vbs);

            exec("cscript //nologo " . escapeshellarg($vbsFile));
            unlink($vbsFile);

            return;
        }

        // ====== 2. MAC (PAKAI EXCEL) ======
        if (stripos(PHP_OS, 'DAR') === 0) {

            $script = '
            tell application "Microsoft Excel"
                open POSIX file "' . $excelPath . '"
                set wb to active workbook
                save workbook as wb filename POSIX file "' . $pdfPath . '" file format PDF file format
                close wb saving no
            end tell
            ';

            exec('osascript -e ' . escapeshellarg($script));
            return;
        }

        // ====== 3. LINUX / FALLBACK (LIBREOFFICE) ======
        $command = "libreoffice --headless --convert-to pdf --outdir "
            . escapeshellarg(dirname($pdfPath)) . " "
            . escapeshellarg($excelPath);

        exec($command);
    }
    private function generateOperatorInitial(string $name): string
    {
        $name = trim($name);

        if ($name === '') {
            return '';
        }

        $parts = array_filter(explode(' ', $name));

        // kalau nama lebih dari 1 kata → ambil huruf depan tiap kata
        if (count($parts) > 1) {
            $initial = '';

            foreach ($parts as $part) {
                $initial .= strtoupper(substr($part, 0, 1));
            }

            return substr($initial, 0, 3);
        }

        // kalau nama cuma 1 kata → ambil 3 huruf depan
        return strtoupper(
            substr(
                preg_replace('/\s+/', '', $name),
                0,
                3
            )
        );
    }
    private function getApprovalInitial(?int $userId): string
    {
        if (!$userId) {
            return '';
        }

        $user = \app\models\User::findOne($userId);

        if (!$user) {
            return '';
        }

        $fullName = trim((string)$user->fullname);

        if ($fullName === '') {
            return '';
        }

        return $this->generateOperatorInitial($fullName);
    }
    public function actionPendingApproval()
    {
        $user = Yii::$app->user->identity;

        if (!$user) {
            throw new \yii\web\ForbiddenHttpException('Harus login');
        }

        $query = ChecksheetResult::find();

        if (in_array($user->role, ['subforeman', 'foreman'])) {

            $query->where([
                'approval_status' => 'submitted'
            ]);

        } elseif ($user->role === 'chief') {

            $query->where([
                'approval_status' => 'leader_approved'
            ]);

        } elseif ($user->role === 'manager') {

            $query->where([
                'approval_status' => 'chief_approved'
            ]);

        } else {
            throw new \yii\web\ForbiddenHttpException(
                'Bukan level approval'
            );
        }

        $results = $query
            ->orderBy(['id' => SORT_DESC])
            ->all();

        return $this->render('pending-approval', [
            'results' => $results
        ]);
    }
    public function actionApproveForeman($id)
    {
        $user = Yii::$app->user->identity;

        if (!$user) {
            throw new \yii\web\ForbiddenHttpException('Harus login');
        }

        if (!in_array($user->role, ['subforeman', 'foreman'])) {
            throw new \yii\web\ForbiddenHttpException('Bukan role foreman');
        }

        $model = ChecksheetResult::findOne($id);

        if (!$model) {
            throw new NotFoundHttpException('Data tidak ditemukan');
        }

        if ($model->approval_status !== 'submitted') {
            Yii::$app->session->setFlash(
                'warning',
                'Data sudah diproses sebelumnya.'
            );

            return $this->redirect(['pending-approval']);
        }

        $model->leader_id = $user->id;
        $model->leader_approved_at = date('Y-m-d H:i:s');
        $model->approval_status = 'leader_approved';

        if ($model->save(false)) {
            Yii::$app->session->setFlash(
                'success',
                'Checksheet berhasil di-approve foreman.'
            );
        }

        return $this->redirect(['pending-approval']);
    }
    public function actionApproveChief($id)
    {
        $user = Yii::$app->user->identity;

        if ($user->role !== 'chief') {
            throw new \yii\web\ForbiddenHttpException('Bukan role chief');
        }

        $model = ChecksheetResult::findOne($id);

        if (!$model) {
            throw new NotFoundHttpException('Data tidak ditemukan');
        }

        $model->chief_id = $user->id;
        $model->chief_approved_at = date('Y-m-d H:i:s');
        $model->approval_status = 'chief_approved';

        $model->save(false);

        Yii::$app->session->setFlash(
            'success',
            'Checksheet berhasil di-approve chief.'
        );

        return $this->redirect(['pending-approval']);
    }
    public function actionApproveManager($id)
    {
        $user = Yii::$app->user->identity;

        if (!$user) {
            throw new \yii\web\ForbiddenHttpException('Harus login');
        }

        if ($user->role !== 'manager') {
            throw new \yii\web\ForbiddenHttpException('Bukan role manager');
        }

        $model = ChecksheetResult::findOne($id);

        if (!$model) {
            throw new NotFoundHttpException('Data tidak ditemukan');
        }

        if ($model->approval_status !== 'chief_approved') {
            Yii::$app->session->setFlash(
                'warning',
                'Data belum sampai tahap approval manager.'
            );

            return $this->redirect(['pending-approval']);
        }

        $model->manager_id = $user->id;
        $model->manager_approved_at = date('Y-m-d H:i:s');
        $model->approval_status = 'approved';

        if ($model->save(false)) {
            Yii::$app->session->setFlash(
                'success',
                'Checksheet berhasil di-approve manager.'
            );
        }

        return $this->redirect(['pending-approval']);
    }
    public function actionApprovalHistory()
    {
        $user = Yii::$app->user->identity;

        if (!$user) {
            throw new \yii\web\ForbiddenHttpException('Harus login');
        }

        if ($user->role !== 'admin') {
            throw new \yii\web\ForbiddenHttpException(
                'Hanya admin yang bisa melihat history approval'
            );
        }

        $results = ChecksheetResult::find()
            ->where(['approval_status' => 'approved'])
            ->orderBy(['id' => SORT_DESC])
            ->all();

        return $this->render('approval-history', [
            'results' => $results
        ]);
    }
    public function actionClearNotification()
    {
        $user = Yii::$app->user->identity;

        if (!$user) {
            throw new \yii\web\ForbiddenHttpException('Harus login');
        }

        if (in_array($user->role, ['subforeman', 'foreman'])) {

            ChecksheetResult::updateAll(
                ['leader_notif_read' => 1],
                [
                    'approval_status' => 'submitted'
                ]
            );

        } elseif ($user->role === 'chief') {

            ChecksheetResult::updateAll(
                ['chief_notif_read' => 1],
                [
                    'approval_status' => 'leader_approved'
                ]
            );

        } elseif ($user->role === 'manager') {

            ChecksheetResult::updateAll(
                ['manager_notif_read' => 1],
                [
                    'approval_status' => 'chief_approved'
                ]
            );

        } elseif ($user->role === 'admin') {

            ChecksheetResult::updateAll(
                ['admin_notif_read' => 1],
                [
                    'approval_status' => 'approved'
                ]
            );
        }

        return $this->redirect(Yii::$app->request->referrer);
    }

}
