# Filament Phase 1: 資料庫規格

**專案**: YAMU Backend - Filament Admin Panel
**Phase**: 1 - Permission System 資料表
**預估時間**: 包含在 Installation (Migration 自動執行)

---

## 📊 Database Schema Overview

### Existing Tables (不變動)

Phase 1 **不需要修改**現有資料表，完全使用現有 Schema:

- ✅ `users` - 使用者表 (已存在)
- ✅ `salesperson_profiles` - 業務員檔案表 (已存在)
- ✅ `companies` - 公司表 (已存在)
- ✅ `experiences` - 工作經驗表 (已存在)
- ✅ `certifications` - 證照表 (已存在)

---

### New Tables (Spatie Permissions)

Phase 1 **新增** Permission 系統相關表 (由 Spatie Package 自動建立):

1. `roles` - 角色表
2. `permissions` - 權限表
3. `model_has_roles` - User-Role 關聯表
4. `model_has_permissions` - User-Permission 關聯表
5. `role_has_permissions` - Role-Permission 關聯表

---

## 🗂️ Existing Tables (Reference)

### users Table

**用途**: 儲存使用者資料 (包含業務員申請資訊)

| Column | Type | Nullable | Default | Index | Description |
|--------|------|----------|---------|-------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | PRIMARY | 主鍵 |
| username | VARCHAR(255) | YES | NULL | UNIQUE | 使用者名稱 |
| name | VARCHAR(255) | NO | - | - | 姓名 |
| email | VARCHAR(255) | NO | - | UNIQUE | Email |
| password_hash | VARCHAR(255) | NO | - | - | 密碼雜湊 |
| role | ENUM('user', 'salesperson', 'admin') | NO | 'user' | INDEX | 角色 |
| status | ENUM('active', 'inactive') | NO | 'active' | - | 帳號狀態 |
| salesperson_status | ENUM('pending', 'approved', 'rejected') | YES | NULL | INDEX | 業務員審核狀態 |
| salesperson_applied_at | TIMESTAMP | YES | NULL | INDEX | 業務員申請時間 |
| salesperson_approved_at | TIMESTAMP | YES | NULL | - | 業務員批准時間 |
| rejection_reason | TEXT | YES | NULL | - | 拒絕原因 |
| can_reapply_at | TIMESTAMP | YES | NULL | - | 可重新申請時間 |
| is_paid_member | BOOLEAN | NO | false | - | 是否付費會員 |
| email_verified_at | TIMESTAMP | YES | NULL | - | Email 驗證時間 |
| remember_token | VARCHAR(100) | YES | NULL | - | Remember Token |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | INDEX | 建立時間 |
| updated_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | - | 更新時間 |
| deleted_at | TIMESTAMP | YES | NULL | - | 軟刪除時間 |

**索引**:
```sql
PRIMARY KEY (id)
UNIQUE INDEX idx_users_email (email)
UNIQUE INDEX idx_users_username (username)
INDEX idx_users_role (role)
INDEX idx_users_salesperson_status (salesperson_status)
INDEX idx_users_salesperson_applied_at (salesperson_applied_at)
INDEX idx_users_created_at (created_at)
```

**重要查詢**:
```sql
-- Phase 1 主要查詢: 取得待審核業務員
SELECT * FROM users
WHERE role = 'salesperson'
AND salesperson_status = 'pending'
ORDER BY salesperson_applied_at ASC;
```

---

### salesperson_profiles Table

**用途**: 儲存業務員詳細資料

| Column | Type | Nullable | Default | Index | Description |
|--------|------|----------|---------|-------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | PRIMARY | 主鍵 |
| user_id | BIGINT UNSIGNED | NO | - | UNIQUE | User ID (FK) |
| company_id | BIGINT UNSIGNED | YES | NULL | INDEX | Company ID (FK) |
| full_name | VARCHAR(255) | YES | NULL | - | 完整姓名 |
| phone | VARCHAR(20) | YES | NULL | - | 電話 |
| email_public | VARCHAR(255) | YES | NULL | - | 公開 Email |
| line_id | VARCHAR(100) | YES | NULL | - | LINE ID |
| wechat_id | VARCHAR(100) | YES | NULL | - | WeChat ID |
| contact_preferences | JSON | YES | NULL | - | 聯絡偏好 |
| bio | TEXT | YES | NULL | - | 個人簡介 |
| specialties | TEXT | YES | NULL | - | 專長領域 |
| service_regions | JSON | YES | NULL | - | 服務地區 |
| avatar_data | LONGTEXT | YES | NULL | - | 頭像 Base64 |
| avatar_mime | VARCHAR(100) | YES | NULL | - | 頭像 MIME Type |
| avatar_size | INTEGER | YES | NULL | - | 頭像檔案大小 |
| approval_status | ENUM('pending', 'approved', 'rejected') | NO | 'pending' | INDEX | 檔案審核狀態 |
| rejected_reason | TEXT | YES | NULL | - | 檔案拒絕原因 |
| approved_by | BIGINT UNSIGNED | YES | NULL | - | 審核者 ID |
| approved_at | TIMESTAMP | YES | NULL | - | 審核時間 |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | - | 建立時間 |
| updated_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | - | 更新時間 |

