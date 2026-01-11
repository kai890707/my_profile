# Implementation Tasks: Swagger API Documentation

**Feature**: swagger-api-documentation

**Date**: 2026-01-08

**Status**: Draft

**Estimated Total Time**: 4.5 hours

---

## Phase 1: Environment Setup (預估: 30 分鐘)

### Task 1.1: Install swagger-php via Composer

**Description**: 安裝 `zircote/swagger-php` 套件

**Command**:
```bash
cd my_profile_ci4
docker exec -it my_profile_ci4_app composer require zircote/swagger-php:^4.0
```

**Output**: `composer.json` 和 `composer.lock` 更新

**Validation**:
```bash
docker exec -it my_profile_ci4_app composer show | grep swagger-php
# 應顯示: zircote/swagger-php  4.x.x
```

**Estimated Time**: 10 分鐘

**Dependencies**: None

---

### Task 1.2: Add Swagger configuration to .env

**Description**: 在 `.env` 檔案新增 Swagger 相關配置

**File**: `my_profile_ci4/.env`

**Changes**: 在檔案末尾新增
```ini
#--------------------------------------------------------------------
# SWAGGER CONFIGURATION
#--------------------------------------------------------------------

SWAGGER_ENABLED=true
SWAGGER_OPENAPI_VERSION=3.0.0
SWAGGER_API_TITLE=業務推廣系統 API
SWAGGER_API_VERSION=1.0.0
SWAGGER_API_DESCRIPTION=業務員管理與搜尋平台的 RESTful API
SWAGGER_UI_DEEP_LINKING=true
SWAGGER_UI_DISPLAY_REQUEST_DURATION=true
SWAGGER_CACHE_ENABLED=false
```

**Validation**: 檢查 `.env` 檔案包含上述內容

**Estimated Time**: 5 分鐘

**Dependencies**: None

---

### Task 1.3: Update Routes configuration

**Description**: 在 `Routes.php` 新增 Swagger 端點路由

**File**: `my_profile_ci4/app/Config/Routes.php`

**Changes**: 在檔案末尾（`});` 之前）新增
```php
// Swagger API Documentation (Development Only)
$routes->group('api/docs', function($routes) {
    $routes->get('/', 'Api\SwaggerController::index');
    $routes->get('openapi.json', 'Api\SwaggerController::json');
});
```

**Validation**:
```bash
docker exec -it my_profile_ci4_app php spark routes | grep "api/docs"
# 應顯示:
# GET  api/docs              Api\SwaggerController::index
# GET  api/docs/openapi.json Api\SwaggerController::json
```

**Estimated Time**: 5 分鐘

**Dependencies**: None

---

### Task 1.4: Test environment detection

**Description**: 確認 `CI_ENVIRONMENT` 環境變數正確設定

**Command**:
```bash
docker exec -it my_profile_ci4_app php -r "echo getenv('CI_ENVIRONMENT');"
```

**Expected Output**: `development`

**Validation**: 輸出為 `development`

**Estimated Time**: 5 分鐘

**Dependencies**: None

---

### Task 1.5: Restart Docker container

**Description**: 重啟容器以載入新的環境變數

**Command**:
```bash
cd my_profile_ci4
docker-compose restart app
```

**Validation**:
```bash
docker ps | grep my_profile_ci4_app
# 應顯示容器正在運行
```

**Estimated Time**: 5 分鐘

**Dependencies**: Task 1.2

---

## Phase 2: Create SwaggerController (預估: 45 分鐘)

### Task 2.1: Create SwaggerController file

**Description**: 建立 `SwaggerController.php` 檔案骨架

**File**: `my_profile_ci4/app/Controllers/Api/SwaggerController.php`

