<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\components\AdminFilter;

class RoleController extends Controller
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
        $auth = Yii::$app->authManager;
        $roles = $auth->getRoles();

        return $this->render('index', [
            'roles' => $roles,
        ]);
    }

    public function actionView($name)
    {
        $auth = Yii::$app->authManager;
        $role = $auth->getRole($name);

        if (!$role) {
            throw new \yii\web\NotFoundHttpException('Role tidak ditemukan');
        }

        // Ambil semua parent roles
        $parents = $auth->getChildren($role->name);

        // Ambil semua user dengan role ini
        $userIds = Yii::$app->db->createCommand(
            'SELECT user_id FROM {{%auth_assignment}} WHERE item_name = :item_name',
            [':item_name' => $name]
        )->queryColumn();

        $users = \app\models\User::find()->where(['id' => $userIds])->all();

        return $this->render('view', [
            'role' => $role,
            'parents' => $parents,
            'users' => $users,
        ]);
    }

    public function actionCreate()
    {
        $post = Yii::$app->request->post();

        if (!empty($post)) {
            $name = trim($post['name']);
            $description = trim($post['description']);

            if (empty($name)) {
                Yii::$app->session->setFlash('error', 'Nama role tidak boleh kosong');
            } else {
                $auth = Yii::$app->authManager;
                $role = $auth->createRole($name);
                $role->description = $description;
                $auth->add($role);

                Yii::$app->session->setFlash('success', 'Role berhasil dibuat');
                return $this->redirect(['index']);
            }
        }

        return $this->render('create');
    }

    public function actionUpdate($name)
    {
        $auth = Yii::$app->authManager;
        $role = $auth->getRole($name);

        if (!$role) {
            throw new \yii\web\NotFoundHttpException('Role tidak ditemukan');
        }

        $post = Yii::$app->request->post();

        if (!empty($post)) {
            $description = trim($post['description']);
            $role->description = $description;
            $auth->update($name, $role);

            Yii::$app->session->setFlash('success', 'Role berhasil diupdate');
            return $this->redirect(['view', 'name' => $name]);
        }

        return $this->render('update', [
            'role' => $role,
        ]);
    }

    public function actionDelete($name)
    {
        $auth = Yii::$app->authManager;
        $role = $auth->getRole($name);

        if (!$role) {
            throw new \yii\web\NotFoundHttpException('Role tidak ditemukan');
        }

        $auth->remove($role);

        Yii::$app->session->setFlash('success', 'Role berhasil dihapus');
        return $this->redirect(['index']);
    }

    public function actionAddPermission($name)
    {
        $auth = Yii::$app->authManager;
        $role = $auth->getRole($name);

        if (!$role) {
            throw new \yii\web\NotFoundHttpException('Role tidak ditemukan');
        }

        $allPermissions = $auth->getPermissions();
        $currentPermissions = $auth->getChildren($name);

        $post = Yii::$app->request->post();

        if (!empty($post) && !empty($post['permission'])) {
            $permission = $auth->getPermission($post['permission']);
            if ($permission) {
                $auth->addChild($role, $permission);
                Yii::$app->session->setFlash('success', 'Permission berhasil ditambahkan');
            }
            return $this->redirect(['view', 'name' => $name]);
        }

        return $this->render('add-permission', [
            'role' => $role,
            'allPermissions' => $allPermissions,
            'currentPermissions' => $currentPermissions,
        ]);
    }

    public function actionRemovePermission($name, $permission)
    {
        $auth = Yii::$app->authManager;
        $role = $auth->getRole($name);
        $perm = $auth->getPermission($permission);

        if (!$role || !$perm) {
            throw new \yii\web\NotFoundHttpException('Role atau permission tidak ditemukan');
        }

        $auth->removeChild($role, $perm);

        Yii::$app->session->setFlash('success', 'Permission berhasil dihapus');
        return $this->redirect(['view', 'name' => $name]);
    }
}
