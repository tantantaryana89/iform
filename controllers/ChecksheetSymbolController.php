<?php

namespace app\controllers;

use Yii;
use app\models\ChecksheetSymbol;
use app\models\ChecksheetItem;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

class ChecksheetSymbolController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST', 'GET'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => ChecksheetSymbol::find(),
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort' => [
                'defaultOrder' => ['id' => SORT_ASC],
            ],
        ]);

        return $this->render('index', compact('dataProvider'));
    }

    public function actionCreate()
    {
        $model = new ChecksheetSymbol();
        $model->scenario = 'create'; // ⬅️ WAJIB

        if ($model->load(Yii::$app->request->post())) {

            $model->imageFile = UploadedFile::getInstance($model, 'imageFile');

            if ($model->validate() && $model->upload() && $model->save(false)) {

                Yii::$app->session->setFlash(
                    'success',
                    '<i class="fas fa-check-circle me-1"></i> 
                    <strong>Sukses!</strong> Simbol berhasil ditambahkan.'
                );

                return $this->redirect(['index']);
            }

            Yii::$app->session->setFlash(
                'error',
                '<i class="fas fa-times-circle me-1"></i> 
                <strong>Gagal!</strong> Periksa kembali input simbol.'
            );
        }

        return $this->render('create', compact('model'));
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->scenario = 'update'; // ⬅️ WAJIB

        if ($model->load(Yii::$app->request->post())) {

            $model->imageFile = UploadedFile::getInstance($model, 'imageFile');

            if ($model->validate() && $model->upload() && $model->save(false)) {

                Yii::$app->session->setFlash(
                    'success',
                    '<i class="fas fa-check-circle me-1"></i> 
                    <strong>Sukses!</strong> Simbol berhasil diperbarui.'
                );

                return $this->redirect(['index']);
            }

            Yii::$app->session->setFlash(
                'error',
                '<i class="fas fa-times-circle me-1"></i> 
                <strong>Gagal!</strong> Perubahan tidak dapat disimpan.'
            );
        }

        return $this->render('update', compact('model'));
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        // Cek apakah simbol masih dipakai oleh checksheet_item
        $usedCount = ChecksheetItem::find()
            ->where(['symbol_id' => $model->id])
            ->count();

        if ($usedCount > 0) {
            Yii::$app->session->setFlash(
                'warning',
                '<i class="fas fa-exclamation-triangle me-1"></i> 
                 <strong>Tidak bisa dihapus!</strong> 
                 Simbol ini masih digunakan oleh checksheet item.'
            );
            return $this->redirect(['index']);
        }

        $model->delete();

        Yii::$app->session->setFlash(
            'success',
            '<i class="fas fa-check-circle me-1"></i> 
             <strong>Sukses!</strong> Simbol berhasil dihapus.'
        );

        return $this->redirect(['index']);
    }

    protected function findModel($id)
    {
        if (($model = ChecksheetSymbol::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Simbol tidak ditemukan.');
    }
}
