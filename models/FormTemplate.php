<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

class FormTemplate extends ActiveRecord
{
    public $file;        // upload Excel
    public $masterPdf;   // upload PDF master (opsional via form)

    public static function tableName()
    {
        return 'form_template';
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class, // created_at & updated_at
        ];
    }

    public function rules()
    {
        return [
            [['name'], 'required'],

            [['description', 'schema_json'], 'string'],

            [['source_file', 'master_pdf_path'], 'string', 'max' => 255],

            // Excel template (WAJIB saat create)
            [
                ['file'],
                'file',
                'extensions' => ['xlsx'],
                'skipOnEmpty' => false,
                'message' => 'Silakan upload file Excel (.xlsx)',
            ],

            // PDF master (BOLEH kosong, tapi WAJIB sebelum generate PDF)
            [
                ['masterPdf'],
                'file',
                'extensions' => ['pdf'],
                'skipOnEmpty' => true,
                'message' => 'Upload PDF master hasil export Excel',
            ],
        ];
    }

    public function attributeLabels()
    {
        return [
            'name'            => 'Nama Template Form',
            'description'     => 'Deskripsi Template',
            'file'            => 'File Excel Struktur',
            'source_file'     => 'Path File Excel',
            'masterPdf'       => 'PDF Master (Opsional)',
            'master_pdf_path' => 'Path PDF Master',
            'schema_json'     => 'Schema Items',
        ];
    }

    /* ===============================
     * HELPER (ISO CORE)
     * =============================== */

    /**
     * Ambil schema hasil parsing Excel
     */
    public function getSchema(): array
    {
        if (empty($this->schema_json)) {
            return [];
        }

        $schema = json_decode($this->schema_json, true);

        if (!is_array($schema)) {
            return [];
        }

        return $schema;
    }

    /**
     * Shortcut items
     */
    public function getItems(): array
    {
        $schema = $this->getSchema();
        return $schema['items'] ?? [];
    }

    /**
     * Absolute path ke PDF master
     * INI YANG DIPAKAI actionPdf()
     */
    public function getMasterPdfPath(): ?string
    {
        if (!$this->master_pdf_path) {
            return null;
        }

        return Yii::getAlias('@webroot/' . $this->master_pdf_path);
    }

    public function getSourceFilePath(): ?string
    {
        if (!$this->source_file) {
            return null;
        }

        return Yii::getAlias('@webroot/' . $this->source_file);
    }

    public function validateSchemaMappings(): array
    {
        $items = $this->getItems();
        if (empty($items)) {
            return ['Schema template kosong'];
        }

        $errors = [];
        $seenTargets = [];

        foreach ($items as $index => $item) {
            $itemLabel = (string)($item['item_id'] ?? ('item#' . ($index + 1)));
            $conditions = $item['conditions'] ?? [];
            if (is_array($conditions) && !empty($conditions)) {
                foreach ($conditions as $conditionIndex => $condition) {
                    $conditionLabel = $itemLabel . ' kondisi #' . ($conditionIndex + 1);
                    $this->validateExcelTarget($condition['excel'] ?? null, $conditionLabel, $errors, $seenTargets);
                }
                continue;
            }

            $this->validateExcelTarget($item['excel'] ?? null, $itemLabel, $errors, $seenTargets);
        }

        return array_values(array_unique($errors));
    }

    private function validateExcelTarget($excel, string $itemLabel, array &$errors, array &$seenTargets): void
    {
        if (!is_array($excel)) {
            $errors[] = $itemLabel . ': metadata excel tidak tersedia';
            return;
        }

        $sheet = trim((string)($excel['sheet'] ?? ''));
        $cell = strtoupper(trim((string)($excel['cell'] ?? '')));
        $row = (int)($excel['row'] ?? 0);

        if ($sheet === '') {
            $errors[] = $itemLabel . ': excel.sheet kosong';
        }

        if (!preg_match('/^[A-Z]+[1-9][0-9]*$/', $cell)) {
            $errors[] = $itemLabel . ': excel.cell tidak valid';
        }

        if ($row <= 0) {
            $errors[] = $itemLabel . ': excel.row tidak valid';
        }

        if ($sheet !== '' && $cell !== '' && preg_match('/^[A-Z]+[1-9][0-9]*$/', $cell)) {
            $targetKey = $sheet . '::' . $cell;
            if (isset($seenTargets[$targetKey])) {
                $errors[] = $itemLabel . ': duplicate target cell dengan ' . $seenTargets[$targetKey];
            } else {
                $seenTargets[$targetKey] = $itemLabel;
            }
        }
    }

    public function getSchemaValidationSummary(): array
    {
        $items = $this->getItems();
        $errors = $this->validateSchemaMappings();

        return [
            'is_valid' => empty($errors),
            'item_count' => count($items),
            'error_count' => count($errors),
            'errors' => $errors,
        ];
    }
}
