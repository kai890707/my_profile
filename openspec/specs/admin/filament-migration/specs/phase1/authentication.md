# Filament Phase 1: 認證架構規格

**專案**: YAMU Backend - Filament Admin Panel
**Phase**: 1 - 雙認證整合策略
**預估時間**: 包含在 Phase 1 總時間 (30 分鐘)

---

## 🎯 Architecture Overview

### Dual Authentication Strategy

```
┌─────────────────────────────────────────────────────────────┐
│                      YAMU Application                         │
├─────────────────────────────────────────────────────────────┤
│                                                               │
│  ┌──────────────────────┐      ┌──────────────────────────┐ │
│  │   Next.js Frontend   │      │   Filament Admin Panel   │ │
│  │   (Port 3001)        │      │   (/filament/admin)      │ │
│  └──────────┬───────────┘      └───────────┬──────────────┘ │
│             │                               │                 │
│             │ API Calls                     │ Web Requests    │
│             │ (Bearer Token)                │ (Session)       │
│             │                               │                 │
│             ▼                               ▼                 │
│  ┌──────────────────────┐      ┌──────────────────────────┐ │
│  │   JWT Auth Guard     │      │  Session Auth Guard      │ │
│  │   (api)              │      │  (admin_session)         │ │
│  │   Driver: jwt        │      │  Driver: session         │ │
│  └──────────┬───────────┘      └───────────┬──────────────┘ │
│             │                               │                 │
│             └───────────────┬───────────────┘                 │
│                             │                                 │
│                             ▼                                 │
│                   ┌──────────────────┐                        │
│                   │  User Provider   │                        │
│                   │  (users table)   │                        │
│                   └──────────────────┘                        │
│                                                               │
└─────────────────────────────────────────────────────────────┘
```

---

## 🔐 Guards Configuration

### config/auth.php

```php
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    */

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    */

    'guards' => [
        // Web Guard (原有，保留)
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        // API Guard (JWT 認證，原有，不變動)
        'api' => [
            'driver' => 'jwt',
            'provider' => 'users',
        ],

        // Admin Session Guard (新增，給 Filament 使用)
        'admin_session' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    */

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => env('AUTH_MODEL', App\Models\User::class),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Reset
    |--------------------------------------------------------------------------
    */

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),
];
```

---

## 🔑 Authentication Flow

### API Authentication (JWT) - 維持不變

**Flow**:
```
1. Frontend (Next.js) → POST /api/auth/login
   Body: { email, password }

2. Laravel → Validate Credentials
   Guard: api (JWT driver)

3. Laravel → Generate JWT Token
   {
     access_token: "eyJ0eXAiOiJKV1QiLCJhbGc...",
     refresh_token: "dGhpc2lzYXJlZnJlc2h0b2tlbg...",
     expires_in: 3600
   }

4. Frontend → Store Token in Memory/Cookie

5. Frontend → API Calls with Token
   Header: Authorization: Bearer {access_token}

6. Laravel → Validate JWT Token
   Middleware: auth:api
   Guard: api

7. Laravel → Return API Response
```

**重要**: JWT 認證流程完全不受 Filament 影響。

---

### Filament Authentication (Session) - 新增

**Flow**:
```
1. Admin → 訪問 /filament/admin

2. Filament → Redirect to /filament/admin/login
   (未登入)

3. Admin → 輸入帳號密碼
   Form Submit: POST /filament/admin/login

4. Laravel → Validate Credentials
   Guard: admin_session (Session driver)

5. Laravel → Create Session
   Session Data: {
     user_id: 1,
     role: admin,
     ...
   }

6. Laravel → Set Session Cookie
   Cookie: laravel_session={encrypted_session_id}

7. Filament → Redirect to /filament/admin (Dashboard)

8. Admin → 瀏覽 Filament Pages
   Cookie: laravel_session (自動帶入)

9. Laravel → Validate Session
   Middleware: auth:admin_session

10. Filament → Render Page
```

---

## 🛡️ Middleware Configuration

### API Routes (routes/api.php)

**維持不變**:

