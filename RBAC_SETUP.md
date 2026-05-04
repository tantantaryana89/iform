# Setup RBAC Admin Panel

## Pendahuluan

Sistem RBAC (Role-Based Access Control) telah berhasil diinstall dan dikonfigurasi di aplikasi Yii2 Anda.

## Struktur RBAC yang Sudah Dibuat

### Roles

1. **operator** - User biasa, hanya bisa lihat checksheet dan dashboard
2. **subforeman** - Bisa manage checksheet
3. **foreman** - Naik dari subforeman
4. **chief** - Naik dari foreman
5. **manager** - Bisa manage user, naik dari chief
6. **admin** - Admin tertinggi, bisa akses semua

### Permissions

- `viewChecksheet` - Lihat checksheet
- `manageChecksheet` - Kelola checksheet
- `viewDashboard` - Lihat dashboard
- `viewUser` - Lihat user
- `createUser` - Buat user baru
- `updateUser` - Edit user
- `deleteUser` - Hapus user
- `accessAdmin` - Akses admin panel

## Cara Setup

### 1. Assign Role Admin ke User Tertentu

Gunakan command console:

```bash
php yii rbac/assign {username} admin
```

Contoh:

```bash
php yii rbac/assign adminuser admin
```

### 2. Akses Admin Panel

Setelah user memiliki role admin, bisa akses:

- **Manajemen Role**: `/role` (Kelola roles, permissions)
- **Assign Role ke User**: `/assignment` (Assign role ke user)

### 3. Menambah Role/Permission Baru

#### Lewat Console Command

Edit file `commands/RbacController.php` dan tambahkan ke method `actionInit`, lalu jalankan:

```bash
php yii rbac/init
```

**Catatan**: Ini akan reset semua roles dan permissions!

#### Lewat Admin Panel

1. Login dengan akun admin
2. Ke halaman "Manajemen Role"
3. Klik "Tambah Role"
4. Isi nama dan deskripsi
5. Klik "Buat Role"

### 4. Assign Permissions ke Role

1. Di "Manajemen Role"
2. Klik "Lihat" pada role yang ingin diubah
3. Klik "Tambah Permission"
4. Pilih permission dari dropdown
5. Klik "Tambah"

### 5. Assign Role ke User

1. Ke halaman "Assign Role ke User"
2. Cari user yang ingin diubah
3. Klik "Assign"
4. Pilih role yang ingin diberikan
5. Klik "Simpan"

## Validasi RBAC di Controller

Untuk melindungi action tertentu dengan RBAC, gunakan:

```php
// Di controller
public function behaviors()
{
    return [
        'access' => [
            'class' => \yii\filters\AccessControl::class,
            'rules' => [
                [
                    'allow' => true,
                    'roles' => ['admin'], // Hanya role admin
                    'actions' => ['index', 'create'],
                ],
                [
                    'allow' => true,
                    'roles' => ['@'], // Semua yang login
                    'actions' => ['view'],
                ],
            ],
        ],
    ];
}
```

Atau gunakan method di controller action:

```php
public function actionEdit($id)
{
    if (!Yii::$app->authManager->checkAccess(Yii::$app->user->id, 'updateUser')) {
        throw new \yii\web\ForbiddenHttpException('Anda tidak memiliki izin');
    }
    // code...
}
```

## Files yang Dibuat

- `components/AdminFilter.php` - Filter untuk check admin access
- `controllers/RoleController.php` - Controller untuk manage roles
- `controllers/AssignmentController.php` - Controller untuk assign roles
- `views/role/` - Views untuk role management
- `views/assignment/` - Views untuk role assignment
- `commands/RbacController.php` - Console command untuk setup RBAC
- `migrations/m250214_000000_create_rbac_tables.php` - Database migration

## Troubleshooting

### User tidak bisa akses admin panel

- Pastikan user sudah di-assign role 'admin'
- Gunakan command: `php yii rbac/assign {username} admin`

### Tabel auth\_\* error

- Pastikan migrasi sudah dijalankan: `php yii migrate`
- Check file `migrations/m250214_000000_create_rbac_tables.php`

### Permission/Role tidak muncul di dropdown

- Pastikan sudah dijalankan: `php yii rbac/init`
- Atau gunakan form "Tambah Role" di admin panel

## Referensi

- [Yii2 Authorization (RBAC)](https://www.yiiframework.com/doc/guide/2.0/en/security-authorization)
- [MDMsoft yii2-admin](https://github.com/mdmsoft/yii2-admin)
