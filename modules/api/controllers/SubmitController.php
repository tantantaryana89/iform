<?php
namespace app\modules\api\controllers;

use Yii;
use app\models\FormTemplate;
use app\models\FormResult;
use app\models\FormResultDetail;
use app\models\MachineTemplate;

class SubmitController extends BaseApiController
{
    /**
     * Submit form dari Android
     * POST /api/submit
     * 
     * Request body:
     * {
     *   "template_id": 1,
     *   "operator": "john",
     *   "mesin_id": "M001",
     *   "tanggal": "2026-02-14",
     *   "shift": "shift1",
     *   "answers": {
     *     "field1": "value1",
     *     "field2": "value2"
     *   }
     * }
     */
    public function actionIndex()
    {
        $request = Yii::$app->request;
        $data = $request->getBodyParams();
        if (!is_array($data) || empty($data)) {
            $data = $request->post();
        }
        if (!is_array($data) || empty($data)) {
            $raw = (string)$request->getRawBody();
            if ($raw !== '') {
                $decoded = json_decode($raw, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $data = $decoded;
                }
            }
        }
        if (!is_array($data)) {
            $data = [];
        }

        // Validasi data wajib
        $required = ['template_id', 'operator', 'mesin_id', 'tanggal', 'shift', 'answers'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                Yii::warning("[submit] missing required field: {$field}", 'api.submit');
                return $this->error("Field '$field' harus diisi", 422);
            }
        }

        $template = FormTemplate::findOne((int)$data['template_id']);
        if (!$template) {
            Yii::warning('[submit] template tidak ditemukan: ' . (string)$data['template_id'], 'api.submit');
            return $this->error('Template tidak ditemukan', 404);
        }

        if ((string)$template->status !== 'active') {
            Yii::warning('[submit] template belum active: ' . (int)$template->id, 'api.submit');
            return $this->error('Template belum aktif untuk produksi', 409);
        }

        $mappingValidation = $template->getSchemaValidationSummary();
        if (!$mappingValidation['is_valid']) {
            Yii::warning('[submit] template mapping invalid: ' . (int)$template->id . ' errors=' . implode(' | ', array_slice($mappingValidation['errors'] ?? [], 0, 5)), 'api.submit');
            return $this->error('Template aktif tetapi mapping belum valid untuk produksi', 409);
        }

        $machineTemplate = MachineTemplate::findOne(['no_mesin' => (string)$data['mesin_id']]);
        if (!$machineTemplate) {
            Yii::warning('[submit] mesin tanpa template: ' . (string)$data['mesin_id'], 'api.submit');
            return $this->error('Mesin belum memiliki template yang terdaftar', 422);
        }

        if ((int)$machineTemplate->template_id !== (int)$template->id) {
            Yii::warning('[submit] template mismatch mesin=' . (string)$data['mesin_id'] . ' expected=' . (int)$machineTemplate->template_id . ' got=' . (int)$template->id, 'api.submit');
            return $this->error('Template tidak sesuai dengan mesin yang dikirim', 422);
        }

        $answers = $data['answers'];
        if (is_string($answers)) {
            $answers = json_decode($answers, true);
        }

        if (!is_array($answers)) {
            Yii::warning('[submit] answers bukan object JSON', 'api.submit');
            return $this->error('Field answers harus berupa object JSON', 422);
        }

        $validation = $this->validateAndNormalizeAnswers($template, $answers);
        if (!empty($validation['unknown'])) {
            Yii::warning('[submit] unknown fields: ' . implode(', ', $validation['unknown']), 'api.submit');
            return $this->error(
                'Jawaban mengandung field yang tidak terdaftar pada template: ' . implode(', ', $validation['unknown']),
                422
            );
        }

        if (!empty($validation['missing_required'])) {
            Yii::warning('[submit] missing required fields: ' . implode(', ', $validation['missing_required']), 'api.submit');
            return $this->error(
                'Field wajib belum diisi: ' . implode(', ', $validation['missing_required']),
                422
            );
        }

        $validatedAnswers = $validation['normalized'];

        // Mulai transaction
        $transaction = Yii::$app->db->beginTransaction();