```php
<?php

use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);

// Protected routes (JWT)
Route::middleware(['auth:api'])->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);

    // ... 其他 API routes
});

// Admin routes (JWT)
Route::middleware(['auth:api', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/statistics', [AdminController::class, 'statistics']);
    Route::get('/salesperson-applications', [AdminController::class, 'salespersonApplications']);
    // ... 其他 Admin API routes
});
```

**重要**:
- API routes 繼續使用 `auth:api` middleware
- 完全不受 Filament 影響

---

### Filament Routes (自動生成)

**由 Filament 自動處理**:

```php
// 這些 routes 由 Filament 自動註冊，無需手動設定

Route::middleware([
    'web',  // Laravel web middleware group
    \Filament\Http\Middleware\Authenticate::class,  // Filament auth
])->group(function () {
    // Filament admin panel routes
    Route::get('/filament/admin', ...)->middleware('auth:admin_session');
    Route::get('/filament/admin/login', ...);
    Route::post('/filament/admin/login', ...);
    Route::post('/filament/admin/logout', ...);
    // ...
});
```

**重要**:
- Filament routes 使用 `admin_session` guard
- 自動處理 CSRF protection
- 自動處理 Session management

---

## 👤 User Model Configuration

### app/Models/User.php

**必要修改**:

```php
<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject, FilamentUser
{
    use HasFactory, Notifiable, SoftDeletes;
    use HasRoles;  // 新增: Spatie Permission

    // ... 現有 constants 和 properties ...

    /**
     * Determine if the user can access the given Filament panel.
     *
     * 新增: Filament 權限檢查
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // 檢查 1: 必須是 admin 角色
        if ($this->role !== self::ROLE_ADMIN) {
            return false;
        }

        // 檢查 2: 帳號必須啟用
        if ($this->status !== 'active') {
            return false;
        }

        // 檢查 3: Email 必須驗證 (可選)
        // if (!$this->hasVerifiedEmail()) {
        //     return false;
        // }

        return true;
    }

    /**
     * Get the guard name for permissions.
     *
     * 新增: Spatie Permission guard
     */
    public function guardName(): string
    {
        return 'admin_session';
    }

    /**
     * Get the identifier that will be stored in the JWT subject claim.
     *
     * 原有: JWT 認證 (維持不變)
     */
    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * 原有: JWT 認證 (維持不變)
     */
    public function getJWTCustomClaims(): array
    {
        return [
            'role' => $this->role,
            'status' => $this->status,
        ];
    }

    /**
     * Get the password attribute name for authentication.
     *
     * 原有: 使用 password_hash 欄位 (維持不變)
     */
    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    // ... 其他現有方法 ...
}
```

---

## 🔐 Permission System Integration

### Spatie Permission Configuration

**已在 Installation 階段完成**:

- `roles` 表已建立
- `permissions` 表已建立
- `model_has_roles` 關聯表已建立
- `model_has_permissions` 關聯表已建立
- `role_has_permissions` 關聯表已建立

---

### Role & Permission Seeder

**檔案**: `database/seeders/FilamentAdminSeeder.php` (新建)

```php
<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class FilamentAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 清除 permission 快取
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 建立 Roles (guard: admin_session)
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

        // 建立 Permissions (Phase 1: 業務員申請審核)
        $permissions = [
            // Salesperson Application Permissions
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
        // Super Admin: 所有權限
        $superAdmin->syncPermissions(Permission::all());

        // Admin: 除了刪除以外的所有權限
        $admin->syncPermissions([
            'view_salesperson::application',
            'view_any_salesperson::application',
            'approve_salesperson::application',
            'reject_salesperson::application',
            'bulk_approve_salesperson::application',
        ]);

        // Reviewer: 僅查看和審核
        $reviewer->syncPermissions([
            'view_salesperson::application',
            'view_any_salesperson::application',
            'approve_salesperson::application',
            'reject_salesperson::application',
        ]);

        // 確保 Admin 使用者有 super_admin 角色
        $adminUser = User::where('email', 'admin@yamu.com')->first();
        if ($adminUser) {
            $adminUser->assignRole($superAdmin);
        }
    }
}
```

**執行 Seeder**:

```bash
php artisan db:seed --class=FilamentAdminSeeder
```

---

## 🧪 Testing Authentication

