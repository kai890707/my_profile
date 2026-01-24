# Filament Phase 1: 安裝與設定規格

**專案**: YAMU Backend - Filament Admin Panel
**Phase**: 1 - 基礎設施與審核功能
**預估時間**: 30 分鐘

---

## 📦 Composer Packages

### Required Packages

```json
{
  "require": {
    "filament/filament": "^3.2",
    "bezhansalleh/filament-shield": "^3.2",
    "spatie/laravel-permission": "^6.0"
  }
}
```

### 版本說明

- **Filament**: ^3.2 (最新穩定版)
- **Filament Shield**: ^3.2 (權限管理 plugin)
- **Spatie Permissions**: ^6.0 (Laravel 11 相容)

---

## 🚀 Installation Steps

### 1. Install Filament Core

```bash
# 進入 Laravel 容器
docker exec -it my_profile_laravel_app bash

# 安裝 Filament
composer require filament/filament:"^3.2"

# 執行 Filament 安裝
php artisan filament:install --panels
```

**提示**:
- 選擇 Panel ID: `admin`
- 選擇路徑: `/filament/admin`
- 不要創建使用者（稍後手動創建）

---

### 2. Install Filament Shield

```bash
# 安裝 Shield (包含 Spatie Permissions)
composer require bezhansalleh/filament-shield:"^3.2"

# 發布 Shield 配置檔案
php artisan vendor:publish --tag="filament-shield-config"

# 執行 Shield 安裝
php artisan shield:install
```

**執行 Shield 安裝時選項**:
- 選擇 `yes` - 發布 migrations
- 選擇 `yes` - 執行 migrations
- 選擇 `yes` - 創建 super_admin 角色

---

### 3. Run Migrations

```bash
# 確保所有 migrations 執行
php artisan migrate

# 驗證新增的表
php artisan db:show --table=roles
php artisan db:show --table=permissions
```

**預期新增的資料表**:
- `roles` - 角色表
- `permissions` - 權限表
- `model_has_roles` - 使用者角色關聯
- `model_has_permissions` - 使用者權限關聯
- `role_has_permissions` - 角色權限關聯

---

### 4. Create Admin User

```bash
# 創建第一個 Admin 使用者
php artisan make:filament-user
```

**輸入資訊**:
```
Name: Admin
Email: admin@yamu.com
Password: (輸入安全密碼)
Password Confirmation: (再次輸入)
```

**重要**: 記錄此帳號密碼，這是唯一的 super_admin 帳號。

---

## ⚙️ Configuration

### 1. Filament Panel Configuration

**檔案**: `app/Providers/Filament/AdminPanelProvider.php`

```php
<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('admin')
            ->path('filament/admin')
            ->login()
            ->registration(false)  // 禁用註冊
            ->passwordReset()
            ->profile()
            ->colors([
                'primary' => Color::hex('#0EA5E9'),  // Sky-500
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                // Dashboard Widgets will be added later
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->authGuard('admin_session')  // 使用新的 session guard
            ->plugin(\BezhanSalleh\FilamentShield\FilamentShieldPlugin::make());
    }
}
```

---

### 2. Authentication Guard Configuration

**檔案**: `config/auth.php`

**新增 Guard**:

```php
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],

    'api' => [
        'driver' => 'jwt',
        'provider' => 'users',
    ],

    // 新增 Filament Admin Guard
    'admin_session' => [
        'driver' => 'session',
        'provider' => 'users',
    ],
],
```

**重要**:
- `api` guard 繼續使用 `jwt` (不變動)
- `admin_session` 使用 `session` driver (新增)
- 兩者完全隔離，使用相同的 `users` provider

---

### 3. User Model Integration

**檔案**: `app/Models/User.php`

**新增 Filament 介面**:

```php
<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject, FilamentUser
{
    use HasRoles;  // 新增 Spatie Permission Trait

    // ... 現有程式碼 ...

    /**
     * Determine if the user can access the Filament panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // 只有 admin 角色可以訪問 Filament
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Get the guard name for permissions.
     */
    public function guardName(): string
    {
        return 'admin_session';
    }
}
```

