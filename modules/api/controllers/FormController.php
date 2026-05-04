<?php

namespace app\modules\api\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use app\models\FormResult;
use app\models\FormResultDetail;

class FormController extends Controller
{
    public $enableCsrfValidation = false;

    public function beforeAction($action)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return parent::beforeAction($action);
    }

    /**
     * POST /api/form/save
     * Body JSON:
     * {
     *   "no_mesin": "MC-001",
     *   "operator": "12345",
     *   "tanggal": "2025-11-22",
     *   "shift": "2",
     *   "hasil": {
     *       "safety_guard": "OK",
     *       "cooling_fan": "NG",
     *       "note": "Fan agak bergetar"
     *   }
     * }
     */
    public function actionSave()
    {
        $data = Yii::$app->request->post();

        // Validasi minimal
        $required = ['no_mesin', 'operator', 'tanggal', 'shift', 'hasil'];

        foreach ($required as $field) {
            if (!isset($data[$field])) {
                return ['status' => 'error', 'message' => "$field wajib diisi"];
            }
        }

        // Simpan header
        $form = new FormResult();
        $form->no_mesin = $data['no_mesin'];
        $form->operator = $data['operator'];
        $form->tanggal = $data['tanggal'];
        $form->shift = $data['shift'];
        $form->created_at = time();
        $form->updated_at = time();

        if (!$form->save()) {
            return ['status' => 'error', 'message' => $form->errors];
        }

        // Simpan detail
        foreach ($data['hasil'] as $field => $value) {
            $detail = new FormResultDetail();
            $detail->form_result_id = $form->id;
            $detail->field_name = $field;
            $detail->field_value = $value;
            $detail->save();
        }

        return [
            'status' => 'ok',
            'message' => 'Form berhasil disimpan',
            'form_result_id' => $form->id
        ];
    }
}
