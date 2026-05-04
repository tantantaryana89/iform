<?php
namespace app\models;

use yii\base\Model;

class ApprovalForm extends Model
{
    public $pin;

    public function rules()
    {
        return [
            [['pin'], 'required'],
            ['pin', 'string', 'min' => 4, 'max' => 32],
        ];
    }

    public function attributeLabels()
    {
        return [
            'pin' => 'PIN Persetujuan',
        ];
    }
}