### 1. Test Session Authentication (Filament)

**Manual Test**:

```bash
# 1. 訪問 Filament
open http://localhost:8080/filament/admin

# 2. 應該自動重導向到登入頁面
# URL: http://localhost:8080/filament/admin/login

# 3. 輸入 Admin 帳號密碼
Email: admin@yamu.com
Password: (你設定的密碼)

# 4. 登入成功後應該看到 Dashboard
# URL: http://localhost:8080/filament/admin

# 5. 檢查 Cookie
# Chrome DevTools → Application → Cookies
# 應該看到: laravel_session, XSRF-TOKEN
```

---

### 2. Test JWT Authentication (API)

**API Test** (應該完全不受影響):

```bash
# 1. API 登入 (JWT)
curl -X POST http://localhost:8080/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password123"
  }'

# 預期回應
{
  "success": true,
  "data": {
    "user": { ... },
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "refresh_token": "dGhpc2lzYXJlZnJlc2h0b2tlbg...",
    "token_type": "bearer",
    "expires_in": 3600
  }
}

# 2. 使用 JWT Token 存取 API
curl -X GET http://localhost:8080/api/auth/me \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc..."

# 預期回應
{
  "success": true,
  "data": {
    "id": 1,
    "email": "test@example.com",
    "role": "user",
    ...
  }
}

# 3. 使用 JWT Token 存取 Admin API
curl -X GET http://localhost:8080/api/admin/statistics \
  -H "Authorization: Bearer {admin_jwt_token}"

# 預期回應 (如果是 admin role)
{
  "success": true,
  "data": {
    "total_salespeople": 150,
    "active_salespeople": 120,
    ...
  }
}
```

---

### 3. Test Guard Isolation

**驗證兩個 Guard 互不干擾**:

```php
// Tinker 測試
php artisan tinker

>>> // 測試 JWT Guard (API)
>>> $user = \App\Models\User::find(1);
>>> $token = auth('api')->login($user);
>>> $token  // 應該返回 JWT Token

>>> // 測試 Session Guard (Filament)
>>> auth('admin_session')->attempt(['email' => 'admin@yamu.com', 'password' => 'password']);
>>> auth('admin_session')->check()  // 應該返回 true
>>> auth('admin_session')->user()   // 應該返回 User instance

>>> // 驗證互不干擾
>>> auth('api')->check()  // 應該返回 false (沒有 JWT Token)
>>> auth('admin_session')->check()  // 應該返回 true (有 Session)
```

---

## 🔒 Security Best Practices

### 1. Session Security

```php
// config/session.php
return [
    'driver' => env('SESSION_DRIVER', 'file'),
    'lifetime' => env('SESSION_LIFETIME', 120),  // 2 hours
    'expire_on_close' => false,
    'encrypt' => true,
    'http_only' => true,  // 防止 XSS 存取 Cookie
    'same_site' => 'lax',  // CSRF 防護
    'secure' => env('SESSION_SECURE_COOKIE', false),  // 生產環境設為 true
];
```

---

### 2. CSRF Protection

**Filament 自動啟用 CSRF 保護**:

```php
// Filament Panel Middleware (自動包含)
\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
```

**重要**:
- 所有 Filament 表單自動包含 CSRF Token
- API routes 不受 CSRF 影響 (使用 JWT)

---

### 3. Rate Limiting

**登入失敗限制** (Laravel 預設):

```php
// config/auth.php (Filament 使用 Laravel 預設)
'passwords' => [
    'users' => [
        'throttle' => 60,  // 60 秒內最多嘗試一次
    ],
],
```

**API Rate Limiting** (維持不變):

```php
// routes/api.php
Route::middleware('throttle:10,1')->prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    // 10 次 / 分鐘
});
```

---

### 4. Password Security

**Filament 使用 Laravel 預設 Password Hash**:

```php
// User Model (維持不變)
protected function casts(): array
{
    return [
        'password_hash' => 'hashed',  // 自動使用 bcrypt
    ];
}
```

---

## 🔄 Token vs Session Comparison

