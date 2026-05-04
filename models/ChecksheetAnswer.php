<?php

namespace app\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

class ChecksheetAnswer extends ActiveRecord
{
    public static function tableName()
    {
        return 'checksheet_answer';
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
            [['instance_id', 'item_id', 'value'], 'required'],
            [['instance_id', 'value'], 'integer'],
            [['note'], 'string'],
            [['item_id'], 'string', 'max' => 50],
        ];
    }

    public function getInstance()
    {
        return $this->hasOne(ChecksheetInstance::class, ['id' => 'instance_id']);
    }
    public function getItem()
    {
        return $this->hasOne(ChecksheetItem::class, ['id' => 'item_id']);
    }
}