**索引**:
```sql
PRIMARY KEY (id)
UNIQUE INDEX idx_salesperson_profiles_user_id (user_id)
INDEX idx_salesperson_profiles_company_id (company_id)
INDEX idx_salesperson_profiles_approval_status (approval_status)

FOREIGN KEY fk_salesperson_profiles_user_id (user_id)
    REFERENCES users(id) ON DELETE CASCADE

FOREIGN KEY fk_salesperson_profiles_company_id (company_id)
    REFERENCES companies(id) ON DELETE SET NULL
```

---

## 🆕 New Tables (Spatie Permissions)

### roles Table

**用途**: 儲存角色定義

**Migration**: `vendor/spatie/laravel-permission/database/migrations/create_permission_tables.php`

| Column | Type | Nullable | Default | Index | Description |
|--------|------|----------|---------|-------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | PRIMARY | 主鍵 |
| name | VARCHAR(255) | NO | - | UNIQUE (name+guard_name) | 角色名稱 |
| guard_name | VARCHAR(255) | NO | - | UNIQUE (name+guard_name) | Guard 名稱 |
| created_at | TIMESTAMP | YES | NULL | - | 建立時間 |
| updated_at | TIMESTAMP | YES | NULL | - | 更新時間 |

**索引**:
```sql
PRIMARY KEY (id)
UNIQUE INDEX roles_name_guard_name_unique (name, guard_name)
```

**Phase 1 資料**:
```sql
INSERT INTO roles (name, guard_name, created_at, updated_at)
VALUES
  ('super_admin', 'admin_session', NOW(), NOW()),
  ('admin', 'admin_session', NOW(), NOW()),
  ('reviewer', 'admin_session', NOW(), NOW());
```

---

### permissions Table

**用途**: 儲存權限定義

| Column | Type | Nullable | Default | Index | Description |
|--------|------|----------|---------|-------|-------------|
| id | BIGINT UNSIGNED | NO | AUTO_INCREMENT | PRIMARY | 主鍵 |
| name | VARCHAR(255) | NO | - | UNIQUE (name+guard_name) | 權限名稱 |
| guard_name | VARCHAR(255) | NO | - | UNIQUE (name+guard_name) | Guard 名稱 |
| created_at | TIMESTAMP | YES | NULL | - | 建立時間 |
| updated_at | TIMESTAMP | YES | NULL | - | 更新時間 |

**索引**:
```sql
PRIMARY KEY (id)
UNIQUE INDEX permissions_name_guard_name_unique (name, guard_name)
```

**Phase 1 資料**:
```sql
INSERT INTO permissions (name, guard_name, created_at, updated_at)
VALUES
  -- Salesperson Application Permissions
  ('view_salesperson::application', 'admin_session', NOW(), NOW()),
  ('view_any_salesperson::application', 'admin_session', NOW(), NOW()),
  ('create_salesperson::application', 'admin_session', NOW(), NOW()),
  ('update_salesperson::application', 'admin_session', NOW(), NOW()),
  ('delete_salesperson::application', 'admin_session', NOW(), NOW()),
  ('delete_any_salesperson::application', 'admin_session', NOW(), NOW()),
  ('approve_salesperson::application', 'admin_session', NOW(), NOW()),
  ('reject_salesperson::application', 'admin_session', NOW(), NOW()),
  ('bulk_approve_salesperson::application', 'admin_session', NOW(), NOW());
```

---

### model_has_roles Table

**用途**: User-Role 多對多關聯

| Column | Type | Nullable | Default | Index | Description |
|--------|------|----------|---------|-------|-------------|
| role_id | BIGINT UNSIGNED | NO | - | FK + INDEX | Role ID |
| model_type | VARCHAR(255) | NO | - | INDEX | Model 類型 |
| model_id | BIGINT UNSIGNED | NO | - | INDEX | Model ID (User ID) |

**索引**:
```sql
INDEX model_has_roles_model_id_model_type_index (model_id, model_type)
PRIMARY KEY (role_id, model_id, model_type)

FOREIGN KEY model_has_roles_role_id_foreign (role_id)
    REFERENCES roles(id) ON DELETE CASCADE
```

