<?php

namespace app\controllers;

use Yii;
use app\models\DaftarMesin;
use app\models\DaftarMesinSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

class DaftarMesinController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $searchModel = new DaftarMesinSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    public function actionCreate()
    {
        $model = new DaftarMesin();

        if (Yii::$app->request->isPost &&
            $model->load(Yii::$app->request->post()) &&
            $model->save()
        ) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if (Yii::$app->request->isPost &&
            $model->load(Yii::$app->request->post()) &&
            $model->save()
        ) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    protected function findModel($id)
    {
        if (($model = DaftarMesin::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Data mesin tidak ditemukan.');
    }


    // ============================================
    // ===============  EXPORT PDF  ===============
    // ============================================

    public function actionExportPdf()
    {
        $models = DaftarMesin::find()->all();

        // generate QR jika belum ada
        foreach ($models as $m) {
            if (!$m->qr_code_path) {
                Yii::$app->runAction('qrcode/default/create', ['id' => $m->id]);
                $m->refresh();
            }
        }

        // mPDF FIX CONFIG
        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'simpleTables' => false,
            'useSubstitutions' => false,
            'keep_table_proportions' => true,
            'margin_left' => 5,
            'margin_right' => 5,
            'margin_top' => 5,
            'margin_bottom' => 5,
        ]);

        // Load view (NO CSS outside)
        $html = $this->renderPartial('pdf-all', [
            'models' => $models
        ]);

        $mpdf->WriteHTML($html);
        return $mpdf->Output("qrcode_mesin.pdf", "I");
    }
}