**Content**: 建立基本 Controller 結構
```php
<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

class SwaggerController extends ResourceController
{
    protected $format = 'json';
    protected $controllersPath;
    protected $enabled;

    public function __construct()
    {
        // TODO: 初始化
    }

    public function index()
    {
        // TODO: 返回 Swagger UI
    }

    public function json()
    {
        // TODO: 返回 OpenAPI JSON
    }

    protected function getSwaggerUI(): string
    {
        // TODO: 生成 HTML
    }
}
```

**Validation**: 檔案存在且無語法錯誤

**Estimated Time**: 10 分鐘

**Dependencies**: Task 1.1

---

### Task 2.2: Implement __construct() method

**Description**: 實作 Constructor，設定掃描路徑和啟用狀態

**File**: `my_profile_ci4/app/Controllers/Api/SwaggerController.php`

**Implementation**: 參照 `specs/data-model.md` 的 SwaggerController 類別定義

**Key Logic**:
```php
$this->controllersPath = APPPATH . 'Controllers/Api';
$this->enabled = env('SWAGGER_ENABLED', true) &&
                env('CI_ENVIRONMENT') === 'development';
```

**Validation**: Constructor 正確初始化屬性

**Estimated Time**: 10 分鐘

**Dependencies**: Task 2.1

---

### Task 2.3: Implement index() method

**Description**: 實作 Swagger UI 顯示邏輯

**File**: `my_profile_ci4/app/Controllers/Api/SwaggerController.php`

**Implementation**:
- 檢查 `$this->enabled`
- 若 false，返回 `$this->failNotFound('Not Found')`
- 若 true，返回 `$this->getSwaggerUI()` HTML

**Validation**: 開發環境訪問 `http://localhost:8080/api/docs` 返回 HTML

**Estimated Time**: 10 分鐘

**Dependencies**: Task 2.2

---

### Task 2.4: Implement json() method

**Description**: 實作 OpenAPI JSON 生成邏輯

**File**: `my_profile_ci4/app/Controllers/Api/SwaggerController.php`

**Implementation**:
- 檢查 `$this->enabled`
- 使用 `\OpenApi\Generator::scan()` 掃描 Controllers
- Try-catch 錯誤處理
- 返回 JSON 並設定 `Cache-Control: no-cache`

**Validation**: 訪問 `http://localhost:8080/api/docs/openapi.json` 返回 JSON

**Estimated Time**: 10 分鐘

**Dependencies**: Task 2.3

---

### Task 2.5: Implement getSwaggerUI() method

**Description**: 實作 Swagger UI HTML 生成

**File**: `my_profile_ci4/app/Controllers/Api/SwaggerController.php`

**Implementation**: 參照 `specs/data-model.md` 的完整 HTML 模板

**Key Elements**:
- 引入 Swagger UI CDN (v5.x)
- 設定 `url: "/api/docs/openapi.json"`
- 啟用 `persistAuthorization: true`

**Validation**: HTML 包含 `swagger-ui` 元素

**Estimated Time**: 5 分鐘

**Dependencies**: Task 2.4

---

## Phase 3: Add OpenAPI Annotations - AuthController (預估: 45 分鐘)

### Task 3.1: Add class-level annotations to AuthController

**Description**: 在 `AuthController` 類別開頭加入 OpenAPI 基本資訊

**File**: `my_profile_ci4/app/Controllers/Api/AuthController.php`

**Changes**: 在 `class AuthController` 之前加入
```php
/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="業務推廣系統 API",
 *     description="業務員管理與搜尋平台的 RESTful API"
 * )
 * @OA\Server(url="http://localhost:8080", description="開發環境")
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="輸入 JWT Token（不需要加 'Bearer ' 前綴）"
 * )
 * @OA\Tag(name="認證", description="使用者認證與授權相關 API")
 */
class AuthController extends ResourceController
```

**Validation**: 訪問 `/api/docs/openapi.json` 包含 `info` 和 `securitySchemes`

**Estimated Time**: 10 分鐘

**Dependencies**: Task 2.5

---

### Task 3.2: Add annotation for POST /api/auth/register

**Description**: 為 `register()` 方法加入完整 OpenAPI 註解