**Phase 1 資料** (Admin 使用者指派 super_admin 角色):
```sql
-- 假設 Admin User ID = 1
INSERT INTO model_has_roles (role_id, model_type, model_id)
VALUES
  (1, 'App\\Models\\User', 1);  -- super_admin role
```

---

### model_has_permissions Table

**用途**: User-Permission 直接授權 (較少使用，多透過 Role)

| Column | Type | Nullable | Default | Index | Description |
|--------|------|----------|---------|-------|-------------|
| permission_id | BIGINT UNSIGNED | NO | - | FK + INDEX | Permission ID |
| model_type | VARCHAR(255) | NO | - | INDEX | Model 類型 |
| model_id | BIGINT UNSIGNED | NO | - | INDEX | Model ID (User ID) |

**索引**:
```sql
INDEX model_has_permissions_model_id_model_type_index (model_id, model_type)
PRIMARY KEY (permission_id, model_id, model_type)

FOREIGN KEY model_has_permissions_permission_id_foreign (permission_id)
    REFERENCES permissions(id) ON DELETE CASCADE
```

**Phase 1**: 通常不直接指派權限給 User，而是透過 Role。

---

### role_has_permissions Table

**用途**: Role-Permission 多對多關聯

| Column | Type | Nullable | Default | Index | Description |
|--------|------|----------|---------|-------|-------------|
| permission_id | BIGINT UNSIGNED | NO | - | FK | Permission ID |
| role_id | BIGINT UNSIGNED | NO | - | FK | Role ID |

**索引**:
```sql
PRIMARY KEY (permission_id, role_id)

FOREIGN KEY role_has_permissions_permission_id_foreign (permission_id)
    REFERENCES permissions(id) ON DELETE CASCADE

FOREIGN KEY role_has_permissions_role_id_foreign (role_id)
    REFERENCES roles(id) ON DELETE CASCADE
```

**Phase 1 資料**:
```sql
-- super_admin (role_id=1): 所有權限
INSERT INTO role_has_permissions (role_id, permission_id)
SELECT 1, id FROM permissions WHERE guard_name = 'admin_session';

-- admin (role_id=2): 除了刪除以外的權限
INSERT INTO role_has_permissions (role_id, permission_id)
SELECT 2, id FROM permissions
WHERE guard_name = 'admin_session'
AND name NOT LIKE '%delete%';

-- reviewer (role_id=3): 僅查看和審核
INSERT INTO role_has_permissions (role_id, permission_id)
SELECT 3, id FROM permissions
WHERE guard_name = 'admin_session'
AND name IN (
  'view_salesperson::application',
  'view_any_salesperson::application',
  'approve_salesperson::application',
  'reject_salesperson::application'
);
```

---

## 📈 Database Queries (Phase 1)

### Critical Queries

**1. 取得待審核業務員列表**:
```sql
SELECT
  u.id,
  u.email,
  u.salesperson_status,
  u.salesperson_applied_at,
  sp.full_name,
  sp.phone,
  c.name AS company_name
FROM users u
LEFT JOIN salesperson_profiles sp ON sp.user_id = u.id
LEFT JOIN companies c ON c.id = sp.company_id
WHERE u.role = 'salesperson'
AND u.salesperson_status = 'pending'
AND u.deleted_at IS NULL
ORDER BY u.salesperson_applied_at ASC
LIMIT 20;
```

**Eloquent Query**:
```php
User::where('role', User::ROLE_SALESPERSON)
    ->where('salesperson_status', User::STATUS_PENDING)
    ->with(['salespersonProfile', 'salespersonProfile.company'])
    ->orderBy('salesperson_applied_at', 'asc')
    ->paginate(20);
```

---

**2. 檢查 User 權限**:
```sql
-- 檢查 User 是否有特定權限
SELECT COUNT(*) FROM permissions p
INNER JOIN role_has_permissions rhp ON rhp.permission_id = p.id
INNER JOIN model_has_roles mhr ON mhr.role_id = rhp.role_id
WHERE p.name = 'approve_salesperson::application'
AND p.guard_name = 'admin_session'
AND mhr.model_type = 'App\\Models\\User'
AND mhr.model_id = 1;  -- User ID
```

**Eloquent Query** (Spatie 自動處理):
```php
auth()->user()->can('approve_salesperson::application');
```

---

**3. 取得待審核數量**:
```sql
SELECT COUNT(*) FROM users
WHERE role = 'salesperson'
AND salesperson_status = 'pending'
AND deleted_at IS NULL;
```

**Eloquent Query**:
```php
User::where('role', User::ROLE_SALESPERSON)
    ->where('salesperson_status', User::STATUS_PENDING)
    ->count();
```

---

## 🔍 Indexes & Performance

### Required Indexes (Already Exist)

Phase 1 **不需要新增索引**，現有索引已足夠:

