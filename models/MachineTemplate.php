<?php

namespace app\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

class MachineTemplate extends ActiveRecord
{
    public static function tableName()
    {
        return 'machine_template';
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
            [['no_mesin', 'template_id'], 'required'],
            [['template_id'], 'integer'],
            [['no_mesin'], 'string', 'max' => 64],
            [['no_mesin'], 'unique'],
            ['no_mesin', 'exist', 'targetClass' => DaftarMesin::class, 'targetAttribute' => 'no_mesin'],
            ['template_id', 'exist', 'targetClass' => FormTemplate::class, 'targetAttribute' => 'id'],
        ];
    }

    public function getTemplate()
    {
        return $this->hasOne(FormTemplate::class, ['id' => 'template_id']);
    }
}
