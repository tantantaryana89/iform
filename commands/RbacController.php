<?php
namespace app\commands;

use Yii;
use yii\console\Controller;
use app\models\User;

class RbacController extends Controller
{
    public $defaultAction = 'init';

    /**
     * Inisialisasi RBAC dengan roles dan permissions
     */
    public function actionInit()
    {
        $auth = Yii::$app->authManager;

        // Hapus semua roles, permissions, dan rules yang sudah ada
        $auth->removeAll();

        // ============ PERMISSIONS ============

        // Permissions untuk User Management
        $createUser = $auth->createPermission('createUser');
        $createUser->description = 'Create user';
        $auth->add($createUser);

        $updateUser = $auth->createPermission('updateUser');
        $updateUser->description = 'Update user';
        $auth->add($updateUser);

        $deleteUser = $auth->createPermission('deleteUser');
        $deleteUser->description = 'Delete user';
        $auth->add($deleteUser);

        $viewUser = $auth->createPermission('viewUser');
        $viewUser->description = 'View user';
        $auth->add($viewUser);

        // Permissions untuk Checksheet
        $manageChecksheet = $auth->createPermission('manageChecksheet');
        $manageChecksheet->description = 'Manage checksheet';
        $auth->add($manageChecksheet);

        $viewChecksheet = $auth->createPermission('viewChecksheet');
        $viewChecksheet->description = 'View checksheet';
        $auth->add($viewChecksheet);

        // Permissions untuk Dashboard
        $viewDashboard = $auth->createPermission('viewDashboard');
        $viewDashboard->description = 'View dashboard';
        $auth->add($viewDashboard);

        // Permissions untuk Admin Panel
        $accessAdmin = $auth->createPermission('accessAdmin');
        $accessAdmin->description = 'Access admin panel';
        $auth->add($accessAdmin);

        // ============ ROLES ============

        // Role: Operator
        $operator = $auth->createRole('operator');
        $operator->description = 'Operator';
        $auth->add($operator);
        $auth->addChild($operator, $viewChecksheet);
        $auth->addChild($operator, $viewDashboard);

        // Role: Subforeman
        $subforeman = $auth->createRole('subforeman');
        $subforeman->description = 'Subforeman';
        $auth->add($subforeman);
        $auth->addChild($subforeman, $operator);
        $auth->addChild($subforeman, $manageChecksheet);

        // Role: Foreman
        $foreman = $auth->createRole('foreman');
        $foreman->description = 'Foreman';
        $auth->add($foreman);
        $auth->addChild($foreman, $subforeman);

        // Role: Chief
        $chief = $auth->createRole('chief');
        $chief->description = 'Chief';
        $auth->add($chief);
        $auth->addChild($chief, $foreman);

        // Role: Manager
        $manager = $auth->createRole('manager');
        $manager->description = 'Manager';
        $auth->add($manager);
        $auth->addChild($manager, $chief);
        $auth->addChild($manager, $viewUser);

        // Role: Admin
        $admin = $auth->createRole('admin');
        $admin->description = 'Admin';
        $auth->add($admin);
        $auth->addChild($admin, $manager);
        $auth->addChild($admin, $createUser);
        $auth->addChild($admin, $updateUser);
        $auth->addChild($admin, $deleteUser);
        $auth->addChild($admin, $accessAdmin);

        echo "✅ RBAC Initialized successfully!\n";
        echo "Roles dan permissions telah dibuat.\n";
    }

    /**
     * Assign role ke user
     */
    public function actionAssign($username, $role)
    {
        $user = User::findOne(['username' => $username]);
        if (!$user) {
            echo "❌ User '$username' tidak ditemukan.\n";
            return 1;
        }

        $auth = Yii::$app->authManager;
        $roleObj = $auth->getRole($role);
        if (!$roleObj) {
            echo "❌ Role '$role' tidak ditemukan.\n";
            return 1;
        }

        $auth->assign($roleObj, $user->id);
        echo "✅ Role '$role' berhasil diassign ke user '$username'.\n";
        return 0;
    }

    /**
     * List semua user dan role mereka
     */
    public function actionListUsers()
    {
        $users = User::find()->all();
        if (empty($users)) {
            echo "Tidak ada user di sistem.\n";
            return 0;
        }

        $auth = Yii::$app->authManager;
        echo "\nDaftar User dan Role:\n";
        echo str_repeat("-", 80) . "\n";
        printf("%-15s | %-30s | %s\n", "Username", "Nama Lengkap", "Roles");
        echo str_repeat("-", 80) . "\n";

        foreach ($users as $user) {
            $roles = [];
            $allRoles = $auth->getRoles();
            foreach ($allRoles as $role) {
                if ($auth->getAssignment($role->name, $user->id)) {
                    $roles[] = $role->name;
                }
            }
            $roleStr = empty($roles) ? '(tidak ada)' : implode(', ', $roles);
            printf("%-15s | %-30s | %s\n", $user->username, substr($user->fullname, 0, 30), $roleStr);
        }

        echo str_repeat("-", 80) . "\n";
    }

    /**
     * List semua roles dan permissions
     */
    public function actionListRoles()
    {
        $auth = Yii::$app->authManager;
        $roles = $auth->getRoles();

        if (empty($roles)) {
            echo "Tidak ada role.\n";
            return 0;
        }

        echo "\nDaftar Roles:\n";
        echo str_repeat("-", 80) . "\n";

        foreach ($roles as $role) {
            echo "📌 Role: " . $role->name . "\n";
            echo "   Deskripsi: " . $role->description . "\n";

            $permissions = $auth->getChildren($role->name);
            if (!empty($permissions)) {
                echo "   Permissions:\n";
                foreach ($permissions as $perm) {
                    echo "     - " . $perm->name . " (" . $perm->description . ")\n";
                }
            }
            echo "\n";
        }

        echo str_repeat("-", 80) . "\n";
    }
}
