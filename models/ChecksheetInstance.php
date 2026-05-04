<?php

namespace app\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

class ChecksheetInstance extends ActiveRecord
{
    public static function tableName()
    {
        return 'checksheet_instance';
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    public function rules()
    {
        return [
            [['template_id', 'mesin_id', 'tanggal', 'shift'], 'required'],
            [['template_id', 'mesin_id', 'shift'], 'integer'],
            [['tanggal'], 'safe'],
            [['operator_id'], 'string', 'max' => 50],
            [['status'], 'in', 'range' => ['draft', 'submitted', 'approved']],
        ];
    }

    /* ===============================
     * RELATIONS
     * =============================== */

    public function getTemplate()
    {
        return $this->hasOne(FormTemplate::class, ['id' => 'template_id']);
    }

    public function getMesin()
    {
        return $this->hasOne(DaftarMesin::class, ['id' => 'mesin_id']);
    }

    public function getAnswers()
    {
        return $this->hasMany(ChecksheetAnswer::class, ['instance_id' => 'id']);
    }
}
