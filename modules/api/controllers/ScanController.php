<?php
namespace app\modules\api\controllers;

use Yii;

class ScanController extends BaseApiController
{
    public function actionIndex()
    {
        $qr = Yii::$app->request->post('qr_code');

        if (!$qr) {
            return $this->error('QR code kosong', 422);
        }

        // sementara dummy
        return $this->success([
            'sheet_id' => 1,
            'machine' => 'MESIN-01',
            'shift' => 'SHIFT-1',
            'form' => [
                [
                    'key' => 'emergency_stop',
                    'label' => 'Emergency Stop',
                    'type' => 'checkbox',
                    'required' => true
                ],
                [
                    'key' => 'oil_level',
                    'label' => 'Oil Level',
                    'type' => 'radio',
                    'options' => ['OK', 'NG']
                ]
            ]
        ], 'QR valid');
    }
}
