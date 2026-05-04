<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\UploadedFile;

class ChecksheetSymbol extends ActiveRecord
{
    /** @var UploadedFile */
    public $imageFile;

    public static function tableName()
    {
        return 'checksheet_symbol';
    }

    public function rules()
    {
        return [
            [['code', 'name'], 'required'],
            [['description'], 'string'],
            [['code'], 'string', 'max' => 50],
            [['name'], 'string', 'max' => 100],
            [['image_path'], 'string', 'max' => 255],
            [['is_active'], 'integer'],

            [['code'], 'unique'],

            [['imageFile'], 'file',
                'skipOnEmpty' => false,
                'extensions' => ['png', 'svg'],
                'checkExtensionByMimeType' => false,
                'on' => 'create'
            ],

            [['imageFile'], 'file',
                'skipOnEmpty' => true,
                'extensions' => ['png', 'svg'],
                'checkExtensionByMimeType' => false,
                'on' => 'update'
            ],
        ];
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();

        $scenarios['create'] = [
            'code',
            'name',
            'description',
            'imageFile',
            'image_path', // ⬅️ INI KUNCINYA
            'is_active'
        ];

        $scenarios['update'] = [
            'name',
            'description',
            'imageFile',
            'image_path', // ⬅️ JUGA
            'is_active'
        ];

        return $scenarios;
    }

    public function upload()
    {
        if (!$this->imageFile) {
            return true;
        }

        $dir = Yii::getAlias('@webroot/uploads/symbols');
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $filename = strtolower($this->code) . '.' . $this->imageFile->extension;
        $path = $dir . '/' . $filename;

        if ($this->imageFile->saveAs($path)) {
            $this->image_path = '/uploads/symbols/' . $filename;
            return true;
        }

        return false;
    }
    public function getSymbol2()
    {
        return $this->hasOne(ChecksheetSymbol::class, ['id' => 'symbol_id_2']);
    }
}
