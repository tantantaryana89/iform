<?php

namespace app\modules\api\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use app\models\DaftarMesin;

class MesinController extends Controller
{
    public $enableCsrfValidation = false;

    public function beforeAction($action)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return parent::beforeAction($action);
    }

    /**
     * GET /api/mesin?no_mesin=xxx
     */
    public function actionIndex()
    {
        $noMesin = Yii::$app->request->get('no_mesin');

        if (!$noMesin) {
            return [
                'status' => 'error',
                'message' => 'Parameter no_mesin wajib diisi'
            ];
        }

        $model = DaftarMesin::findOne(['no_mesin' => $noMesin]);

        if (!$model) {
            return [
                'status' => 'error',
                'message' => 'Mesin tidak ditemukan'
            ];
        }

        return [
            'status' => 'ok',
            'data' => [
                'id' => $model->id,
                'no_mesin' => $model->no_mesin,
                'nama_mesin' => $model->nama_mesin,
                'kategori' => $model->kategori,
                'lokasi' => $model->lokasi,
                'status' => $model->status,
                'vendor' => $model->vendor,
                'serial_number' => $model->serial_number,
                'tgl_last_maintenance' => $model->tgl_last_maintenance,
                'next_maintenance_due' => $model->next_maintenance_due,
                'qr_image_url' => Yii::$app->request->hostInfo . '/qrcode/' . $model->id . '.png',
            ]
        ];
    }
}
