<?php

namespace app\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;


/**
 * @property int $id
 * @property int $template_id
 * @property int $section_id
 * @property string $label
 * @property string $type
 * @property int|null $symbol_id
 * @property int|null $symbol_id_2
 * @property int $sort_order
 * @property int $excel_row_base
 * @property string $shift_json
 * @property string $instruction_json
 * @property int $created_at
 * @property int $updated_at
 */
class ChecksheetItem extends ActiveRecord
{
    public static function tableName()
    {
        return 'checksheet_item';
    }

    /* =====================================================
     * BEHAVIORS
     * ===================================================== */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /* =====================================================
     * RULES (INI KUNCI UTAMA)
     * ===================================================== */
    public function rules()
    {
        return [
            // REQUIRED
            [['template_id', 'section_id', 'label'], 'required'],

            // INTEGER FIELDS
            [
                ['template_id', 'section_id', 'sort_order', 'symbol_id', 'symbol_id_2', 'excel_row_base'],
                'integer'
            ],

            // STRING / ENUM
            [['label'], 'string'],
            [['type'], 'in', 'range' => ['checklist', 'number', 'text_input', 'okng']],

            // JSON FIELDS
            [['shift_json', 'instruction_json'], 'safe'],

            // FK VALIDATION
            [['template_id'], 'exist',
                'targetClass' => ChecksheetTemplate::class,
                'targetAttribute' => ['template_id' => 'id'],
            ],
            [['section_id'], 'exist',
                'targetClass' => ChecksheetSection::class,
                'targetAttribute' => ['section_id' => 'id'],
            ],
            [['symbol_id'], 'exist',
                'targetClass' => ChecksheetSymbol::class,
                'targetAttribute' => ['symbol_id' => 'id'],
                'skipOnEmpty' => true,
            ],
            [['symbol_id_2'], 'exist',
                'targetClass' => ChecksheetSymbol::class,
                'targetAttribute' => ['symbol_id_2' => 'id'],
                'skipOnEmpty' => true,
            ],
        ];
    }

    /* =====================================================
     * LABELS
     * ===================================================== */
    public function attributeLabels()
    {
        return [
            'label'        => 'Label Item',
            'type'         => 'Tipe',
            'symbol_id'    => 'Simbol Utama',
            'symbol_id_2'  => 'Simbol Kedua',
            'sort_order'   => 'Urutan',
        ];
    }

    /* =====================================================
     * RELATIONS
     * ===================================================== */

    public function getTemplate()
    {
        return $this->hasOne(ChecksheetTemplate::class, ['id' => 'template_id']);
    }

    public function getSection()
    {
        return $this->hasOne(ChecksheetSection::class, ['id' => 'section_id']);
    }

    /** Simbol utama */
    public function getSymbol()
    {
        return $this->hasOne(ChecksheetSymbol::class, ['id' => 'symbol_id']);
    }

    /** Simbol kedua */
    public function getSymbol2()
    {
        return $this->hasOne(ChecksheetSymbol::class, ['id' => 'symbol_id_2']);
    }

    /* =====================================================
     * SHIFT HELPERS
     * ===================================================== */

    public function getShiftArray(): array
    {
        if (empty($this->shift_json)) {
            return [];
        }

        $data = json_decode($this->shift_json, true);
        return is_array($data) ? $data : [];
    }

    public function setShift(array $shift)
    {
        $this->shift_json = json_encode(array_values($shift));
    }

    /* =====================================================
     * INSTRUCTION HELPERS
     * ===================================================== */

    public function getInstruction(): array
    {
        if (empty($this->instruction_json)) {
            return $this->getDefaultInstruction();
        }

        $data = json_decode($this->instruction_json, true);
        if (!is_array($data)) {
            return $this->getDefaultInstruction();
        }

        return $this->normalizeInstruction($data);
    }

    public function setInstruction(array $instruction)
    {
        $this->instruction_json = json_encode($this->normalizeInstruction($instruction), JSON_UNESCAPED_UNICODE);
    }

