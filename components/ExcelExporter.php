<?php
namespace app\components;

use Yii;
use app\models\FormResult;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExcelExporter
{
    public function exportFormResult(FormResult $formResult)
    {
        $spreadsheet = $this->buildTemplateSpreadsheet($formResult);

        if ($spreadsheet === null) {
            $spreadsheet = $this->buildGenericSpreadsheet($formResult);
        }

        $fileName = 'form_result_' . $formResult->id . '_' . date('YmdHis') . '.xlsx';
        $filePath = Yii::getAlias('@runtime/export') . '/' . $fileName;

        $dir = Yii::getAlias('@runtime/export');
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        (new Xlsx($spreadsheet))->save($filePath);

        return $filePath;
    }

    public function exportMultipleResults($formResults, $fileName = 'form_results')
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);

        $sheetIndex = 0;
        foreach ($formResults as $formResult) {
            $sheet = $spreadsheet->createSheet($sheetIndex++);
            $sheet->setTitle('Form_' . $formResult->id);
            $this->fillGenericSheet($sheet, $formResult);
        }

        $filePath = Yii::getAlias('@runtime/export') . '/' . $fileName . '_' . date('YmdHis') . '.xlsx';
        $dir = Yii::getAlias('@runtime/export');
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        (new Xlsx($spreadsheet))->save($filePath);

        return $filePath;
    }

    private function buildTemplateSpreadsheet(FormResult $formResult): ?Spreadsheet
    {
        $template = $formResult->template;
        Yii::info([
            'form_result_id' => $formResult->id,
            'template_exists' => $template ? true : false,
        ], 'excel_export_start');

        if (!$template) {
            return null;
        }

        $templatePath = $template->getSourceFilePath();
        if (!$templatePath || !is_file($templatePath)) {
            Yii::info([
                'template_path' => $templatePath,
                'file_exists'   => is_file((string)$templatePath),
            ], 'excel_export_template_missing');
            return null;
        }

        $schema = $template->getSchema();
        $items  = $template->getItems();
        if (empty($items)) {
            Yii::info(['items' => $items], 'excel_export_items_empty');
            return null;
        }

        $dynamicConfig = $this->resolveDynamicConfig($schema);

        $spreadsheet  = IOFactory::load($templatePath);
        $sheet        = $spreadsheet->getActiveSheet();
        $answerMap    = $this->buildAnswerMap($formResult);
        $resultColumn = $this->resolveResultColumn($sheet);
        $stats        = $this->collectTemplateStats($items, $answerMap);

        $sheet->setCellValue($resultColumn . '1', 'Hasil Digital');
        $sheet->setCellValue($resultColumn . '2', 'Operator: ' . $formResult->operator);
        $sheet->setCellValue($resultColumn . '3', 'Tanggal: ' . $formResult->tanggal . ' | Shift: ' . $formResult->shift);
        $sheet->setCellValue($resultColumn . '4', 'Total item: ' . $stats['total']);
        $sheet->setCellValue($resultColumn . '5', 'Terisi: ' . $stats['filled']);
        $sheet->setCellValue($resultColumn . '6', 'Wajib belum terisi: ' . ($stats['missing_required_count'] > 0 ? implode(', ', $stats['missing_required']) : '0'));

        // Ambil meta_mapping dan hitung shift index sekali saja
        $metaMapping = $schema['meta_mapping'] ?? [];
        $shiftIndex  = $this->extractShiftIndex((string)$formResult->shift, 3);

        // DEBUG LOG — sangat berguna untuk diagnosis jika initial masih tidak muncul
        Yii::info([
            'form_result_id' => $formResult->id,
            'shift'          => $formResult->shift,
            'shift_index'    => $shiftIndex,
            'meta_mapping'   => $metaMapping,
            'schema_keys'    => array_keys($schema),
            'operator_raw'   => $formResult->operator,
            'leader_id'      => $formResult->leader_id ?? null,
            'chief_id'       => $formResult->chief_id ?? null,
            'manager_id'     => $formResult->manager_id ?? null,
        ], 'excel_export_debug');

        /**
         * ================================================================
         * WRITE OPERATOR INITIAL
         * ----------------------------------------------------------------
         * Operator disimpan sebagai plain string (kolom `operator` varchar)
         * di tabel form_result — langsung pakai $formResult->operator.
         *
         * Cell target diambil STATIS dari meta_mapping.
         * TIDAK menggunakan resolveDynamicCell karena fungsi itu akan
         * menggeser kolom berdasarkan tanggal & baris berdasarkan shift,
         * cocok untuk item check tapi SALAH untuk cell initial yang statis.
         * ================================================================
         */
        $operatorCellKey = 'operator_shift_' . $shiftIndex;

        if (!empty($metaMapping[$operatorCellKey]) && !empty($formResult->operator)) {
            $operatorCell    = strtoupper(trim((string)$metaMapping[$operatorCellKey]));
            $operatorInitial = $this->generateInitial((string)$formResult->operator);

            $sheet->setCellValue($operatorCell, $operatorInitial);

            Yii::info([
                'operator_cell_key' => $operatorCellKey,
                'operator_cell'     => $operatorCell,
                'operator_initial'  => $operatorInitial,
            ], 'excel_export_operator_initial');
        } else {
            Yii::warning([
                'reason'                  => 'operator initial tidak ditulis',
                'operator_cell_key'       => $operatorCellKey,
                'meta_mapping_key_exists' => isset($metaMapping[$operatorCellKey]),
                'operator_empty'          => empty($formResult->operator),
            ], 'excel_export_operator_skip');
        }

        /**
         * ================================================================
         * WRITE LEADER INITIAL
         * ----------------------------------------------------------------
         * Leader disimpan sebagai FK (leader_id → tabel user).
         * $formResult->leader mengembalikan objek User, bukan string.
         * Nama diambil dari ->fullname sesuai model User.
         * ================================================================
         */
        $leaderCellKey = 'leader_shift_' . $shiftIndex;

        if (!empty($metaMapping[$leaderCellKey])) {
            $leaderUser = $formResult->leader; // objek User atau null
            $leaderName = ($leaderUser !== null) ? trim((string)$leaderUser->fullname) : '';

            if ($leaderName !== '') {
                $leaderCell    = strtoupper(trim((string)$metaMapping[$leaderCellKey]));
                $leaderInitial = $this->generateInitial($leaderName);

                $sheet->setCellValue($leaderCell, $leaderInitial);

                Yii::info([
                    'leader_cell_key' => $leaderCellKey,
                    'leader_cell'     => $leaderCell,
                    'leader_name'     => $leaderName,
                    'leader_initial'  => $leaderInitial,
                ], 'excel_export_leader_initial');
            } else {
                Yii::info([
                    'reason'          => 'leader initial tidak ditulis — leader belum approve atau nama kosong',
                    'leader_cell_key' => $leaderCellKey,
                    'leader_id'       => $formResult->leader_id ?? null,
                ], 'excel_export_leader_skip');
            }
        }

        /**
         * ================================================================
         * WRITE CHIEF INITIAL
         * ----------------------------------------------------------------
         * Chief disimpan sebagai FK (chief_id → tabel user).
         * $formResult->chief mengembalikan objek User, bukan string.
         * ================================================================
         */
        if (!empty($metaMapping['chief'])) {
            $chiefUser = $formResult->chief; // objek User atau null
            $chiefName = ($chiefUser !== null) ? trim((string)$chiefUser->fullname) : '';

            if ($chiefName !== '') {
                $chiefCell    = strtoupper(trim((string)$metaMapping['chief']));
                $chiefInitial = $this->generateInitial($chiefName);

                $sheet->setCellValue($chiefCell, $chiefInitial);

                Yii::info([
                    'chief_cell'    => $chiefCell,
                    'chief_name'    => $chiefName,
                    'chief_initial' => $chiefInitial,
                ], 'excel_export_chief_initial');
            } else {
                Yii::info([
                    'reason'   => 'chief initial tidak ditulis — chief belum approve atau nama kosong',
                    'chief_id' => $formResult->chief_id ?? null,
                ], 'excel_export_chief_skip');
            }
        }

        /**
         * ================================================================
         * WRITE MANAGER INITIAL
         * ----------------------------------------------------------------
         * Manager disimpan sebagai FK (manager_id → tabel user).
         * $formResult->manager mengembalikan objek User, bukan string.
         * ================================================================
         */
        if (!empty($metaMapping['manager'])) {
            $managerUser = $formResult->manager; // objek User atau null
            $managerName = ($managerUser !== null) ? trim((string)$managerUser->fullname) : '';

            if ($managerName !== '') {
                $managerCell    = strtoupper(trim((string)$metaMapping['manager']));
                $managerInitial = $this->generateInitial($managerName);

                $sheet->setCellValue($managerCell, $managerInitial);

                Yii::info([
                    'manager_cell'    => $managerCell,
                    'manager_name'    => $managerName,
                    'manager_initial' => $managerInitial,
                ], 'excel_export_manager_initial');
            } else {
                Yii::info([
                    'reason'     => 'manager initial tidak ditulis — manager belum approve atau nama kosong',
                    'manager_id' => $formResult->manager_id ?? null,
                ], 'excel_export_manager_skip');
            }
        }

        /**
         * ================================================================
         * WRITE ITEM CHECK VALUES
         * ================================================================
         */
        foreach ($items as $item) {
            $itemId = (string)($item['item_id'] ?? '');
            if ($itemId === '' || !array_key_exists($itemId, $answerMap)) {
                continue;
            }

            $value = $this->formatExcelValue($answerMap[$itemId], $item);
            if ($value === '') {
                continue;
            }

            if ($this->writeValueToMappedTargets($spreadsheet, $item, $formResult, $dynamicConfig, $value)) {
                continue;
            }

            if (!empty($item['excel']['sheet']) && !empty($item['excel']['cell'])) {
                $targetSheet = $spreadsheet->getSheetByName((string)$item['excel']['sheet']);
                if ($targetSheet) {
                    $resolvedCell = $this->resolveDynamicCell((array)$item, $formResult, $dynamicConfig);
                    $targetSheet->setCellValue($resolvedCell, $value);
                    continue;
                }
            }

            if (!empty($item['excel']['sheet']) && !empty($item['excel']['row'])) {
                $targetSheet = $spreadsheet->getSheetByName((string)$item['excel']['sheet']);
                if ($targetSheet) {
                    $targetResultColumn = $this->resolveResultColumn($targetSheet);
                    $targetSheet->setCellValue($targetResultColumn . (int)$item['excel']['row'], $value);
                    continue;
                }
            }

            $sourceRow = (int)($item['source_row'] ?? 0);
            if ($sourceRow > 0) {
                $sheet->setCellValue($resultColumn . $sourceRow, $value);
            }
        }

        return $spreadsheet;
    }

    /**
     * Generate initial dari nama lengkap.
     *
     * Lebih dari 1 kata → huruf pertama tiap kata, maks 3 huruf.
     *   "Tantan Taryana"       → "TT"
     *   "Budi Santoso Wibowo"  → "BSW"
     *
     * 1 kata → 3 huruf pertama.
     *   "Tantan" → "TAN"
     */
    private function generateInitial(string $name): string
    {
        $parts = array_values(array_filter(explode(' ', trim($name))));

        if (count($parts) > 1) {
            $initial = '';
            foreach ($parts as $part) {
                $initial .= strtoupper(substr($part, 0, 1));
            }
            return substr($initial, 0, 3);
        }

        return strtoupper(substr(preg_replace('/\s+/', '', $name), 0, 3));
    }

    /**
     * @deprecated Gunakan generateInitial() — dipertahankan agar tidak ada method orphan.
     */
    private function generateOperatorInitial(string $name): string
    {
        return $this->generateInitial($name);
    }

    private function writeValueToMappedTargets(Spreadsheet $spreadsheet, array $item, FormResult $formResult, array $dynamicConfig, string $value): bool
    {
        $targets = [];

        if (!empty($item['conditions']) && is_array($item['conditions'])) {
            foreach ($item['conditions'] as $condition) {
                if (!empty($condition['excel']) && is_array($condition['excel'])) {
                    $targets[] = $condition['excel'];
                }
            }
        }

        if (empty($targets)) {
            return false;
        }

        $wrote = false;
        foreach ($targets as $target) {
            $sheetName = (string)($target['sheet'] ?? '');
            $cell      = (string)($target['cell'] ?? '');
            if ($sheetName === '' || $cell === '') {
                continue;
            }

            $targetSheet = $spreadsheet->getSheetByName($sheetName);
            if ($targetSheet === null) {
                continue;
            }

            $targetItem   = ['excel' => $target];
            $resolvedCell = $this->resolveDynamicCell($targetItem, $formResult, $dynamicConfig);
            $targetSheet->setCellValue($resolvedCell, $value);
            $wrote = true;
        }

        return $wrote;
    }

    private function resolveDynamicConfig(array $schema): array
    {
        $cfg = (array)($schema['excel_dynamic'] ?? []);

        return [
            'enabled'        => array_key_exists('enabled', $cfg) ? (bool)$cfg['enabled'] : true,
            'mode'           => (string)($cfg['mode'] ?? 'matrix_shift_day'),
            'base_day'       => max(1, (int)($cfg['base_day'] ?? 1)),
            'max_shift_rows' => max(1, (int)($cfg['max_shift_rows'] ?? 3)),
        ];
    }

    private function resolveDynamicCell(array $item, FormResult $formResult, array $dynamicConfig): string
    {
        $baseCell = strtoupper(trim((string)($item['excel']['cell'] ?? '')));
        if (!preg_match('/^([A-Z]+)([1-9][0-9]*)$/', $baseCell, $matches)) {
            return $baseCell;
        }

        $baseColumn = $matches[1];
        $baseRow    = (int)$matches[2];

        if (empty($dynamicConfig['enabled'])) {
            return $baseColumn . $baseRow;
        }

        $dateTs = strtotime((string)$formResult->tanggal);
        if ($dateTs === false) {
            return $baseColumn . $baseRow;
        }

        $dayOfMonth = (int)date('j', $dateTs);
        $baseDay    = max(1, (int)$dynamicConfig['base_day']);
        $dayOffset  = max(0, $dayOfMonth - $baseDay);

        $mode = (string)($dynamicConfig['mode'] ?? 'matrix_shift_day');
        if ($mode !== 'matrix_shift_day') {
            return $baseColumn . $baseRow;
        }

        $shiftIndex   = $this->extractShiftIndex((string)$formResult->shift, (int)$dynamicConfig['max_shift_rows']);
        $baseColIndex = Coordinate::columnIndexFromString($baseColumn);
        $targetCol    = Coordinate::stringFromColumnIndex($baseColIndex + $dayOffset);
        $targetRow    = $baseRow + max(0, $shiftIndex - 1);

        return $targetCol . $targetRow;
    }

    private function extractShiftIndex(string $shiftValue, int $maxShiftRows): int
    {
        $shift = strtolower(trim($shiftValue));
        if ($shift === '') {
            return 1;
        }

        if (preg_match('/(\d+)/', $shift, $m)) {
            $n = (int)$m[1];
            if ($n > 0) {
                return min($maxShiftRows, $n);
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

    private function buildGenericSpreadsheet(FormResult $formResult): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();

        $row = 1;
        $sheet->setCellValue('A' . $row, 'LAPORAN FORM');
        $sheet->mergeCells('A' . $row . ':G' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(12);

        $row += 2;
        $sheet->setCellValue('A' . $row, 'Mesin:');
        $sheet->setCellValue('B' . $row, $formResult->no_mesin);

        $row++;
        $sheet->setCellValue('A' . $row, 'Operator:');
        $sheet->setCellValue('B' . $row, $formResult->operator);

        $row++;
        $sheet->setCellValue('A' . $row, 'Tanggal:');
        $sheet->setCellValue('B' . $row, $formResult->tanggal);

        $row++;
        $sheet->setCellValue('A' . $row, 'Shift:');
        $sheet->setCellValue('B' . $row, $formResult->shift);

        $row += 2;
        $headers = ['No', 'Section', 'Item ID', 'Item', 'Wajib', 'Standard', 'Hasil'];
        foreach ($headers as $index => $header) {
            $column = Coordinate::stringFromColumnIndex($index + 1);
            $sheet->setCellValue($column . $row, $header);
        }
        $sheet->getStyle('A' . $row . ':G' . $row)->getFont()->setBold(true);

        $row++;
        $items     = $formResult->template ? $formResult->template->getItems() : [];
        $answerMap = $this->buildAnswerMap($formResult);

        if (!empty($items)) {
            foreach ($items as $item) {
                $itemId = (string)($item['item_id'] ?? '');
                $sheet->setCellValue('A' . $row, $item['no'] ?? '');
                $sheet->setCellValue('B' . $row, $item['section'] ?? '');
                $sheet->setCellValue('C' . $row, $itemId);
                $sheet->setCellValue('D' . $row, $item['label'] ?? '');
                $sheet->setCellValue('E' . $row, !empty($item['required']) ? 'Ya' : 'Tidak');
                $sheet->setCellValue('F' . $row, $item['standard'] ?? '');
                $sheet->setCellValue('G' . $row, $this->formatExcelValue($answerMap[$itemId] ?? null, $item));
                $row++;
            }
        } else {
            foreach ($formResult->getDetails()->all() as $index => $detail) {
                $sheet->setCellValue('A' . $row, $index + 1);
                $sheet->setCellValue('C' . $row, $detail->field_name);
                $sheet->setCellValue('D' . $row, $detail->field_name);
                $sheet->setCellValue('G' . $row, $detail->field_value);
                $row++;
            }
        }

        foreach (range('A', 'G') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        return $spreadsheet;
    }

    private function fillGenericSheet($sheet, FormResult $formResult)
    {
        $genericSpreadsheet = $this->buildGenericSpreadsheet($formResult);
        $sourceSheet        = $genericSpreadsheet->getActiveSheet();

        $highestRow    = $sourceSheet->getHighestRow();
        $highestColumn = Coordinate::columnIndexFromString($sourceSheet->getHighestColumn());

        for ($row = 1; $row <= $highestRow; $row++) {
            for ($column = 1; $column <= $highestColumn; $column++) {
                $coordinate = Coordinate::stringFromColumnIndex($column) . $row;
                $sheet->setCellValue($coordinate, $sourceSheet->getCell($coordinate)->getValue());
            }
        }

        foreach (range(1, $highestColumn) as $column) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($column))->setAutoSize(true);
        }
    }

    private function buildAnswerMap(FormResult $formResult): array
    {
        $map = [];
        foreach ($formResult->getDetails()->all() as $detail) {
            $map[$detail->field_name] = $detail->field_value;
        }

        return $map;
    }

    private function resolveResultColumn($sheet): string
    {
        $highestColumn = Coordinate::columnIndexFromString($sheet->getHighestDataColumn());

        return Coordinate::stringFromColumnIndex($highestColumn + 1);
    }

    private function collectTemplateStats(array $items, array $answerMap): array
    {
        $total           = 0;
        $filled          = 0;
        $missingRequired = [];

        foreach ($items as $item) {
            $itemId = (string)($item['item_id'] ?? '');
            if ($itemId === '') {
                continue;
            }

            $total++;
            $hasValue = array_key_exists($itemId, $answerMap) && !$this->isEmptyValue($answerMap[$itemId]);
            if ($hasValue) {
                $filled++;
            }

            if (!empty($item['required']) && !$hasValue) {
                $missingRequired[] = $itemId;
            }
        }

        return [
            'total'                  => $total,
            'filled'                 => $filled,
            'missing_required'       => $missingRequired,
            'missing_required_count' => count($missingRequired),
        ];
    }

    private function isEmptyValue($value): bool
    {
        if ($value === null) {
            return true;
        }

        if (is_array($value)) {
            return empty($value);
        }

        return trim((string)$value) === '';
    }

    private function formatExcelValue($value, array $item = []): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $value = $decoded;
            }
        }

        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        $stringValue = trim((string)$value);
        $inputType   = (string)($item['input_type'] ?? '');
        if ($inputType === 'check') {
            $normalized = strtolower($stringValue);
            if (in_array($normalized, ['1', 'true', 'ok', 'yes', 'y', 'check', 'checked'], true)) {
                return '✓';
            }
        }

        return $stringValue;
    }
}