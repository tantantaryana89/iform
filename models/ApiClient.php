<?php

namespace app\models;

use yii\db\ActiveRecord;

class ApiClient extends ActiveRecord
{
    public static function tableName()
    {
        return 'api_client';
    }
}