**File**: `my_profile_ci4/app/Controllers/Api/AuthController.php`

**Implementation**: 在方法之前加入
```php
/**
 * @OA\Post(
 *     path="/api/auth/register",
 *     tags={"認證"},
 *     summary="業務員註冊",
 *     description="建立新的業務員帳號並自動建立個人檔案",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"username", "email", "password", "full_name"},
 *             @OA\Property(property="username", type="string", minLength=3, maxLength=50, example="john_doe"),
 *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
 *             @OA\Property(property="password", type="string", format="password", minLength=8, example="SecurePass123"),
 *             @OA\Property(property="full_name", type="string", minLength=2, maxLength=100, example="王小明"),
 *             @OA\Property(property="phone", type="string", pattern="^09\\d{8}$", example="0912345678")
 *         )
 *     ),
 *     @OA\Response(response=201, description="註冊成功",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(property="message", type="string", example="註冊成功")
 *         )
 *     ),
 *     @OA\Response(response=422, description="驗證失敗")
 * )
 */
```

**Validation**: Swagger UI 顯示 POST /api/auth/register

**Estimated Time**: 10 分鐘

**Dependencies**: Task 3.1

---

### Task 3.3: Add annotation for POST /api/auth/login

**Description**: 為 `login()` 方法加入註解

**Estimated Time**: 5 分鐘

**Dependencies**: Task 3.2

---

### Task 3.4: Add annotation for POST /api/auth/refresh

**Description**: 為 `refresh()` 方法加入註解

**Estimated Time**: 5 分鐘

**Dependencies**: Task 3.3

---

### Task 3.5: Add annotation for POST /api/auth/logout

**Description**: 為 `logout()` 方法加入註解，標記 `security={{"bearerAuth":{}}}`

**Estimated Time**: 5 分鐘

**Dependencies**: Task 3.4

---

### Task 3.6: Add annotation for GET /api/auth/me

**Description**: 為 `me()` 方法加入註解，標記 `security={{"bearerAuth":{}}}`

**Estimated Time**: 5 分鐘

**Dependencies**: Task 3.5

---

### Task 3.7: Test AuthController annotations

**Description**: 驗證 AuthController 的 5 個 API 在 Swagger UI 正確顯示

**Validation**:
- 訪問 `http://localhost:8080/api/docs`
- 檢查 "認證" Tag 下有 5 個端點
- 檢查 logout 和 me 顯示鎖頭圖示（需要認證）

**Estimated Time**: 5 分鐘

**Dependencies**: Task 3.6

---

## Phase 4: Add OpenAPI Annotations - SearchController (預估: 20 分鐘)

### Task 4.1: Add class-level annotations to SearchController

**Description**: 加入 Tag 定義

**File**: `my_profile_ci4/app/Controllers/Api/SearchController.php`

**Changes**:
```php
/**
 * @OA\Tag(name="搜尋", description="業務員搜尋相關 API")
 */
class SearchController extends ResourceController
```

**Estimated Time**: 5 分鐘

**Dependencies**: Task 3.7

---

### Task 4.2: Add annotation for GET /api/search/salespersons

**Description**: 為 `search()` 方法加入註解，包含查詢參數

**Key Parameters**:
- `keyword`: 搜尋關鍵字
- `industry`: 產業篩選
- `region`: 地區篩選
- `page`: 分頁頁碼
- `limit`: 每頁數量

**Estimated Time**: 10 分鐘

**Dependencies**: Task 4.1

---

### Task 4.3: Add annotation for GET /api/search/salespersons/{id}

**Description**: 為 `show()` 方法加入註解，包含路徑參數

**Key Parameter**:
- `id`: 業務員 ID (路徑參數)

**Estimated Time**: 5 分鐘

**Dependencies**: Task 4.2

---

## Phase 5: Add OpenAPI Annotations - SalespersonController (預估: 60 分鐘)