| 特性 | JWT (API) | Session (Filament) |
|------|-----------|-------------------|
| **儲存位置** | Client Side (Memory/Cookie) | Server Side (File/Redis) |
| **傳遞方式** | HTTP Header (Authorization) | HTTP Cookie (laravel_session) |
| **Stateless** | ✅ Yes | ❌ No (需要 server state) |
| **Scalability** | ✅ 高 (無需 server state) | ⚠️ 中 (需要共享 session) |
| **Security** | ⚠️ Token 無法即時撤銷 | ✅ 可即時撤銷 (刪除 session) |
| **使用場景** | Mobile App, SPA Frontend | Traditional Web, Admin Panel |
| **CSRF Protection** | ❌ 不需要 | ✅ 必須啟用 |
| **Expiration** | Token 內建過期時間 | Session 過期 + Cookie 過期 |
| **適合對象** | Next.js Frontend (API Calls) | Filament Admin (Web UI) |

---

## ✅ Acceptance Criteria

Phase 1 認證架構完成檢查清單:

### Configuration
- [ ] `admin_session` guard 已新增到 config/auth.php
- [ ] `api` guard 配置維持不變 (JWT driver)
- [ ] Session 配置正確 (http_only, same_site, encrypt)

### User Model
- [ ] User Model 實作 `FilamentUser` 介面
- [ ] `canAccessPanel()` 方法已實作
- [ ] User Model 使用 `HasRoles` trait
- [ ] `guardName()` 方法返回 `admin_session`
- [ ] JWT 相關方法維持不變

### Permissions
- [ ] Spatie Permission 已安裝
- [ ] `super_admin` role 已建立
- [ ] `admin` role 已建立
- [ ] `reviewer` role 已建立
- [ ] 業務員申請相關 permissions 已建立
- [ ] Admin 使用者已指派 `super_admin` role

### Functionality
- [ ] Filament 登入頁面可訪問
- [ ] Admin 帳號可成功登入 Filament
- [ ] 登入後 Session Cookie 正常設定
- [ ] API JWT 登入仍正常運作
- [ ] API JWT Token 存取仍正常
- [ ] 兩個 Guard 互不干擾

### Security
- [ ] CSRF Token 正常運作
- [ ] Session Cookie 設定 http_only
- [ ] Session Cookie 設定 same_site=lax
- [ ] 非 admin 角色無法訪問 Filament
- [ ] 停用的帳號無法登入

---

## 📝 Migration Checklist

從現有系統遷移到雙認證架構:

1. **備份**
   - [ ] 備份 config/auth.php
   - [ ] 備份 app/Models/User.php
   - [ ] 備份資料庫

2. **安裝**
   - [ ] 執行 composer require spatie/laravel-permission
   - [ ] 執行 permissions migrations
   - [ ] 執行 FilamentAdminSeeder

3. **配置**
   - [ ] 新增 admin_session guard
   - [ ] 更新 User Model (FilamentUser)
   - [ ] 更新 User Model (HasRoles)

4. **測試**
   - [ ] 測試 Filament 登入
   - [ ] 測試 API JWT 登入
   - [ ] 測試權限系統

5. **驗證**
   - [ ] 確認 API 功能不受影響
   - [ ] 確認 Next.js Frontend 不受影響
   - [ ] 確認 Session 正常運作

---

## 🐛 Common Issues

### Issue: "Target class [FilamentUser] does not exist"

**原因**: 忘記在 User Model 實作 FilamentUser 介面

**解決**:
```php
use Filament\Models\Contracts\FilamentUser;

class User extends Authenticatable implements JWTSubject, FilamentUser
```

---

### Issue: "Permission does not exist for guard admin_session"

**原因**: Permission 使用錯誤的 guard

**解決**:
```bash
# 清除 permission 快取
php artisan permission:cache-reset

# 重新執行 seeder
php artisan db:seed --class=FilamentAdminSeeder
```

---

### Issue: API 認證突然失效

**原因**: Guard 配置錯誤

**解決**:
```bash
# 檢查 config/auth.php
# 確保 'api' guard 使用 'jwt' driver

# 清除配置快取
php artisan config:clear
php artisan config:cache
```

---

**文檔版本**: 1.0
**最後更新**: 2026-01-24
**負責人**: Backend Team
