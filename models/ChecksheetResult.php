<?php

namespace app\models;

use yii\db\ActiveRecord;
use app\models\ChecksheetResultItem;

class ChecksheetResult extends ActiveRecord
{
    public static function tableName()
    {
        return 'checksheet_result';
    }

    public function rules()
    {
        return [
            [['file_path'], 'string', 'max' => 255],
            [['file_path'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'file_path' => 'File Excel',
        ];
    }

    public function getItems()
    {
        return $this->hasMany(
            ChecksheetResultItem::class,
            ['result_id' => 'id']
        );
    }

    public function getTemplate()
    {
        return $this->hasOne(
            ChecksheetTemplate::class,
            ['id' => 'template_id']
        );
    }

    // Foreman/Subforeman approval
    public function getLeader()
    {
        return $this->hasOne(
            User::class,
            ['id' => 'leader_id']
        );
    }

    // Chief approval
    public function getChief()
    {
        return $this->hasOne(
            User::class,
            ['id' => 'chief_id']
        );
    }

    // Manager approval
    public function getManager()
    {
        return $this->hasOne(
            User::class,
            ['id' => 'manager_id']
        );
    }
}