---

### 4. Shield Configuration

**檔案**: `config/filament-shield.php`

**關鍵配置**:

```php
return [
    'shield_resource' => [
        'should_register_navigation' => true,
        'slug' => 'shield/roles',
        'navigation_sort' => -1,
        'navigation_badge' => true,
        'navigation_group' => true,
        'is_globally_searchable' => false,
        'show_model_path' => true,
    ],

    'auth_provider_model' => [
        'fqcn' => 'App\\Models\\User',
    ],

    'super_admin' => [
        'enabled' => true,
        'name' => 'super_admin',
        'define_via_gate' => false,
    ],

    'permission_prefixes' => [
        'resource' => [
            'view',
            'view_any',
            'create',
            'update',
            'restore',
            'restore_any',
            'replicate',
            'reorder',
            'delete',
            'delete_any',
            'force_delete',
            'force_delete_any',
        ],

        'page' => 'page',
        'widget' => 'widget',
    ],

    'entities' => [
        'pages' => true,
        'widgets' => true,
        'resources' => true,
        'custom_permissions' => false,
    ],

    'generator' => [
        'option' => 'policies_and_permissions',
    ],

    'exclude' => [
        'enabled' => true,

        'pages' => [
            'Dashboard',
        ],

        'widgets' => [
            'AccountWidget', 'FilamentInfoWidget',
        ],

        'resources' => [],
    ],

    'register_role_policy' => [
        'enabled' => true,
    ],
];
```

---

### 5. Environment Variables

**檔案**: `.env`

**新增配置**:

```env
# Filament Admin Panel
FILAMENT_PANEL_PATH=/filament/admin
FILAMENT_DEFAULT_LOCALE=zh_TW
FILAMENT_BROADCAST_DRIVER=log

# Auth Guards (確認現有設定)
AUTH_GUARD=web
AUTH_PASSWORD_BROKER=users
```

**不需要變更**:
- JWT 相關設定保持不變
- Database 設定保持不變

---

## 🧪 Verification Steps

### 1. Check Installation

```bash
# 檢查 Filament 是否安裝
php artisan filament:info

# 預期輸出
# Filament version: 3.2.x
# Panel: admin
# Path: /filament/admin
```

---

### 2. Check Routes

```bash
# 檢查 Filament routes
php artisan route:list --path=filament

# 預期輸出
# GET|HEAD  filament/admin/login
# POST      filament/admin/login
# GET|HEAD  filament/admin
# ...
```

---

### 3. Check Permissions

```bash
# 進入 Tinker
php artisan tinker

# 檢查 Role 是否創建
>>> \Spatie\Permission\Models\Role::all()

# 預期輸出
# [
#   {
#     "id": 1,
#     "name": "super_admin",
#     "guard_name": "admin_session",
#   }
# ]
```

---

### 4. Access Admin Panel

**瀏覽器測試**:

1. 訪問: `http://localhost:8080/filament/admin`
2. 應該看到 Filament 登入頁面
3. 使用創建的 admin 帳號登入
4. 成功登入後應該看到空白的 Dashboard

**預期結果**:
- ✅ 登入頁面正常顯示
- ✅ CSRF Token 正常運作
- ✅ 可以使用 admin 帳號登入
- ✅ Dashboard 正常載入（雖然是空白）

---

### 5. API 功能驗證

**確保 JWT API 仍正常運作**:

```bash
# 測試 API 登入 (JWT)
curl -X POST http://localhost:8080/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password123"
  }'

# 預期: JWT Token 正常返回
# {
#   "success": true,
#   "data": {
#     "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
#     ...
#   }
# }
```

**重要**: API 認證應該完全不受 Filament 安裝影響。

---

## 📁 Generated File Structure

安裝完成後應該有以下檔案結構:

```
app/
├── Filament/
│   ├── Resources/          # Filament Resources (稍後新增)
│   ├── Pages/              # Custom Pages (稍後新增)
│   └── Widgets/            # Dashboard Widgets (稍後新增)
├── Providers/
│   └── Filament/
│       └── AdminPanelProvider.php  # Panel 配置
└── Models/
    └── User.php            # 已更新 (新增 FilamentUser, HasRoles)

config/
├── filament.php            # Filament 核心配置
└── filament-shield.php     # Shield 配置

database/
└── migrations/
    ├── xxxx_create_permission_tables.php  # Spatie Permissions
    └── xxxx_add_shield_roles.php          # Shield Roles

resources/
└── views/
    └── vendor/
        └── filament/       # Filament Views (可客製化)
```

---

## 🔒 Security Considerations

### Session Security

```php
// config/session.php
return [
    'driver' => env('SESSION_DRIVER', 'file'),
    'lifetime' => env('SESSION_LIFETIME', 120),
    'expire_on_close' => false,
    'encrypt' => true,
    'http_only' => true,
    'same_site' => 'lax',
    'secure' => env('SESSION_SECURE_COOKIE', false),
];
```

**重要**:
- `http_only` 必須為 `true` (防止 XSS)
- `same_site` 設為 `lax` (CSRF 防護)
- 生產環境 `secure` 設為 `true` (HTTPS only)

---

### Admin Access Control

```php
// app/Http/Middleware/FilamentAdminOnly.php (可選)
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class FilamentAdminOnly
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth('admin_session')->check() ||
            auth('admin_session')->user()->role !== 'admin') {
            abort(403, 'Unauthorized access to admin panel');
        }

        return $next($request);
    }
}
```

---

## 🐛 Troubleshooting

### Issue 1: Cannot Access Admin Panel

**症狀**: 訪問 `/filament/admin` 出現 404

**解決方案**:
```bash
# 清除快取
php artisan optimize:clear

# 重新產生路由快取
php artisan route:cache
```

---

### Issue 2: CSRF Token Mismatch

**症狀**: 登入時出現 "CSRF token mismatch"

**解決方案**:
```bash
# 確認 session driver 正確
php artisan config:cache

# 清除 session
php artisan session:clear
```

---

### Issue 3: Permissions Not Working

**症狀**: Shield permissions 無法正常運作

**解決方案**:
```bash
# 清除 permission 快取
php artisan permission:cache-reset

# 重新產生 permissions
php artisan shield:generate
```

---

## ✅ Acceptance Criteria

Phase 1 安裝完成檢查清單:

- [ ] Filament 3.2+ 已安裝
- [ ] Shield 3.2+ 已安裝
- [ ] Spatie Permissions 已安裝
- [ ] Migrations 已執行 (roles, permissions 表已建立)
- [ ] AdminPanelProvider 已配置
- [ ] `admin_session` guard 已新增到 config/auth.php
- [ ] User Model 實作 FilamentUser 介面
- [ ] User Model 使用 HasRoles trait
- [ ] Admin 使用者已創建
- [ ] 可訪問 `/filament/admin` 登入頁面
- [ ] Admin 帳號可正常登入
- [ ] Dashboard 正常載入
- [ ] API JWT 認證仍正常運作 (未受影響)
- [ ] `super_admin` 角色已創建

---

## 📊 Performance Expectations

**目標效能** (參考 metrics-standards.md):

| 指標 | 目標值 | 測量方式 |
|------|--------|----------|
| 登入頁面 LCP | < 1s | Chrome DevTools |
| Dashboard 載入 | < 2s | Network Tab |
| 首次登入時間 | < 1.5s | 包含認證 + 重導向 |
| Session 創建 | < 100ms | Laravel Debugbar |

---

## 📝 Next Steps

Phase 1 完成後，進入 Phase 2:

1. 建立 SalespersonApplicationResource
2. 實作審核 Actions (Approve, Reject)
3. 新增 Table Filters
4. 實作 Bulk Actions
5. 客製化 Theme

**預估時間**: Phase 2 需要 1.5 小時

---

**文檔版本**: 1.0
**最後更新**: 2026-01-24
**負責人**: Backend Team
