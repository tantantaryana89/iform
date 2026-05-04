# RBAC Admin Panel - Quick Start Guide

## 🚀 Setup Awal (5 menit)

### Step 1: Buat Database Tables

Sudah dilakukan melalui migration:

```bash
php yii migrate
```

### Step 2: Inisialisasi Roles & Permissions

```bash
php yii rbac/init
```

### Step 3: Lihat Daftar User di Sistem

```bash
php yii rbac/list-users
```

Contoh output:

```
Daftar User dan Role:
----
Username        | Nama Lengkap                   | Roles
----
admin           | Administrator                 | admin
operator1       | Operator One                   | (tidak ada)
----
```

### Step 4: Assign Role Admin ke User

Ubah `{username}` dengan username user yang ingin dijadikan admin:

```bash
php yii rbac/assign {username} admin
```

Contoh:

```bash
php yii rbac/assign operator1 admin
```

### Step 5: Akses Admin Panel

1. Login dengan akun yang sudah di-assign role admin
2. Buka URL: `http://localhost/iform/index.php?r=site/admin-index`
3. Dari sana bisa kelola Role, Permission, dan User Assignments

---

## 📋 Halaman-Halaman Admin

### 1. Admin Dashboard

**URL:** `/site/admin-index`

- Overview RBAC system
- Quick links ke halaman-halaman admin

### 2. Manajemen Role

**URL:** `/role`

- 👁️ View semua role
- ➕ Tambah role baru
- ✏️ Edit deskripsi role
- ➕ Tambah permission ke role
- ❌ Hapus permission dari role
- 🗑️ Hapus role

### 3. Assign Role ke User

**URL:** `/assignment`

- 📌 Lihat semua user dan role mereka
- 🔗 Assign satu atau lebih role ke user
- 🔄 Update role user
- ❌ Revoke role dari user

---

## 💡 Contoh Use Case

### Skenario: Karyawan Baru Butuh Akses Operator

1. Asumsikan sudah buat user baru di User Management
2. Ke halaman `/assignment`
3. Cari user tersebut, klik "Assign"
4. Pilih role "operator", klik "Simpan"
5. User bisa login dan akses fitur operator

### Skenario: Buat Role Baru untuk Supervisor

1. Ke halaman `/role`
2. Klik "Tambah Role"
3. Isi nama: `supervisor`, deskripsi: `Supervisor Shift`
4. Klik "Buat Role"
5. Klik "Lihat" pada role supervisor
6. Klik "Tambah Permission"
7. Pilih permissions yang sesuai (contoh: `viewChecksheet`, `manageChecksheet`)
8. Klik "Tambah" untuk setiap permission
9. Ke `/assignment` dan assign role ke user

---

## 🔐 Protect Action/Controller dengan RBAC

### Method 1: Check Permission di Action

```php
// Di ControllerAnda
public function actionView($id)
{
    // Cek apakah user punya permission 'viewChecksheet'
    if (!Yii::$app->authManager->checkAccess(Yii::$app->user->id, 'viewChecksheet')) {
        throw new \yii\web\ForbiddenHttpException('Anda tidak memiliki izin');
    }
    // ... code selanjutnya
}
```

### Method 2: Gunakan Filter pada Controller

```php
use yii\filters\AccessControl;

public function behaviors()
{
    return [
        'access' => [
            'class' => AccessControl::class,
            'rules' => [
                [
                    'allow' => true,
                    'roles' => ['admin'], // Hanya role admin
                    'actions' => ['delete', 'edit'],
                ],
                [
                    'allow' => true,
                    'permissions' => ['viewChecksheet'], // Punya permission ini
                    'actions' => ['view', 'index'],
                ],
            ],
        ],
    ];
}
```

### Method 3: Check di View

```php
<?php if (Yii::$app->authManager->checkAccess(Yii::$app->user->id, 'createUser')): ?>
    <?= Html::a('Tambah User', ['/user/create'], ['class' => 'btn btn-primary']) ?>
<?php endif; ?>
```

---

## 🛠️ Command Line Tools

### List Semua Users

```bash
php yii rbac/list-users
```

### List Semua Roles & Permissions

```bash
php yii rbac/list-roles
```

### Assign Role ke User

```bash
php yii rbac/assign {username} {role}
```

Contoh:

```bash
php yii rbac/assign john manager
php yii rbac/assign admin123 admin
```

### Reset RBAC (Hati-hati!)

```bash
php yii rbac/init
```

Ini akan **menghapus semua role dan permission** lalu buat ulang dari scratch.

---

## 📊 Struktur Hierarchy Role

```
admin
  ├── createUser
  ├── updateUser
  ├── deleteUser
  ├── accessAdmin
  └── manager
      ├── viewUser
      └── chief
          └── foreman
              └── subforeman
                  ├── manageChecksheet
                  └── operator
                      ├── viewChecksheet
                      └── viewDashboard
```

**Penjelasan:**

- Role di bawah mewarisi semua permissions dari role di atas
- Contoh: `manager` punya semua permissions dari `chief`, `foreman`, `subforeman`, dan `operator`
- `admin` adalah role tertinggi dengan akses penuh

---

## ❓ FAQ

### Q: Bisakah satu user punya banyak role?

**A:** Ya! Di halaman `Assignment`, bisa pilih multiple roles untuk satu user.

### Q: Bagaimana cara update deskripsi permission?

**A:** Saat ini permissions hanya bisa dibuat via console. Edit `/commands/RbacController.php` dan jalankan `php yii rbac/init` lagi.

### Q: Tidak bisa login ke admin panel?

1. Pastikan user sudah di-assign role 'admin': `php yii rbac/list-users`
2. Jika belum, jalankan: `php yii rbac/assign {username} admin`
3. Session mungkin perlu refresh - logout lalu login ulang

### Q: Password admin lupa?

Gunakan halaman User Management atau akses database langsung untuk reset password.

---

**Dokumentasi lengkap:** Lihat `RBAC_SETUP.md`