### Task 5.1: Add class-level annotations to SalespersonController

**Description**: 加入 Tag 定義

**File**: `my_profile_ci4/app/Controllers/Api/SalespersonController.php`

**Changes**:
```php
/**
 * @OA\Tag(name="業務員", description="業務員個人檔案管理 API")
 */
```

**Estimated Time**: 5 分鐘

**Dependencies**: Task 4.3

---

### Task 5.2: Add annotations for profile management endpoints

**Description**: 為以下方法加入註解（所有需要 `bearerAuth`）
- `getProfile()` - GET /api/salesperson/profile
- `updateProfile()` - PUT /api/salesperson/profile
- `saveCompany()` - POST /api/salesperson/company

**Estimated Time**: 20 分鐘

**Dependencies**: Task 5.1

---

### Task 5.3: Add annotations for experience endpoints

**Description**: 為以下方法加入註解
- `getExperiences()` - GET /api/salesperson/experiences
- `createExperience()` - POST /api/salesperson/experiences
- `deleteExperience()` - DELETE /api/salesperson/experiences/{id}

**Estimated Time**: 15 分鐘

**Dependencies**: Task 5.2

---

### Task 5.4: Add annotations for certification endpoints

**Description**: 為以下方法加入註解
- `getCertifications()` - GET /api/salesperson/certifications
- `createCertification()` - POST /api/salesperson/certifications

**Estimated Time**: 10 分鐘

**Dependencies**: Task 5.3

---

### Task 5.5: Add annotation for approval status endpoint

**Description**: 為 `getApprovalStatus()` 方法加入註解

**Estimated Time**: 5 分鐘

**Dependencies**: Task 5.4

---

### Task 5.6: Test SalespersonController annotations

**Description**: 驗證 SalespersonController 的 9 個 API 正確顯示

**Estimated Time**: 5 分鐘

**Dependencies**: Task 5.5

---

## Phase 6: Add OpenAPI Annotations - AdminController (預估: 90 分鐘)

### Task 6.1: Add class-level annotations to AdminController

**Description**: 加入 Tag 定義

**File**: `my_profile_ci4/app/Controllers/Api/AdminController.php`

**Changes**:
```php
/**
 * @OA\Tag(name="管理員", description="管理員後台管理 API")
 */
```

**Estimated Time**: 5 分鐘

**Dependencies**: Task 5.6

---

### Task 6.2: Add annotations for approval management endpoints (7 APIs)

**Description**: 為審核相關方法加入註解（所有需要 `bearerAuth`）
- `getPendingApprovals()` - GET /api/admin/pending-approvals
- `approveUser()` - POST /api/admin/approve-user/{id}
- `rejectUser()` - POST /api/admin/reject-user/{id}
- `approveCompany()` - POST /api/admin/approve-company/{id}
- `rejectCompany()` - POST /api/admin/reject-company/{id}
- `approveCertification()` - POST /api/admin/approve-certification/{id}
- `rejectCertification()` - POST /api/admin/reject-certification/{id}

**Estimated Time**: 35 分鐘

**Dependencies**: Task 6.1

---

### Task 6.3: Add annotations for user management endpoints (3 APIs)

**Description**: 為使用者管理方法加入註解
- `getUsers()` - GET /api/admin/users
- `updateUserStatus()` - PUT /api/admin/users/{id}/status
- `deleteUser()` - DELETE /api/admin/users/{id}

**Estimated Time**: 15 分鐘

**Dependencies**: Task 6.2

---

### Task 6.4: Add annotations for settings endpoints (4 APIs)

**Description**: 為系統設定方法加入註解
- `getIndustries()` - GET /api/admin/settings/industries
- `createIndustry()` - POST /api/admin/settings/industries
- `getRegions()` - GET /api/admin/settings/regions
- `createRegion()` - POST /api/admin/settings/regions

**Estimated Time**: 20 分鐘

**Dependencies**: Task 6.3

---

