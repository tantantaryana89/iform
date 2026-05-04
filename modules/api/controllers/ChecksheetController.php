<?php

namespace app\modules\api\controllers;

use Yii;
use yii\rest\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\ChecksheetTemplate;
use app\models\ChecksheetItem;


class ChecksheetController extends Controller
{
    private const APP_TIME_ZONE = 'Asia/Jakarta';

    // 🔥 API = NO CSRF
    public $enableCsrfValidation = false;

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // ❌ Tidak pakai session/login
        unset($behaviors['authenticator']);

        // ✅ JSON only
        $behaviors['contentNegotiator'] = [
            'class' => \yii\filters\ContentNegotiator::class,
            'formats' => [
                'application/json' => Response::FORMAT_JSON,
            ],
        ];

        // ✅ HTTP method
        $behaviors['verbs'] = [
            'class' => VerbFilter::class,
            'actions' => [
                'submit'   => ['POST'],
                'by-mesin' => ['GET'],
            ],
        ];

        return $behaviors;
    }

    /**
     * ===============================
     * GET CHECKSHEET BY MESIN
     * GET:
     * index.php?r=api/checksheet/by-mesin&no_mesin=XXX
     * ===============================
     */
    public function actionByMesin($no_mesin)
    {
        $template = ChecksheetTemplate::find()
            ->joinWith('mesin')
            ->with([
                'mesin',
                'sections.items.symbol',
                'sections.items.symbol2',
            ])
            ->where([
                'daftar_mesin.no_mesin' => $no_mesin,
                'checksheet_template.status' => 'active',
            ])
            ->one();

        if (!$template) {
            return [
                'status' => 'error',
                'message' => 'Template untuk mesin ini tidak ditemukan',
            ];
        }

        return $this->buildResponse($template);
    }

    /**
     * ===============================
     * SUBMIT CHECKSHEET
     * POST:
     * index.php?r=api/checksheet/submit
     * ===============================
     */
    public function actionSubmit()
    {
        $data = Yii::$app->request->getBodyParams();
        Yii::error(json_encode($data), 'api.checksheet');
        Yii::error('VERSI BARU CONTROLLER JALAN', 'api.checksheet');
        Yii::error('ACTION SUBMIT KEPANGGIL', 'api.checksheet');

        if (empty($data['template_id']) && empty($data['template_name'])) {
            return [
                'status' => 'error',
                'message' => 'template_id atau template_name wajib diisi',
            ];
        }

        if (empty($data['template_id'])) {
            $templateId = ChecksheetTemplate::find()
                ->select('id')
                ->where(['name' => $data['template_name']])
                ->scalar();

            if (!$templateId) {
                return [
                    'status' => 'error',
                    'message' => 'Template tidak ditemukan',
                ];
            }

            $data['template_id'] = (int) $templateId;
        }

        if (empty($data['mesin']) || empty($data['shift']) || empty($data['items'])) {
            return [
                'status' => 'error',
                'message' => 'mesin, shift, dan items wajib diisi',
            ];
        }

        if (!is_array($data['items'])) {
            return [
                'status' => 'error',
                'message' => 'Items harus berupa array',
            ];
        }

        $now = $this->getCurrentDateTime();
        $submitDate = $this->resolveSubmitDate($data['tanggal'] ?? null, $now);

        $forceReplace = !empty($data['force']) && !in_array((string)$data['force'], ['0', 'false', 'False', 'FALSE'], true);

        $existingResult = (new \yii\db\Query())
            ->from('checksheet_result')
            ->select(['id', 'submitted_at'])
            ->where([
                'template_id' => (int)$data['template_id'],
                'mesin' => (string)$data['mesin'],
                'shift' => (string)$data['shift'],
            ])
            ->andWhere('DATE(submitted_at) = :submitDate', [':submitDate' => $submitDate])
            ->orderBy(['id' => SORT_DESC])
            ->one();

        if ($existingResult && !$forceReplace) {
            Yii::$app->response->statusCode = 409;
            return [
                'status' => 'conflict',
                'message' => 'Data shift ' . (string)$data['shift'] . ' sudah ada',
                'shift' => (string)$data['shift'],
                'mesin' => (string)$data['mesin'],
                'existing_result_id' => (int)$existingResult['id'],
                'submit_date' => $submitDate,
                'requires_confirmation' => true,
            ];
        }

        $submittedAt = $submitDate . ' ' . $now->format('H:i:s');
        $createdAt = $now->format('Y-m-d H:i:s');

        $db = Yii::$app->db;
        $tx = $db->beginTransaction();

        try {
            if ($existingResult && $forceReplace) {
                $resultId = (int)$existingResult['id'];

                $db->createCommand()->update('checksheet_result', [
                    'submitted_at' => $submittedAt,
                    'created_by'   => $data['operator_name'] ?? null,
                ], ['id' => $resultId])->execute();

                $db->createCommand()->delete('checksheet_result_item', [
                    'result_id' => $resultId,
                ])->execute();
            } else {
                // Insert header
                $db->createCommand()->insert('checksheet_result', [
                    'template_id' => $data['template_id'],
                    'mesin'       => $data['mesin'],
                    'shift'       => $data['shift'],
                    'submitted_at'=> $submittedAt,
                    'created_at'  => $createdAt,
                    'created_by'  => $data['operator_name'] ?? null,
                ])->execute();

                $resultId = (int)$db->getLastInsertID();
            }

            // Insert detail
            foreach ($data['items'] as $row) {

                if (is_numeric($row)) {
                    $itemId = (int)$row;
                    $rawValue = 'OK';
                } 
                elseif (is_array($row) && isset($row['id'], $row['value'])) {
                    $itemId = (int)$row['id'];
                    $rawValue = (string)$row['value'];
                } 
                else {
                    throw new \Exception('Format item tidak valid');
                }

                $item = ChecksheetItem::findOne($itemId);
                if (!$item) {
                    throw new \Exception("Item ID {$itemId} tidak ditemukan");
                }

                $db->createCommand()->insert('checksheet_result_item', [
                    'result_id'  => $resultId,
                    'item_id'    => $item->id,
                    'item_code'  => $item->item_code,
                    'raw_value'  => $rawValue,
                    'created_at' => $createdAt,
                ])->execute();
            }

            $tx->commit();

            return [
                'status' => 'ok',
                'result_id' => (int) $resultId,
                'replaced' => (bool)($existingResult && $forceReplace),
            ];

        } catch (\Throwable $e) {
            $tx->rollBack();
            Yii::error($e->getMessage(), 'api.checksheet');

            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    private function getCurrentDateTime(): \DateTimeImmutable
    {
        return new \DateTimeImmutable('now', new \DateTimeZone(Yii::$app->timeZone ?: self::APP_TIME_ZONE));
    }

    private function resolveSubmitDate($rawDate, \DateTimeImmutable $fallback): string
    {
        if ($rawDate === null || trim((string)$rawDate) === '') {
            return $fallback->format('Y-m-d');
        }

        try {
            $date = new \DateTimeImmutable((string)$rawDate, new \DateTimeZone(Yii::$app->timeZone ?: self::APP_TIME_ZONE));
            return $date->setTimezone(new \DateTimeZone(Yii::$app->timeZone ?: self::APP_TIME_ZONE))->format('Y-m-d');
        } catch (\Throwable $exception) {
            Yii::warning('Tanggal submit tidak valid: ' . (string)$rawDate, 'api.checksheet');
            return $fallback->format('Y-m-d');
        }
    }

    /**
     * ===============================
     * RESPONSE BUILDER
     * ===============================
     */
    protected function buildResponse($template)
    {
        $baseUrl = Yii::$app->params['publicBaseUrl'];
        $sections = [];

        foreach ($template->sections as $section) {
            $items = [];

            foreach ($section->items as $item) {
                $instruction = $item->getInstruction();

                $items[] = [
                    'id' => $item->id,
                    'label' => $item->label,
                    'type'  => $item->type,
                    'conditions' => $item->getConditionRows(),
                    'standard'  => $instruction['standard'] ?? [],
                    'cara'      => $instruction['cara'] ?? [],
                    'frekuensi' => $instruction['frekuensi'] ?? [],
                    'note'      => $instruction['note'] ?? [],
                    'symbol' => $item->symbol ? [
                        'image' => $baseUrl . $item->symbol->image_path,
                    ] : null,
                    'symbol2' => $item->symbol2 ? [
                        'image' => $baseUrl . $item->symbol2->image_path,
                    ] : null,
                ];
            }

            $sections[] = [
                'title' => $section->title,
                'items' => $items,
            ];
        }

        return [
            'status' => 'ok',
            'template' => [
                'id'    => $template->id,                 // ⭐ penting ke depan
                'name'  => $template->name,
                'mesin' => $template->mesin->nama_mesin,
            ],
            'sections' => $sections,
        ];
    }
}
