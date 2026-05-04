<?php

namespace app\controllers;

use yii\web\Controller;
use yii\filters\AccessControl;
use app\models\FormTemplate;
use app\models\ChecksheetResult;

class DashboardController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'], // HARUS LOGIN
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $stats = $this->buildTemplateStats();

        return $this->render('index', [
            'stats' => $stats,
        ]);
    }

    private function buildTemplateStats(): array
    {
        $templates = FormTemplate::find()
            ->orderBy(['id' => SORT_DESC])
            ->all();

        $valid = 0;
        $invalid = 0;
        $active = 0;

        foreach ($templates as $template) {
            $summary = $template->getSchemaValidationSummary();

            if ($summary['is_valid']) {
                $valid++;
            } else {
                $invalid++;
            }

            if ((string)$template->status === 'active') {
                $active++;
            }
        }

        $today = date('Y-m-d');

        return [
            /*
            -------------------------------
            TEMPLATE
            -------------------------------
            */
            'total' => count($templates),
            'valid' => $valid,
            'invalid' => $invalid,
            'active' => $active,

            /*
            -------------------------------
            SUBMISSION
            -------------------------------
            */
            'submit_today' => ChecksheetResult::find()
                ->where(['like', 'submitted_at', $today])
                ->count(),

            'approved' => ChecksheetResult::find()
                ->where(['approval_status' => 'approved'])
                ->count(),

            'pending' => ChecksheetResult::find()
                ->where([
                    'approval_status' => [
                        'submitted',
                        'leader_approved',
                        'chief_approved'
                    ]
                ])
                ->count(),

            'rejected' => ChecksheetResult::find()
                ->where(['approval_status' => 'rejected'])
                ->count(),

            /*
            -------------------------------
            APPROVAL PIPELINE
            -------------------------------
            */
            'submitted' => ChecksheetResult::find()
                ->where(['approval_status' => 'submitted'])
                ->count(),

            'leader_approved' => ChecksheetResult::find()
                ->where(['approval_status' => 'leader_approved'])
                ->count(),

            'chief_approved' => ChecksheetResult::find()
                ->where(['approval_status' => 'chief_approved'])
                ->count(),

            'final_approved' => ChecksheetResult::find()
                ->where(['approval_status' => 'approved'])
                ->count(),

            /*
            -------------------------------
            MACHINE MONITORING
            -------------------------------
            */
            'machine_normal' => ChecksheetResult::find()
            ->select('mesin')
            ->distinct()
            ->where(['like', 'submitted_at', $today])
            ->count(),

            // sementara dummy
            'machine_late' => 0,
            'machine_missing' => 0,
        ];
    }
}