### Task 6.5: Add annotation for statistics endpoint

**Description**: 為 `getStatistics()` 方法加入註解

**Estimated Time**: 10 分鐘

**Dependencies**: Task 6.4

---

### Task 6.6: Test AdminController annotations

**Description**: 驗證 AdminController 的 16 個 API 正確顯示

**Estimated Time**: 5 分鐘

**Dependencies**: Task 6.5

---

## Phase 7: Define Common Schemas (預估: 15 分鐘)

### Task 7.1: Define reusable Schema components

**Description**: 在 `AuthController.php` 加入常用 Schema 定義

**File**: `my_profile_ci4/app/Controllers/Api/AuthController.php`

**Schemas to Define**:
- `User` - 使用者物件
- `ValidationError` - 驗證錯誤回應
- `ErrorResponse` - 一般錯誤回應
- `SalespersonProfile` - 業務員檔案物件
- `Experience` - 工作經驗物件
- `Certification` - 證照物件

**Implementation**: 在類別註解區塊加入 `@OA\Schema` 定義

**Estimated Time**: 15 分鐘

**Dependencies**: Task 6.6

---

## Phase 8: Testing & Validation (預估: 30 分鐘)

### Task 8.1: Test Swagger UI accessibility

**Description**: 測試 Swagger UI 在不同環境的可訪問性

**Test Cases**:
```bash
# 開發環境 - 應成功
curl -I http://localhost:8080/api/docs
# 預期: HTTP/1.1 200 OK

# 測試 OpenAPI JSON
curl http://localhost:8080/api/docs/openapi.json | jq '.openapi'
# 預期: "3.0.0"
```

**Validation**:
- [x] 開發環境返回 200 OK
- [x] OpenAPI JSON 有效
- [x] Swagger UI 正確載入

**Estimated Time**: 10 分鐘

**Dependencies**: Task 7.1

---

### Task 8.2: Test JWT authentication in Swagger UI

**Description**: 測試 JWT Token 輸入和認證功能

**Test Steps**:
1. 在 Swagger UI 測試 POST /api/auth/login 取得 Token
2. 點擊 "Authorize" 按鈕
3. 輸入 access_token（不含 "Bearer "）
4. 測試 GET /api/auth/me 端點
5. 確認返回 200 OK 和使用者資訊

**Validation**:
- [x] Authorize 按鈕正常運作
- [x] 輸入 Token 後可成功呼叫需要認證的 API
- [x] 未輸入 Token 時返回 401 Unauthorized

**Estimated Time**: 10 分鐘

**Dependencies**: Task 8.1

---

### Task 8.3: Verify all endpoints are documented

**Description**: 檢查所有 32 個 API 端點是否都在 Swagger UI 中顯示

**Checklist**:
- [ ] 認證模組: 5 APIs
- [ ] 搜尋模組: 2 APIs
- [ ] 業務員模組: 9 APIs
- [ ] 管理員模組: 16 APIs

**Total**: 32 APIs

**Validation**: 在 Swagger UI 中逐一確認每個端點

**Estimated Time**: 10 分鐘

**Dependencies**: Task 8.2

---

## Phase 9: Production Environment Testing (預估: 10 分鐘)

### Task 9.1: Test production environment hiding

**Description**: 模擬生產環境，確認 Swagger UI 被隱藏

**Test Steps**:
```bash
# 修改 .env
CI_ENVIRONMENT=production

# 重啟容器
docker-compose restart app

# 測試訪問
curl -I http://localhost:8080/api/docs
# 預期: HTTP/1.1 404 Not Found

# 恢復開發環境
CI_ENVIRONMENT=development
docker-compose restart app
```

**Validation**:
- [x] 生產環境返回 404
- [x] 開發環境恢復後正常運作

**Estimated Time**: 10 分鐘

**Dependencies**: Task 8.3

---

## Phase 10: Documentation & Cleanup (預估: 15 分鐘)

