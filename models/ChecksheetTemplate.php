<?php
namespace app\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

class ChecksheetTemplate extends ActiveRecord
{
    public static function tableName()
    {
        return 'checksheet_template';
    }

    /**
     * =========================
     * AUTO TIMESTAMP
     * =========================
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * =========================
     * VALIDATION RULES
     * =========================
     */
    public function rules()
    {
        return [
            // wajib
            [['name', 'mesin_id', 'version', 'status'], 'required'],

            // tipe data
            [['mesin_id', 'version', 'created_at', 'updated_at'], 'integer'],

            // string
            [['name'], 'string', 'max' => 255],
            [['status'], 'in', 'range' => ['draft', 'active']],

            // relasi mesin
            [['mesin_id'], 'exist',
                'targetClass' => DaftarMesin::class,
                'targetAttribute' => ['mesin_id' => 'id'],
                'message' => 'Mesin tidak valid',
            ],
        ];
    }

    /**
     * =========================
     * LABEL
     * =========================
     */
    public function attributeLabels()
    {
        return [
            'name'       => 'Nama Template',
            'mesin_id'   => 'Mesin',
            'version'    => 'Versi',
            'status'     => 'Status',
            'created_at' => 'Dibuat Pada',
            'updated_at' => 'Diubah Pada',
        ];
    }

    /**
     * =========================
     * RELATIONS
     * =========================
     */

    // Mesin (untuk API header)
    public function getMesin()
    {
        return $this->hasOne(DaftarMesin::class, ['id' => 'mesin_id']);
    }

    // Section (UNTUK API FINAL)
    public function getSections()
    {
        return $this->hasMany(ChecksheetSection::class, ['template_id' => 'id'])
            ->orderBy(['sort_order' => SORT_ASC]);
    }

    /**
     * =========================
     * HELPER
     * =========================
     */

    /**
     * Apakah template siap dipakai (API guard)
     */
    public function isValidForUse(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        if (!$this->sections) {
            return false;
        }

        foreach ($this->sections as $section) {
            if (!$section->items) {
                return false;
            }
        }

        return true;
    }
}
