<?php

namespace app\modules\api\controllers;

use Yii;
use yii\rest\Controller;
use yii\web\Response;
use app\models\ApiClient;


class BaseApiController extends Controller
{
    public function behaviors()
    {
        return [
            'contentNegotiator' => [
                'class' => \yii\filters\ContentNegotiator::class,
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
        ];
    }

    public function beforeAction($action)
    {
        $header = (string)Yii::$app->request->headers->get('Authorization', '');
        if ($header === '' || !preg_match('/^Bearer\s+(.+)$/i', $header, $matches)) {
            Yii::$app->response->statusCode = 401;
            Yii::$app->response->format = Response::FORMAT_JSON;
            Yii::$app->response->data = [
                'status' => 'error',
                'message' => 'Authorization Bearer token wajib diisi',
            ];
            return false;
        }

        $token = trim((string)$matches[1]);
        $client = ApiClient::find()
            ->where(['token' => $token, 'is_active' => 1])
            ->one();

        if (!$client) {
            Yii::$app->response->statusCode = 401;
            Yii::$app->response->format = Response::FORMAT_JSON;
            Yii::$app->response->data = [
                'status' => 'error',
                'message' => 'Token tidak valid',
            ];
            return false;
        }

        return parent::beforeAction($action);
    }

    protected function ok($data = [], $message = 'OK')
    {
        return [
            'status' => 'ok',
            'message' => $message,
            'data' => $data
        ];
    }

    protected function error($message, $code = 400)
    {
        Yii::$app->response->statusCode = $code;
        return [
            'status' => 'error',
            'message' => $message
        ];
    }
}