        try {
            // 1. Simpan ke form_result
            $formResult = new FormResult();
            $formResult->template_id = $data['template_id'];
            $formResult->operator = $data['operator'];
            $formResult->no_mesin = $data['mesin_id'];
            $formResult->tanggal = $data['tanggal'];
            $formResult->shift = $data['shift'];
            $formResult->created_at = time();
            $formResult->updated_at = time();

            if (!$formResult->save()) {
                throw new \Exception('Gagal menyimpan form result: ' . json_encode($formResult->errors));
            }

            // 2. Simpan setiap field ke form_result_detail
            foreach ($validatedAnswers as $fieldName => $fieldValue) {
                $detail = new FormResultDetail();
                $detail->form_result_id = $formResult->id;
                $detail->field_name = $fieldName;
                $detail->field_value = is_array($fieldValue) ? json_encode($fieldValue) : $fieldValue;

                if (!$detail->save()) {
                    throw new \Exception('Gagal menyimpan detail: ' . json_encode($detail->errors));
                }
            }

            $transaction->commit();

            Yii::info('Form submitted: ID=' . $formResult->id, 'api.submit');

            return $this->ok(
                ['form_result_id' => $formResult->id],
                'Form berhasil disimpan'
            );

        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error('Submit form error: ' . $e->getMessage(), 'api.submit');
            return $this->error($e->getMessage(), 500);
        }
    }

    private function validateAndNormalizeAnswers(FormTemplate $template, array $answers): array
    {
        $items = $template->getItems();
        if (empty($items)) {
            return [
                'normalized' => [],
                'unknown' => array_keys($answers),
                'missing_required' => [],
            ];
        }

        $aliasMap = [];
        $requiredItemIds = [];
        foreach ($items as $item) {
            $canonicalKey = (string)($item['item_id'] ?? '');
            if ($canonicalKey === '') {
                continue;
            }

            if (!empty($item['required'])) {
                $requiredItemIds[] = $canonicalKey;
            }

            $aliases = [
                $canonicalKey,
                (string)($item['label'] ?? ''),
                (string)($item['no'] ?? ''),
                $this->normalizeKey((string)($item['label'] ?? '')),
            ];

            foreach ($aliases as $alias) {
                $alias = trim((string)$alias);
                if ($alias === '') {
                    continue;
                }
                $aliasMap[$alias] = $canonicalKey;
            }
        }

        $normalized = [];
        $unknown = [];
        foreach ($answers as $incomingKey => $incomingValue) {
            $lookupKey = trim((string)$incomingKey);
            $canonicalKey = $aliasMap[$lookupKey] ?? $aliasMap[$this->normalizeKey($lookupKey)] ?? null;
            if ($canonicalKey === null) {
                $unknown[] = $lookupKey;
                continue;
            }

            $normalized[$canonicalKey] = $incomingValue;
        }

        $missingRequired = [];
        foreach (array_unique($requiredItemIds) as $requiredItemId) {
            if (!array_key_exists($requiredItemId, $normalized)) {
                $missingRequired[] = $requiredItemId;
                continue;
            }

            $value = $normalized[$requiredItemId];
            if ($value === null || $value === '' || (is_array($value) && empty($value))) {
                $missingRequired[] = $requiredItemId;
            }
        }

        return [
            'normalized' => $normalized,
            'unknown' => array_values(array_unique(array_filter($unknown))),
            'missing_required' => $missingRequired,
        ];
    }

    private function normalizeKey(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9]+/', '_', $value);

        return trim((string)$value, '_');
    }

    /**
     * Export form result ke Excel
     * GET /api/submit/export?id=1
     */
    public function actionExport($id)
    {
        $formResult = FormResult::findOne($id);
        if (!$formResult) {
            return $this->error('Form tidak ditemukan', 404);
        }

        try {
            $exporter = new \app\components\ExcelExporter();
            $filePath = $exporter->exportFormResult($formResult);

            Yii::info('Form exported: ID=' . $id, 'api.submit');

            return $this->ok(
                ['file_path' => basename($filePath)],
                'Export berhasil'
            );

        } catch (\Exception $e) {
            Yii::error('Export error: ' . $e->getMessage(), 'api.submit');
            return $this->error($e->getMessage(), 500);
        }
    }
}
