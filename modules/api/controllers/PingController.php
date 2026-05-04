<?php
namespace app\modules\api\controllers;

use Yii;
use yii\rest\Controller;

class PingController extends Controller
{
    public function actionIndex()
    {
        $now = new \DateTimeImmutable('now', new \DateTimeZone(Yii::$app->timeZone ?: 'Asia/Jakarta'));

        return [
            'status' => 'ok',
            'message' => 'API ready',
            'time' => $now->format('Y-m-d H:i:s')
        ];
    }
}
