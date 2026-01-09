# YAMU Backend API - Laravel 11

> 業務員推廣系統後端 API - 高品質、可測試、生產就緒

[![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?logo=php)](https://php.net)
[![Tests](https://img.shields.io/badge/Tests-201%20passing-success)](tests)
[![Coverage](https://img.shields.io/badge/Coverage-80%25-success)](tests)
[![PHPStan](https://img.shields.io/badge/PHPStan-Level%209-success)](phpstan.neon)

---

## 📋 專案簡介

這是 YAMU 業務員推廣系統的後端 API，提供完整的 RESTful API 服務。

### 核心特性

- 🔐 **JWT 雙令牌認證** - Access Token (60分) + Refresh Token (7天)
- 👥 **三級角色權限** - Admin、Salesperson、User
- 📝 **完整 CRUD 操作** - 業務員檔案、公司、經歷、證照
- 🔍 **進階搜尋功能** - 多條件篩選、地區搜尋
- ✅ **審核流程管理** - 完整的申請與審核機制
- 📊 **Swagger 文檔** - OpenAPI 3.0 互動式 API 文檔
- 🧪 **高測試覆蓋率** - 201 測試、80%+ 覆蓋率
- 🛡️ **代碼品質保證** - PHPStan Level 9、Strict Types

---

## 🚀 快速開始

### 必要條件

- Docker Desktop 或 Docker Engine
- Git

### 安裝與啟動

```bash
# 1. Clone 專案
git clone https://github.com/kai890707/my_profile.git
cd my_profile/my_profile_laravel

# 2. 複製環境設定檔
cp .env.example .env

# 3. 啟動 Docker 容器
docker-compose up -d

# 4. 安裝依賴（首次啟動）
docker exec -it my_profile_laravel_app composer install

# 5. 產生應用程式金鑰
docker exec -it my_profile_laravel_app php artisan key:generate

# 6. 執行資料庫遷移
docker exec -it my_profile_laravel_app php artisan migrate

# 7. 執行資料種子
docker exec -it my_profile_laravel_app php artisan db:seed

# 8. 測試 API
curl http://localhost:8080/api/health
# 應返回: {"status":"healthy","timestamp":"..."}
```

### 服務存取點

| 服務 | URL | 憑證 |
|------|-----|------|
| **API** | http://localhost:8080 | - |
| **Swagger UI** | http://localhost:8080/api/docs | - |
| **phpMyAdmin** | http://localhost:8081 | root / 123456 |
| **MySQL** | localhost:3306 | laravel / 123456 |

---

## 📡 API 文檔

### Swagger UI (推薦)

訪問互動式 API 文檔：http://localhost:8080/api/docs

特性：
- 🎯 **Try it out** - 直接測試 API
- 📚 **完整範例** - Request/Response 範例
- 🔐 **JWT 認證** - 可配置 Bearer Token
- 📋 **28 個端點** - 完整覆蓋

### OpenAPI JSON

下載機器可讀的規格：http://localhost:8080/api/docs/openapi.json

用途：
- 自動產生客戶端代碼
- 導入 Postman/Insomnia
- API 測試工具整合

---

## 🏗️ 專案架構

```
my_profile_laravel/
├── app/
│   ├── Http/
│   │   ├── Controllers/Api/   # API 控制器 (5 個)
│   │   ├── Middleware/        # 中間件 (JWT, Role)
│   │   ├── Requests/          # Form Request 驗證 (15 個)
│   │   └── Resources/         # API Resources (8 個)
│   ├── Models/               # Eloquent Models (8 個)
│   ├── Services/             # 業務邏輯層 (3 個)
│   └── Policies/             # 授權策略 (3 個)
├── database/
│   ├── migrations/           # 資料庫遷移 (15 個)
│   ├── seeders/              # 資料種子
│   └── factories/            # Model Factories
├── tests/
│   ├── Feature/              # 功能測試 (165 tests)
│   └── Unit/                 # 單元測試 (36 tests)
├── docker/                   # Docker 配置
│   ├── nginx/                # Nginx 配置
│   └── php/                  # PHP-FPM 配置
├── docker-compose.yml        # Docker Compose 設定
├── phpstan.neon              # PHPStan 配置 (Level 9)
└── .env.example              # 環境變數範例
```

---

## 📊 技術規格

### 核心技術

| 技術 | 版本 | 用途 |
|------|------|------|
| Laravel | 11.x | Web Framework |
| PHP | 8.4 | 程式語言 |
| MySQL | 8.0 | 主資料庫 |
| tymon/jwt-auth | 2.x | JWT 認證 |
| PHPStan | Level 9 | 靜態分析 |
| Pest | 3.x | 測試框架 |
| zircote/swagger-php | 5.x | OpenAPI 文檔 |

### 架構模式

```
請求流程:
Request
  → Middleware (JWT 認證)
    → Controller (HTTP 層)
      → Form Request (驗證)
        → Service (業務邏輯)
          → Model (資料層)
            → Database
          ← Repository (查詢)
        ← Business Logic
      ← API Resource (格式化)
    ← JSON Response
  ← HTTP Response
```

**特性**:
- ✅ **Thin Controllers** - 控制器只處理 HTTP
- ✅ **Service Layer** - 業務邏輯集中管理
- ✅ **Repository Pattern** - 資料存取抽象化
- ✅ **Policy Authorization** - 權限檢查統一管理
- ✅ **Resource Transformation** - 一致的 API 響應格式

---

## 🗄️ 資料庫

### Schema 概覽

```
users (用戶)
├── id
├── username
├── email
├── password
├── role (admin/salesperson/user)
└── status (active/pending/suspended)

salesperson_profiles (業務員檔案)
├── id
├── user_id → users
├── company_id → companies
├── full_name
├── phone
├── avatar
└── service_regions (JSON)

companies (公司)
├── id
├── user_id → users
├── name
├── industry_id → industries
├── size
└── status (approved/pending/rejected)

industries (產業類別)
├── id
└── name

regions (地區)
├── id
├── name
└── parent_id → regions
```

完整 Schema 定義: [Database Migrations](database/migrations/)

### 資料種子

```bash
# 執行所有種子
php artisan db:seed

# 執行特定種子
php artisan db:seed --class=IndustrySeeder
php artisan db:seed --class=RegionSeeder
```

**預設資料**:
- 10 個產業類別
- 368 個地區 (縣市 + 鄉鎮市區)
- 1 個管理員帳號 (開發環境)

---

## 🧪 測試

### 執行測試

```bash
# 執行所有測試
php artisan test

# 執行 Feature Tests
php artisan test --testsuite=Feature

# 執行 Unit Tests
php artisan test --testsuite=Unit

# 產生覆蓋率報告
php artisan test --coverage

# 最小覆蓋率要求
php artisan test --coverage --min=80
```

### 測試統計

```
Tests:    201 passed
  Feature: 165 tests (API integration)
  Unit:     36 tests (Service layer)

Coverage: 80%+
  Controllers: 100%
  Services:    95%
  Models:      85%

Duration: ~6 seconds
```

### 測試結構

```
tests/
├── Feature/
│   ├── Auth/              # 44 tests
│   │   ├── RegisterTest.php
│   │   ├── LoginTest.php
│   │   ├── RefreshTest.php
│   │   ├── LogoutTest.php
│   │   └── MeTest.php
│   ├── Profile/           # 51 tests
│   │   ├── IndexTest.php
│   │   ├── ShowTest.php
│   │   ├── MeTest.php
│   │   ├── CreateTest.php
│   │   ├── UpdateTest.php
│   │   └── DeleteTest.php
│   ├── Company/           # 44 tests
│   └── Admin/             # 26 tests
└── Unit/
    └── Services/          # 36 tests
        ├── AuthServiceTest.php
        ├── CompanyServiceTest.php
        └── SalespersonProfileServiceTest.php
```

---

## 🔐 認證與授權

### JWT 認證流程

```bash
# 1. 註冊
POST /api/auth/register
{
  "email": "user@example.com",
  "password": "password123",
  "full_name": "John Doe"
}
→ 返回: access_token, refresh_token, user

# 2. 使用 Access Token
GET /api/profile
Header: Authorization: Bearer <access_token>

# 3. 刷新令牌
POST /api/auth/refresh
Header: Authorization: Bearer <refresh_token>
→ 返回: 新的 access_token

# 4. 登出
POST /api/auth/logout
Header: Authorization: Bearer <access_token>
```

### Token 配置

| Token | 有效期 | 用途 |
|-------|--------|------|
| Access Token | 60 分鐘 | API 存取 |
| Refresh Token | 7 天 | 更新 Access Token |

### 角色權限

| 角色 | 權限 |
|------|------|
| **admin** | 完整系統管理、審核所有申請 |
| **salesperson** | 管理個人檔案、公司資料 |
| **user** | 查看公開資料 |

權限檢查使用 Laravel Policy: [app/Policies/](app/Policies/)

---

## 🛠️ 開發

### 代碼品質

```bash
# PHPStan 靜態分析 (Level 9)
vendor/bin/phpstan analyse

# Laravel Pint 代碼格式化
vendor/bin/pint

# 檢查代碼風格（不修改）
vendor/bin/pint --test
```

### 開發環境

```bash
# 查看日誌
docker logs -f my_profile_laravel_app

# 進入容器
docker exec -it my_profile_laravel_app bash

# 清除快取
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# 重新產生快取
php artisan config:cache
php artisan route:cache
```

### 新增 API 端點

1. **建立 Controller**:
   ```bash
   php artisan make:controller Api/FeatureController
   ```

2. **建立 Form Request**:
   ```bash
   php artisan make:request StoreFeatureRequest
   ```

3. **建立 API Resource**:
   ```bash
   php artisan make:resource FeatureResource
   ```

4. **新增路由** (`routes/api.php`):
   ```php
   Route::middleware('auth:api')->group(function () {
       Route::apiResource('features', FeatureController::class);
   });
   ```

5. **新增測試**:
   ```bash
   php artisan make:test Feature/FeatureTest --pest
   ```

6. **更新 Swagger 文檔** - 在 Controller 中新增 OpenAPI 註解

---

## 🚢 部署

### Docker 部署

```bash
# 生產環境建置
docker-compose -f docker-compose.prod.yml build

# 啟動生產環境
docker-compose -f docker-compose.prod.yml up -d

# 執行 Migration（生產環境）
docker exec -it app php artisan migrate --force
```

### 環境變數

關鍵環境變數（`.env`）:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_HOST=mysql
DB_DATABASE=my_profile_laravel
DB_USERNAME=laravel
DB_PASSWORD=<secure-password>

JWT_SECRET=<jwt-secret>
JWT_ACCESS_TOKEN_TTL=60
JWT_REFRESH_TOKEN_TTL=10080
```

### Health Check

```bash
# API 健康檢查
curl http://localhost:8080/api/health

# 資料庫連線檢查
php artisan db:monitor
```

完整部署文檔: [MODULE_07_COMPLETION.md](MODULE_07_COMPLETION.md)

---

## 📚 相關文檔

### 專案文檔

| 文檔 | 說明 |
|------|------|
| [MIGRATION_SUMMARY.md](MIGRATION_SUMMARY.md) | Laravel 遷移完成報告 |
| [MODULE_07_COMPLETION.md](MODULE_07_COMPLETION.md) | 生產部署完整指南 |
| [SWAGGER_IMPLEMENTATION.md](SWAGGER_IMPLEMENTATION.md) | Swagger 實作報告 |

### API 規範

| 文檔 | 說明 |
|------|------|
| [OpenSpec API Specs](../openspec/specs/api/endpoints.md) | 完整 API 規範 |
| [Data Models](../openspec/specs/models/data-models.md) | 資料模型定義 |
| [Architecture](../openspec/specs/architecture/overview.md) | 系統架構設計 |

---

## 🐛 常見問題

### Q: JWT Token 無效

**A**: 檢查 `.env` 中的 `JWT_SECRET` 是否已設定：
```bash
php artisan jwt:secret
```

### Q: 資料庫連線失敗

**A**: 確認 Docker 容器運行狀態：
```bash
docker-compose ps
docker logs my_profile_laravel_db
```

### Q: Migration 失敗

**A**: 重置資料庫並重新執行：
```bash
php artisan migrate:fresh --seed
```

### Q: 測試失敗

**A**: 清除測試資料庫並重新執行：
```bash
php artisan test --env=testing --recreate-databases
```

### Q: Swagger UI 顯示錯誤

**A**: 清除快取並重新產生：
```bash
php artisan config:clear
php artisan route:clear
```

---

## 🔧 維護

### 定期任務

```bash
# 清理過期的 JWT Token (建議每日執行)
php artisan jwt:prune

# 備份資料庫
php artisan backup:run

# 檢查系統健康狀況
php artisan system:check
```

### 日誌管理

日誌位置: `storage/logs/laravel.log`

```bash
# 查看最新日誌
tail -f storage/logs/laravel.log

# 清理舊日誌
php artisan log:clear --days=30
```

---

## 📈 效能優化

### 快取策略

```bash
# 產生所有快取
php artisan optimize

# 包含:
# - config:cache  (配置快取)
# - route:cache   (路由快取)
# - view:cache    (視圖快取)
```

### 資料庫優化

```php
// 使用 Eager Loading 避免 N+1 查詢
$profiles = SalespersonProfile::with(['user', 'company', 'industry'])->get();

// 使用索引加速查詢（已在 Migration 中定義）
// - users: email (unique)
// - salesperson_profiles: user_id, company_id, status
// - companies: user_id, status
```

---

## 📜 授權

此專案為個人作品集專案。

---

## 👤 維護者

**Kai Huang**
- GitHub: [@kai890707](https://github.com/kai890707)

---

## 🔗 相關連結

- [Laravel 11 Documentation](https://laravel.com/docs/11.x)
- [Pest Testing Framework](https://pestphp.com)
- [PHPStan Static Analysis](https://phpstan.org)
- [JWT Authentication](https://jwt-auth.readthedocs.io)
- [OpenAPI Specification](https://swagger.io/specification/)

---

**最後更新**: 2026-01-10 | **版本**: 1.0.0
