<?php

namespace app\controllers;

use Yii;
use yii\rest\Controller;
use yii\web\Response;
use app\models\ChecksheetTemplate;
use app\models\ChecksheetItem;

class ApiChecksheetController extends Controller
{
    public $enableCsrfValidation = false;

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        unset($behaviors['authenticator']);
        unset($behaviors['csrf']);

        $behaviors['contentNegotiator'] = [
            'class' => \yii\filters\ContentNegotiator::class,
            'formats' => [
                'application/json' => Response::FORMAT_JSON,
            ],
        ];

        return $behaviors;
    }

    /**
     * ===============================
     * SUBMIT CHECKSHEET
     * ===============================
     * POST api-checksheet/submit
     */
    public function actionSubmit()
    {
        $data = Yii::$app->request->getBodyParams();

        Yii::error('STEP 1: submit masuk', 'debug.checksheet');
        Yii::error($data, 'debug.checksheet');
        

        // ===============================
        // 1️⃣ VALIDASI JSON
        // ===============================
        if (!is_array($data)) {
            return $this->asJson([
                'status' => 'error',
                'message' => 'Invalid JSON',
            ]);
        }

        // ===============================
        // 2️⃣ FIELD WAJIB
        // ===============================
        foreach (['mesin', 'shift', 'items'] as $key) {
            if (!isset($data[$key]) || $data[$key] === '') {
                return $this->asJson([
                    'status' => 'error',
                    'message' => "Field {$key} wajib diisi",
                ]);
            }
        }

        if (!is_array($data['items']) || count($data['items']) === 0) {
            return $this->asJson([
                'status' => 'error',
                'message' => 'Items harus berupa array dan tidak boleh kosong',
            ]);
        }

        // ===============================
        // 3️⃣ CARI TEMPLATE AKTIF
        // ===============================
        $template = ChecksheetTemplate::find()
            ->joinWith('mesin')
            ->where([
                'daftar_mesin.nama_mesin' => $data['mesin'],
                'checksheet_template.status' => 'active',
            ])
            ->one();

        if (!$template) {
            return $this->asJson([
                'status' => 'error',
                'message' => 'Template aktif untuk mesin ini tidak ditemukan',
            ]);
        }

        // ===============================
        // 4️⃣ SIMPAN KE DATABASE
        // ===============================
        $db = Yii::$app->db;
        $tx = $db->beginTransaction();

        try {

            // ===============================
            // HEADER RESULT
            // ===============================
            $db->createCommand()->insert('checksheet_result', [
                'template_id'  => $template->id,
                'mesin'        => $data['mesin'],
                'shift'        => $data['shift'],
                'submitted_at' => date('Y-m-d H:i:s'),
                'created_at'   => time(),
                'created_by'   => null,
                'approval_status' => 'submitted',
            ])->execute();

            $resultId = $db->getLastInsertID();

            // ===============================
            // DETAIL ITEMS (BACKWARD COMPATIBLE)
            // ===============================
            foreach ($data['items'] as $row) {

                $itemId = is_array($row) ? ($row['id'] ?? null) : $row;
                $value  = is_array($row) ? (string)($row['value'] ?? '1') : '1';

                if (!$itemId) {
                    throw new \Exception('Item ID tidak valid');
                }

                $item = ChecksheetItem::findOne($itemId);
                if (!$item) {
                    throw new \Exception("Item ID {$itemId} tidak ditemukan");
                }

                $db->createCommand()->insert('checksheet_result_item', [
                    'result_id'  => $resultId,
                    'item_id'    => $itemId,
                    'item_code'  => $item->item_code,
                    'raw_value'  => $value,
                    'created_at' => time(),
                ])->execute();
            }

            $tx->commit();

            return $this->asJson([
                'status'    => 'ok',
                'result_id' => $resultId,
            ]);

        } catch (\Throwable $e) {

            $tx->rollBack();

            Yii::error($e->getMessage(), 'debug.checksheet');
            Yii::error($e->getTraceAsString(), 'debug.checksheet');

            return [
                'status'  => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }
}
