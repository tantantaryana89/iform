<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class FormResultDetail extends ActiveRecord
{
    public static function tableName()
    {
        return 'form_result_detail';
    }

    public function rules()
    {
        return [
            [['form_result_id', 'field_name'], 'required'],
            [['form_result_id'], 'integer'],
            [['field_value'], 'string'],
            [['field_name'], 'string', 'max' => 255],
        ];
    }

    public function getFormResult()
    {
        return $this->hasOne(FormResult::class, ['id' => 'form_result_id']);
    }
}
