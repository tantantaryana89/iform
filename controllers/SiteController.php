<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use app\models\LoginForm;

class SiteController extends Controller
{
    public function behaviors()
    {
        return [
            // proteksi index + logout
            'access' => [
                'class' => AccessControl::class,
                'only'  => ['index', 'logout'],
                'rules' => [
                    [
                        'actions' => ['index', 'logout'],
                        'allow'   => true,
                        'roles'   => ['@'], // hanya user login
                    ],
                ],
            ],
            // logout harus POST
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['POST'],
                ],
            ],
        ];
    }

    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    public function actionIndex()
    {
        return $this->redirect(['dashboard/index']);
    }

    public function actionLogin()
    {
        $this->layout = 'login';

        if (!Yii::$app->user->isGuest) {
            return $this->redirect(['dashboard/index']);
        }

        $model = new LoginForm();

        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->redirect(['dashboard/index']);
        }

        return $this->render('login', [
            'model' => $model,
        ]);
    }

    public function actionLogout()
    {
        Yii::$app->user->logout(false);
        Yii::$app->session->destroy();

        return $this->redirect(['site/login']);
    }

    public function actionAdmin()
    {
        $this->layout = 'admin';
        return $this->render('admin');
    }

    public function actionAdminIndex()
    {
        // Check if user is admin
        if (!Yii::$app->authManager->getAssignment('admin', Yii::$app->user->id)) {
            throw new \yii\web\ForbiddenHttpException('Anda tidak memiliki izin mengakses halaman ini');
        }

        return $this->render('admin-index');
    }
}
