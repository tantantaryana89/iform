<?php

namespace app\models;

use yii\db\ActiveRecord;

class ChecksheetTemplateMap extends ActiveRecord
{
    public static function tableName()
    {
        return 'checksheet_template_map';
    }
}
