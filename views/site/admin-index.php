<?php
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Admin Panel - RBAC Management';
$this->params['breadcrumbs'][] = 'Admin';
?>

<div class="admin-dashboard">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1><?= $this->title ?></h1>
            <p class="text-muted">Kelola Roles, Permissions, dan User Assignments</p>
        </div>
    </div>

    <div class="row">
        <!-- Card: Manajemen Role -->
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100 border-left-primary">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h5 class="card-title mb-2">📋 Manajemen Role</h5>
                            <p class="card-text text-muted small">
                                Buat, edit, dan hapus roles. Kelola permissions yang dimiliki setiap role.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white">
                    <?= Html::a('Kelola Role →', Url::to(['/role']), ['class' => 'btn btn-primary btn-sm']) ?>
                </div>
            </div>
        </div>

        <!-- Card: Assign Role ke User -->
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100 border-left-success">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h5 class="card-title mb-2">👥 Assign Role</h5>
                            <p class="card-text text-muted small">
                                Berikan role ke user. Satu user bisa memiliki banyak role.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white">
                    <?= Html::a('Assign Role →', Url::to(['/assignment']), ['class' => 'btn btn-success btn-sm']) ?>
                </div>
            </div>
        </div>

        <!-- Card: User Management -->
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100 border-left-info">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h5 class="card-title mb-2">🔧 Manajemen User</h5>
                            <p class="card-text text-muted small">
                                Kelola user profile, ubah password, dan non-aktifkan user.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white">
                    <?= Html::a('Kelola User →', Url::to(['/user']), ['class' => 'btn btn-info btn-sm']) ?>
                </div>
            </div>
        </div>
    </div>

    <hr class="my-5">

    <!-- Info Box -->
    <div class="row mt-5">
        <div class="col-md-12">
            <div class="alert alert-info" role="alert">
                <h5 class="alert-heading">ℹ️ Informasi RBAC</h5>
                <p>
                    Sistem RBAC (Role-Based Access Control) telah dikonfigurasi dengan berikut struktur:
                </p>
                <ul class="mb-0">
                    <li><strong>Roles:</strong> operator, subforeman, foreman, chief, manager, admin</li>
                    <li><strong>Inheritance:</strong> Admin > Manager > Chief > Foreman > Subforeman > Operator</li>
                    <li><strong>Permissions:</strong> viewChecksheet, manageChecksheet, viewDashboard, viewUser, createUser, updateUser, deleteUser, accessAdmin</li>
                </ul>
            </div>
        </div>
    </div>

    <style>
    .border-left-primary {
        border-left: 4px solid #007bff !important;
    }
    .border-left-success {
        border-left: 4px solid #28a745 !important;
    }
    .border-left-info {
        border-left: 4px solid #17a2b8 !important;
    }
    .card {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    </style>
</div>
