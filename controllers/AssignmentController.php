<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\components\AdminFilter;
use app\models\User;

class AssignmentController extends Controller
{
    public function behaviors()
    {
        return [
            'admin' => [
                'class' => AdminFilter::class,
            ],
        ];
    }

    public function actionIndex()
    {
        $users = User::find()->all();
        $auth = Yii::$app->authManager;
        $roles = $auth->getRoles();

        $userRoles = [];
        foreach ($users as $user) {
            $userRoles[$user->id] = [];
            foreach ($roles as $role) {
                if ($auth->getAssignment($role->name, $user->id)) {
                    $userRoles[$user->id][] = $role->name;
                }
            }
        }

        return $this->render('index', [
            'users' => $users,
            'roles' => $roles,
            'userRoles' => $userRoles,
        ]);
    }

    public function actionAssign($userId)
    {
        $user = User::findOne($userId);
        if (!$user) {
            throw new \yii\web\NotFoundHttpException('User tidak ditemukan');
        }

        $auth = Yii::$app->authManager;
        $roles = $auth->getRoles();

        $post = Yii::$app->request->post();

        if (!empty($post)) {
            // Hapus semua role lama
            $oldRoles = $auth->getRoles();
            foreach ($oldRoles as $role) {
                $auth->revoke($role, $userId);
            }

            // Assign role baru
            if (!empty($post['roles'])) {
                foreach ($post['roles'] as $roleName) {
                    $role = $auth->getRole($roleName);
                    if ($role) {
                        $auth->assign($role, $userId);
                    }
                }
            }

            Yii::$app->session->setFlash('success', 'Role user berhasil diupdate');
            return $this->redirect(['index']);
        }

        // Ambil role user saat ini
        $userRoles = [];
        foreach ($roles as $role) {
            if ($auth->getAssignment($role->name, $userId)) {
                $userRoles[] = $role->name;
            }
        }

        return $this->render('assign', [
            'user' => $user,
            'roles' => $roles,
            'userRoles' => $userRoles,
        ]);
    }

    public function actionRevoke($userId, $role)
    {
        $user = User::findOne($userId);
        if (!$user) {
            throw new \yii\web\NotFoundHttpException('User tidak ditemukan');
        }

        $auth = Yii::$app->authManager;
        $roleObj = $auth->getRole($role);
        if (!$roleObj) {
            throw new \yii\web\NotFoundHttpException('Role tidak ditemukan');
        }

        $auth->revoke($roleObj, $userId);

        Yii::$app->session->setFlash('success', 'Role berhasil dihapus dari user');
        return $this->redirect(['index']);
    }
}
