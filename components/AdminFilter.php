<?php
namespace app\components;

use Yii;
use yii\base\ActionFilter;
use yii\web\ForbiddenHttpException;

/**
 * Behavior untuk mengecek akses admin
 */
class AdminFilter extends ActionFilter
{
    public function beforeAction($action)
    {
        if (Yii::$app->user->isGuest) {
            throw new ForbiddenHttpException('Harus login terlebih dahulu');
        }

        // Cek apakah user memiliki role admin
        if (!Yii::$app->authManager->getAssignment('admin', Yii::$app->user->id)) {
            throw new ForbiddenHttpException('Anda tidak memiliki izin mengakses halaman ini');
        }

        return true;
    }
}