### Task 10.1: Add inline documentation

**Description**: 確保 SwaggerController 程式碼有適當的註解

**File**: `my_profile_ci4/app/Controllers/Api/SwaggerController.php`

**Requirements**:
- 每個方法都有 PHPDoc 註解
- 關鍵邏輯有行內註解說明

**Estimated Time**: 10 分鐘

**Dependencies**: Task 9.1

---

### Task 10.2: Verify no sensitive information

**Description**: 檢查所有 Swagger 註解，確保無敏感資訊

**Check Points**:
- [ ] 無真實密碼
- [ ] 無真實 Token
- [ ] 無真實個資
- [ ] 無資料庫連線字串
- [ ] 範例使用假資料

**Validation**: Code review 通過

**Estimated Time**: 5 分鐘

**Dependencies**: Task 10.1

---

## Task Dependencies Diagram

```
Phase 1 (Environment Setup)
Task 1.1 → Task 1.2 → Task 1.5
       ↓
     Task 1.3 → Task 1.4
       ↓
Phase 2 (SwaggerController)
Task 2.1 → Task 2.2 → Task 2.3 → Task 2.4 → Task 2.5
       ↓
Phase 3 (AuthController)
Task 3.1 → 3.2 → 3.3 → 3.4 → 3.5 → 3.6 → 3.7
       ↓
Phase 4 (SearchController)
Task 4.1 → Task 4.2 → Task 4.3
       ↓
Phase 5 (SalespersonController)
Task 5.1 → 5.2 → 5.3 → 5.4 → 5.5 → 5.6
       ↓
Phase 6 (AdminController)
Task 6.1 → 6.2 → 6.3 → 6.4 → 6.5 → 6.6
       ↓
Phase 7 (Schemas)
Task 7.1
       ↓
Phase 8 (Testing)
Task 8.1 → Task 8.2 → Task 8.3
       ↓
Phase 9 (Production Test)
Task 9.1
       ↓
Phase 10 (Documentation)
Task 10.1 → Task 10.2
```

---

## Completion Checklist

功能完成時必須確認：

### 功能驗收
- [ ] 開發環境可訪問 Swagger UI (`http://localhost:8080/api/docs`)
- [ ] 生產環境無法訪問 Swagger UI（返回 404）
- [ ] OpenAPI JSON 正確生成
- [ ] 所有 32 個 API 端點都有文件
- [ ] JWT 認證功能正常（Authorize 按鈕可用）
- [ ] Try it out 功能可測試 API

### 程式碼品質
- [ ] 無語法錯誤
- [ ] 遵循 CodeIgniter 4 規範
- [ ] Swagger 註解符合 OpenAPI 3.0 標準
- [ ] 無敏感資訊洩漏

### 文件品質
- [ ] 每個端點都有清楚的中文說明
- [ ] Request/Response 範例完整
- [ ] 錯誤回應有說明
- [ ] Tag 分類正確

---

## Notes

- 所有 Swagger 註解使用繁體中文撰寫
- 範例資料使用假資料，不包含真實個資
- 開發過程中隨時測試，確保註解正確
- 若掃描發生錯誤，檢查 `writable/logs/log-{date}.log`

---

## Risk Mitigation

- **風險**: 註解語法錯誤導致無法生成 OpenAPI JSON
  **緩解**: 每完成一個 Controller 立即測試 `/api/docs/openapi.json`

- **風險**: 任務過多容易遺漏端點
  **緩解**: 使用 Task 8.3 的檢查清單逐一驗證

- **風險**: 生產環境誤啟用 Swagger
  **緩解**: 使用 Task 9.1 明確測試生產環境隱藏功能

---

**Total Tasks**: 45 tasks

**Total Estimated Time**: 270 minutes (4.5 hours)

**Critical Path**: Task 1.1 → 2.1 → 3.1 → 4.1 → 5.1 → 6.1 → 7.1 → 8.1 → 9.1 → 10.2
