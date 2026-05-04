<?php
namespace app\modules\qrcode\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\QrCode;

class DefaultController extends Controller
{
    public function actionCreate($id, $download = 0)
{
    // Generate QR Code dengan chillerlan/php-qrcode atau library lain
    // atau gunakan online API
    
    // Contoh sederhana dengan GD
    $qrData = "ID MESIN: {$id}";
    $size = 300;
    
    // Buat image putih
    $image = imagecreatetruecolor($size, $size);
    $white = imagecolorallocate($image, 255, 255, 255);
    $blue = imagecolorallocate($image, 0, 102, 255);
    
    imagefilledrectangle($image, 0, 0, $size, $size, $white);
    
    // Di sini Anda perlu library QR Code generator
    // atau gunakan API online
    
    Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
    Yii::$app->response->headers->set('Content-Type', 'image/png');
    
    if ($download) {
        Yii::$app->response->headers->set(
            'Content-Disposition',
            "attachment; filename=\"qrcode-{$id}.png\""
        );
    }
    
    ob_start();
    imagepng($image);
    $content = ob_get_clean();
    imagedestroy($image);
    
    return $content;
}
}