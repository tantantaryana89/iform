<?php

namespace app\models;

use yii\base\Model;

class MassApprovalForm extends Model
{
    public $ids = [];
    public $pin;

    public function rules()
    {
        return [
            ['ids', 'required', 'message' => 'Pilih setidaknya satu notifikasi.'],
            ['ids', 'each', 'rule' => ['integer']],
            ['pin', 'required', 'on' => 'approve', 'message' => 'PIN diperlukan untuk menyetujui.'],
            ['pin', 'string', 'min' => 4, 'max' => 32],
        ];
    }

    public function attributeLabels()
    {
        return [
            'ids' => 'Notifikasi Terpilih',
            'pin' => 'PIN Persetujuan',
        ];
    }
}
