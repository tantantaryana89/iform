<?php

namespace app\models;

use yii\db\ActiveRecord;

class ChecksheetResultItem extends ActiveRecord
{
    public static function tableName()
    {
        return 'checksheet_result_item';
    }

    public function getItem()
    {
        return $this->hasOne(\app\models\ChecksheetItem::class, [
            'item_code' => 'item_code'
        ]);
    }
}
