<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;

class DaftarMesin extends ActiveRecord
{
    public static function tableName()
    {
        return 'daftar_mesin';
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
            [['no_mesin', 'nama_mesin'], 'required'],

            [['kategori', 'lokasi', 'vendor', 'serial_number', 'tgl_last_maintenance', 'next_maintenance_due', 'qr_code_path'], 'default', 'value' => null],
            [['tgl_last_maintenance', 'next_maintenance_due'], 'safe'],
            [['created_at', 'updated_at'], 'integer'],

            [['no_mesin'], 'string', 'max' => 64],
            [['nama_mesin', 'lokasi', 'vendor', 'serial_number', 'qr_code_path'], 'string', 'max' => 255],
            [['kategori'], 'string', 'max' => 100],
            [['status'], 'string', 'max' => 20],

            [['no_mesin'], 'unique'],
            ['status', 'in', 'range' => array_keys(self::getStatusList())],
        ];
    }

    public static function getStatusList()
    {
        return [
            'active'   => 'Aktif',
            'inactive' => 'Non-Aktif',
            'maint'    => 'Maintenance',
        ];
    }

    public static function getKategoriList()
    {
        return [
            'Injection' => 'Injection',
            'Welding'   => 'Welding',
            'Assembly'  => 'Assembly',
            'Cutting'   => 'Cutting',
            'Painting'  => 'Painting',
            'Press'     => 'Press',
            'Other'     => 'Other',
        ];
    }

    /**
     * Generate QR Code and save PNG + DB path
     */
    public function generateQr()
    {
        $dir = Yii::getAlias('@webroot/qrcode');
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        // QR berisi FULL no_mesin
        $text = $this->no_mesin;

        $qr = new QrCode(
            $text,
            new Encoding('UTF-8'),
            ErrorCorrectionLevel::High,
            300, // ukuran
            10,  // margin
            RoundBlockSizeMode::Margin,
            new Color(0, 51, 153)
        );

        $writer = new PngWriter();
        $result = $writer->write($qr);

        $filePath = Yii::getAlias('@webroot/qrcode/' . $this->id . '.png');
        $result->saveToFile($filePath);

        // simpan path untuk tampil di view
        $this->qr_code_path = "qrcode/{$this->id}.png";
        $this->save(false);
    }
    public function getChecksheetTemplates()
    {
        return $this->hasMany(ChecksheetTemplate::class, ['mesin_id' => 'id']);
    }
}
