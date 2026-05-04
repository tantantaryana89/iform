<?php
namespace app\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * @property int $id
 * @property int $template_id
 * @property string $title
 * @property int $sort_order
 * @property int $created_at
 * @property int $updated_at
 */
class ChecksheetSection extends ActiveRecord
{
    public static function tableName()
    {
        return 'checksheet_section';
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
            [['template_id', 'title'], 'required'],
            [['template_id', 'sort_order'], 'integer'],
            [['title'], 'string', 'max' => 255],

            [['template_id'], 'exist',
                'targetClass' => ChecksheetTemplate::class,
                'targetAttribute' => ['template_id' => 'id'],
            ],
        ];
    }

    public function attributeLabels()
    {
        return [
            'title' => 'Nama Section',
            'sort_order' => 'Urutan',
        ];
    }

    /* ================= RELATIONS ================= */

    public function getTemplate()
    {
        return $this->hasOne(ChecksheetTemplate::class, ['id' => 'template_id']);
    }

    public function getItems()
    {
        return $this->hasMany(ChecksheetItem::class, ['section_id' => 'id'])
            ->orderBy(['sort_order' => SORT_ASC]);
    }
}
