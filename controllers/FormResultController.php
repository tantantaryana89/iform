<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use app\models\ApprovalForm;
use app\models\FormResult;
use app\models\MassApprovalForm;
use app\components\ExcelExporter;

class FormResultController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['index', 'view', 'download', 'download-all', 'approve', 'mass-approve', 'approval-history'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'], // hanya user login
                    ],
                ],
            ],
        ];
    }

    /**
     * Daftar semua form results
     */
    public function actionIndex()
    {
        $formResults = FormResult::find()
            ->orderBy(['id' => SORT_DESC])
            ->all();

        return $this->render('index', [
            'formResults' => $formResults,
        ]);
    }

    /**
     * Lihat detail form result
     */
    public function actionView($id)
    {
        $formResult = FormResult::findOne($id);
        if (!$formResult) {
            throw new \yii\web\NotFoundHttpException('Form tidak ditemukan');
        }

        $details = $formResult->getDetails()->all();
        $orderedDetails = $this->buildOrderedDetails($formResult, $details);

        return $this->render('view', [
            'formResult' => $formResult,
            'details' => $details,
            'orderedDetails' => $orderedDetails,
            'approvalForm' => new ApprovalForm(),
        ]);
    }

    public function actionApprove($id)
    {
        $formResult = FormResult::findOne($id);
        if (!$formResult) {
            throw new \yii\web\NotFoundHttpException('Form tidak ditemukan');
        }

        $user = Yii::$app->user->identity;
        if (!$formResult->canBeApprovedBy($user)) {
            throw new \yii\web\ForbiddenHttpException('Anda tidak memiliki izin untuk approve form ini');
        }

        $approvalForm = new ApprovalForm();
        if ($approvalForm->load(Yii::$app->request->post()) && $approvalForm->validate()) {
            if (!$user->validatePin($approvalForm->pin)) {
                $approvalForm->addError('pin', 'PIN tidak valid');
            } elseif (!$formResult->approveBy($user)) {
                throw new \yii\web\ServerErrorHttpException('Gagal menyimpan approval');
            } else {
                Yii::$app->session->setFlash('success', 'Form berhasil diapprove oleh ' . $user->fullname);
                return $this->redirect(['view', 'id' => $formResult->id]);
            }
        }

        $details = $formResult->getDetails()->all();
        $orderedDetails = $this->buildOrderedDetails($formResult, $details);

        return $this->render('view', [
            'formResult' => $formResult,
            'details' => $details,
            'orderedDetails' => $orderedDetails,
            'approvalForm' => $approvalForm,
        ]);
    }

    public function actionMassApprove()
    {
        $user = Yii::$app->user->identity;
        $model = new MassApprovalForm();
        $selectedIds = Yii::$app->request->post('selectedIds', Yii::$app->request->get('selectedIds', []));
        if (!is_array($selectedIds)) {
            $selectedIds = is_string($selectedIds) ? array_filter(array_map('trim', explode(',', $selectedIds))) : [];
        }

        $selectedIds = array_map('intval', array_filter($selectedIds));
        $model->ids = $selectedIds;

        $selectedForms = $this->getPendingFormsForUser($selectedIds, $user);

        if (Yii::$app->request->isPost) {
            $model->scenario = 'approve';
            if ($model->load(Yii::$app->request->post()) && $model->validate()) {
                if (!$user->validatePin($model->pin)) {
                    $model->addError('pin', 'PIN tidak valid');
                } elseif (count($selectedForms) !== count($selectedIds)) {
                    $model->addError('ids', 'Beberapa notifikasi tidak valid atau tidak dapat diapprove.');
                } else {
                    $transaction = Yii::$app->db->beginTransaction();
                    try {
                        $approved = 0;
                        foreach ($selectedForms as $form) {
                            if (!$form->approveBy($user)) {
                                throw new \Exception('Gagal approve form #' . $form->id);
                            }
                            $approved++;
                        }
                        $transaction->commit();
                        Yii::$app->session->setFlash('success', "Berhasil menyetujui $approved notifikasi.");
                        return $this->redirect(['index']);
                    } catch (\Throwable $e) {
                        $transaction->rollBack();
                        Yii::error('Mass approve error: ' . $e->getMessage(), 'form.mass-approve');
                        $model->addError('ids', 'Gagal menyetujui notifikasi. Coba lagi.');
                    }
                }
            }
        }

        return $this->render('mass-approve', [
            'model' => $model,
            'selectedForms' => $selectedForms,
        ]);
    }

    private function getPendingFormsForUser(array $ids, $user)
    {
        if (empty($ids)) {
            return [];
        }

        $forms = FormResult::find()
            ->where(['id' => $ids])
            ->all();

        $valid = [];
        foreach ($forms as $form) {
            if ($form->canBeApprovedBy($user)) {
                $valid[] = $form;
            }
        }

        return $valid;
    }

    public function actionApprovalHistory()
    {
        // Admin only
        $user = Yii::$app->user->identity;
        if (!Yii::$app->authManager->getAssignment('admin', $user->id)) {
            throw new \yii\web\ForbiddenHttpException('Hanya admin yang dapat mengakses halaman ini.');
        }

        $statusFilter = Yii::$app->request->get('status', '');
        $roleFilter = Yii::$app->request->get('role', '');
        $dateFromFilter = Yii::$app->request->get('date_from', '');
        $dateToFilter = Yii::$app->request->get('date_to', '');

        $query = FormResult::find()
            ->where(['<>', 'approval_status', FormResult::STATUS_SUBMITTED]);

        if ($statusFilter) {
            $query->andWhere(['approval_status' => $statusFilter]);
        }

        if ($dateFromFilter) {
            $query->andWhere(['>=', 'created_at', strtotime($dateFromFilter)]);
        }

        if ($dateToFilter) {
            $query->andWhere(['<=', 'created_at', strtotime($dateToFilter . ' 23:59:59')]);
        }

        $formResults = $query->orderBy(['created_at' => SORT_DESC])->all();

        return $this->render('approval-history', [
            'formResults' => $formResults,
            'statusFilter' => $statusFilter,
            'roleFilter' => $roleFilter,
            'dateFromFilter' => $dateFromFilter,
            'dateToFilter' => $dateToFilter,
        ]);
    }

    /**
     * Download form result sebagai Excel
     * GET /form-result/download?id=1
     */
    public function actionDownload($id)
    {
        $formResult = FormResult::findOne($id);
        if (!$formResult) {
            throw new \yii\web\NotFoundHttpException('Form tidak ditemukan');
        }

        try {
            $exporter = new ExcelExporter();
            $filePath = $exporter->exportFormResult($formResult);

            return Yii::$app->response->sendFile($filePath);

        } catch (\Exception $e) {
            Yii::error('Download form error: ' . $e->getMessage(), 'form.download');
            throw new \yii\web\ServerErrorHttpException('Gagal membuat file Excel');
        }
    }

    /**
     * Download semua form results sebagai Excel (multiple sheets)
     * GET /form-result/download-all
     */
    public function actionDownloadAll()
    {
        try {
            $formResults = FormResult::find()->all();

            if (empty($formResults)) {
                throw new \yii\web\BadRequestHttpException('Tidak ada data form');
            }

            $exporter = new ExcelExporter();
            $filePath = $exporter->exportMultipleResults($formResults, 'all_form_results');

            return Yii::$app->response->sendFile($filePath);

        } catch (\Exception $e) {
            Yii::error('Download all forms error: ' . $e->getMessage(), 'form.download');
            throw new \yii\web\ServerErrorHttpException($e->getMessage());
        }
    }

    private function buildOrderedDetails(FormResult $formResult, array $details): array
    {
        $detailMap = [];
        foreach ($details as $detail) {
            $detailMap[$detail->field_name] = $detail;
        }

        $ordered = [];
        $template = $formResult->template;
        $items = $template ? $template->getItems() : [];

        foreach ($items as $item) {
            $itemId = (string)($item['item_id'] ?? '');
            $detail = $itemId !== '' ? ($detailMap[$itemId] ?? null) : null;
            $ordered[] = [
                'item' => $item,
                'detail' => $detail,
            ];

            if ($detail) {
                unset($detailMap[$itemId]);
            }
        }

        foreach ($detailMap as $fieldName => $detail) {
            $ordered[] = [
                'item' => [
                    'item_id' => $fieldName,
                    'label' => $fieldName,
                    'section' => 'Field tambahan',
                ],
                'detail' => $detail,
            ];
        }

        return $ordered;
    }
}
