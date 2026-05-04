<?php

namespace app\controllers;

use Yii;
use app\models\User;
use app\models\UserSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

class UserController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete'    => ['POST','GET'],
                    'reset-pin' => ['POST'],
                    'manage-pin' => ['GET','POST'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $searchModel  = new UserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', compact('searchModel', 'dataProvider'));
    }

    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    public function actionCreate()
    {
        $model = new User();
        $model->scenario = 'create';

        if ($model->load(Yii::$app->request->post())) {

            // ===== PASSWORD LOGIN =====
            if (!empty($model->password)) {
                $model->password_hash = Yii::$app->security->generatePasswordHash($model->password);
            }

            $model->auth_key = Yii::$app->security->generateRandomString();

            // ===== PIN APPROVAL (SAMA DENGAN MANAGE-PIN) =====
            $pinType = Yii::$app->request->post('pin_type');

            // DEFAULT = generate
            if ($pinType === null || $pinType === 'generate') {
                $model->pin = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                $model->require_pin = 1;
            }

            // Manual hanya kalau dipilih eksplisit
            if ($pinType === 'manual') {
                if (empty($model->pin)) {
                    $model->addError('pin', 'PIN manual wajib diisi.');
                } else {
                    $model->require_pin = 1;
                }
            }

            if (!$model->hasErrors() && $model->save()) {
                Yii::$app->session->setFlash('success', 'User berhasil dibuat!');
                return $this->redirect(['view', 'id' => $model->id]);
            }

            Yii::$app->session->setFlash('error', 'Gagal menyimpan User. Periksa input kembali.');
            Yii::error(['user-create-errors' => $model->getErrors()], 'user.create');
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->scenario = 'update';

        // kosongkan virtual field (supaya tidak overwrite)
        $model->password = '';
        $model->pin = '';

        if ($model->load(Yii::$app->request->post())) {

            // ===== PASSWORD LOGIN =====
            if (!empty($model->password)) {
                $model->password_hash = Yii::$app->security->generatePasswordHash($model->password);
            }

            // ===== PIN APPROVAL =====
            $pinType = Yii::$app->request->post('pin_type');

            // DEFAULT: tidak ubah PIN
            if ($pinType === 'generate') {
                // regenerate PIN
                $model->pin = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                $model->require_pin = 1;
            }

            if ($pinType === 'manual') {
                if (empty($model->pin)) {
                    $model->addError('pin', 'PIN manual wajib diisi.');
                } else {
                    $model->require_pin = 1;
                }
            }

            // ===== SIMPAN =====
            if (!$model->hasErrors() && $model->save()) {
                Yii::$app->session->setFlash('success', 'Data user berhasil diperbarui.');
                return $this->redirect(['view', 'id' => $model->id]);
            }

            Yii::$app->session->setFlash('error', 'Gagal memperbarui user. Periksa input.');
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        if (!Yii::$app->user->isGuest && $model->id == Yii::$app->user->id) {
            Yii::$app->session->setFlash('warning', 'Tidak bisa menghapus akun Anda sendiri.');
            return $this->redirect(['index']);
        }

        $model->delete();
        Yii::$app->session->setFlash('success', 'User berhasil dihapus.');
        return $this->redirect(['index']);
    }

    public function actionResetPin($id)
    {
        if (!Yii::$app->request->isPost) {
            throw new \yii\web\MethodNotAllowedHttpException('POST only.');
        }

        $model = $this->findModel($id);
        $model->pin_hash = null;
        $model->require_pin = 1;
        $model->save(false);

        Yii::$app->session->setFlash('success', 'PIN berhasil direset!');
        return $this->redirect(['index']);
    }

    /**
     * Manage PIN page: choose generate random PIN or set manually
     */
    public function actionManagePin($id)
    {
        $model = $this->findModel($id);

        if (Yii::$app->request->isPost) {
            $type = Yii::$app->request->post('type');

            if ($type === 'generate') {
                // 6-digit numeric PIN
                $pin = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                $model->pin = $pin;
                $model->require_pin = 1;
                $model->save(false);

                Yii::$app->session->setFlash('success', "PIN baru untuk {$model->username}: {$pin}");
                return $this->redirect(['view', 'id' => $model->id]);
            }

            if ($type === 'manual') {
                $manualPin = Yii::$app->request->post('manual_pin');
                if (empty($manualPin)) {
                    Yii::$app->session->setFlash('error', 'PIN manual tidak boleh kosong.');
                } else {
                    $model->pin = $manualPin;
                    $model->require_pin = 1;
                    $model->save(false);
                    Yii::$app->session->setFlash('success', 'PIN berhasil diperbarui.');
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            }
        }

        return $this->render('manage-pin', ['model' => $model]);
    }

    protected function findModel($id)
    {
        if (($model = User::findOne(['id' => $id])) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('User tidak ditemukan.');
    }
}