    public function getConditionRows(): array
    {
        $instruction = $this->getInstruction();
        return $instruction['conditions'] ?? [];
    }

    public function getShift(): array
    {
        return $this->getShiftArray();
    }
    public function beforeValidate()
    {
        if ($this->isNewRecord && empty($this->item_code)) {
            $this->item_code = 'ITEM-' . time() . '-' . rand(100,999);
        }
        return parent::beforeValidate();
    }

    public function beforeSave($insert)
    {
        if (empty($this->item_code)) {
            $this->item_code = 'ITEM-' . time() . '-' . rand(100, 999);
        }

        if (empty($this->type)) {
            $this->type = 'checklist';
        }

        if ((int)$this->excel_row_base <= 0) {
            $fallbackRow = (int)$this->sort_order;
            $this->excel_row_base = $fallbackRow > 0 ? $fallbackRow : 1;
        }

        if (empty($this->shift_json)) {
            $this->shift_json = json_encode(['1']);
        }

        if (empty($this->instruction_json)) {
            $this->instruction_json = json_encode($this->getDefaultInstruction(), JSON_UNESCAPED_UNICODE);
        } else {
            $decodedInstruction = json_decode((string)$this->instruction_json, true);
            $this->instruction_json = json_encode(
                $this->normalizeInstruction(is_array($decodedInstruction) ? $decodedInstruction : []),
                JSON_UNESCAPED_UNICODE
            );
        }

        return parent::beforeSave($insert);
    }

    private function getDefaultInstruction(): array
    {
        return [
            'standard' => [],
            'cara' => [],
            'frekuensi' => [],
            'note' => [],
            'conditions' => [],
        ];
    }

    private function normalizeInstruction(array $instruction): array
    {
        $conditions = [];
        $rawConditions = $instruction['conditions'] ?? [];

        if (is_array($rawConditions)) {
            foreach ($rawConditions as $row) {
                if (!is_array($row)) {
                    continue;
                }

                $normalizedRow = [
                    'standard' => trim((string)($row['standard'] ?? '')),
                    'cara' => trim((string)($row['cara'] ?? '')),
                    'frekuensi' => trim((string)($row['frekuensi'] ?? '')),
                    'note' => trim((string)($row['note'] ?? '')),
                ];

                if (implode('', $normalizedRow) === '') {
                    continue;
                }

                $conditions[] = $normalizedRow;
            }
        }

        if (empty($conditions)) {
            $standards = is_array($instruction['standard'] ?? null) ? $instruction['standard'] : [];
            $caras = is_array($instruction['cara'] ?? null) ? $instruction['cara'] : [];
            $frequencies = is_array($instruction['frekuensi'] ?? null) ? $instruction['frekuensi'] : [];
            $notes = is_array($instruction['note'] ?? null) ? $instruction['note'] : [];
            $maxRows = max(count($standards), count($caras), count($frequencies), count($notes));

            for ($index = 0; $index < $maxRows; $index++) {
                $normalizedRow = [
                    'standard' => trim((string)($standards[$index] ?? '')),
                    'cara' => trim((string)($caras[$index] ?? '')),
                    'frekuensi' => trim((string)($frequencies[$index] ?? '')),
                    'note' => trim((string)($notes[$index] ?? '')),
                ];

                if (implode('', $normalizedRow) === '') {
                    continue;
                }

                $conditions[] = $normalizedRow;
            }
        }

        return [
            'standard' => array_values(array_filter(array_map(static fn ($row) => $row['standard'], $conditions), static fn ($value) => $value !== '')),
            'cara' => array_values(array_filter(array_map(static fn ($row) => $row['cara'], $conditions), static fn ($value) => $value !== '')),
            'frekuensi' => array_values(array_filter(array_map(static fn ($row) => $row['frekuensi'], $conditions), static fn ($value) => $value !== '')),
            'note' => array_values(array_filter(array_map(static fn ($row) => $row['note'], $conditions), static fn ($value) => $value !== '')),
            'conditions' => $conditions,
        ];
    }

}