```sql
-- users 表
INDEX idx_users_role (role)
INDEX idx_users_salesperson_status (salesperson_status)
INDEX idx_users_salesperson_applied_at (salesperson_applied_at)

-- salesperson_profiles 表
UNIQUE INDEX idx_salesperson_profiles_user_id (user_id)
INDEX idx_salesperson_profiles_company_id (company_id)
```

### Performance Expectations

**目標** (參考 metrics-standards.md):

| 查詢 | 目標時間 | 測量方式 |
|------|---------|----------|
| 業務員列表查詢 (20 筆) | < 50ms | Laravel Debugbar |
| 權限檢查查詢 | < 10ms | Spatie Permission Cache |
| 統計數量查詢 | < 20ms | Simple COUNT query |

**優化策略**:
- ✅ 使用 Eager Loading (避免 N+1 Query)
- ✅ Permission Cache (Spatie 自動快取)
- ✅ 適當的索引 (已存在)

---

## 🗄️ Seeder

### FilamentAdminSeeder

**檔案**: `database/seeders/FilamentAdminSeeder.php`

```php
<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class FilamentAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 清除快取
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 建立 Roles
        $superAdmin = Role::firstOrCreate([
            'name' => 'super_admin',
            'guard_name' => 'admin_session',
        ]);

        $admin = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'admin_session',
        ]);

        $reviewer = Role::firstOrCreate([
            'name' => 'reviewer',
            'guard_name' => 'admin_session',
        ]);

        // 建立 Permissions
        $permissions = [
            'view_salesperson::application',
            'view_any_salesperson::application',
            'create_salesperson::application',
            'update_salesperson::application',
            'delete_salesperson::application',
            'delete_any_salesperson::application',
            'approve_salesperson::application',
            'reject_salesperson::application',
            'bulk_approve_salesperson::application',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'admin_session',
            ]);
        }

        // 指派權限給 Roles
        $superAdmin->syncPermissions(Permission::all());

        $admin->syncPermissions([
            'view_salesperson::application',
            'view_any_salesperson::application',
            'approve_salesperson::application',
            'reject_salesperson::application',
            'bulk_approve_salesperson::application',
        ]);

        $reviewer->syncPermissions([
            'view_salesperson::application',
            'view_any_salesperson::application',
            'approve_salesperson::application',
            'reject_salesperson::application',
        ]);

        // 指派 Role 給 Admin User (如果存在)
        $adminUser = User::where('email', 'admin@yamu.com')->first();
        if ($adminUser) {
            $adminUser->assignRole($superAdmin);
        }

        $this->command->info('Filament Admin seeder completed!');
    }
}
```

**執行 Seeder**:
```bash
php artisan db:seed --class=FilamentAdminSeeder
```

---

## 🧪 Database Testing

### Test Queries

```bash
# 進入 Tinker
php artisan tinker

# 測試 1: 查詢待審核業務員
>>> \App\Models\User::where('role', 'salesperson')
    ->where('salesperson_status', 'pending')
    ->with('salespersonProfile')
    ->get();

# 測試 2: 檢查 Admin 角色
>>> \Spatie\Permission\Models\Role::where('name', 'super_admin')
    ->where('guard_name', 'admin_session')
    ->first();

# 測試 3: 檢查權限
>>> $user = \App\Models\User::find(1);
>>> $user->hasRole('super_admin');  // true
>>> $user->can('approve_salesperson::application');  // true

# 測試 4: 查詢效能
>>> DB::enableQueryLog();
>>> \App\Models\User::where('role', 'salesperson')
    ->where('salesperson_status', 'pending')
    ->with('salespersonProfile')
    ->get();
>>> count(DB::getQueryLog());  // 應該 <= 3 個查詢
```

---

## ✅ Verification Checklist

### Migrations
- [ ] Spatie Permission migrations 已執行
- [ ] `roles` 表已建立
- [ ] `permissions` 表已建立
- [ ] `model_has_roles` 表已建立
- [ ] `model_has_permissions` 表已建立
- [ ] `role_has_permissions` 表已建立

### Data Seeding
- [ ] `super_admin` role 已建立
- [ ] `admin` role 已建立
- [ ] `reviewer` role 已建立
- [ ] 9 個 salesperson application permissions 已建立
- [ ] Roles 已正確指派 permissions
- [ ] Admin user 已指派 super_admin role

### Indexes
- [ ] `users` 表索引正常運作
- [ ] `salesperson_profiles` 表索引正常運作
- [ ] Spatie tables 索引正常運作

### Performance
- [ ] 業務員列表查詢 < 50ms
- [ ] Permission 檢查 < 10ms (有快取)
- [ ] 無 N+1 Query 問題

---

**文檔版本**: 1.0
**最後更新**: 2026-01-24
**負責人**: Backend Team
