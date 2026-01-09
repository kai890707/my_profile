# Business Rules: Swagger API Documentation

**Feature**: swagger-api-documentation

**Date**: 2026-01-08

**Version**: 1.0

---

## Overview

本文件定義 Swagger API 文件系統的業務規則，包括環境控制、安全性、註解規範、文件品質等。

---

## BR-001: Environment-Based Visibility

**Rule**: Swagger UI 僅在開發環境可見，生產環境完全隱藏

**Implementation**:
- Application validation: 檢查 `CI_ENVIRONMENT` 環境變數
- 開發環境（`development`）→ 顯示 Swagger UI
- 非開發環境（`production`, `testing`）→ 返回 404

**Logic**:
```php
$enabled = env('SWAGGER_ENABLED', true) &&
           env('CI_ENVIRONMENT') === 'development';

if (!$enabled) {
    return $this->failNotFound('Not Found');
}
```

**Example**:
```
開發環境:
CI_ENVIRONMENT=development
GET /api/docs → 200 OK (Swagger UI)

生產環境:
CI_ENVIRONMENT=production
GET /api/docs → 404 Not Found

測試環境:
CI_ENVIRONMENT=testing
GET /api/docs → 404 Not Found
```

**Error Handling**:
- HTTP Status: 404 Not Found
- Error Message: "Not Found"

**Rationale**: 防止生產環境暴露 API 內部結構和實作細節

---

## BR-002: No Authentication Required for Swagger UI

**Rule**: 訪問 Swagger UI 本身不需要 JWT 認證

**Implementation**:
- Route configuration: `/api/docs` 不加 `auth` filter
- Public access in development environment

**Logic**:
```php
// Routes.php
$routes->group('api/docs', function($routes) {
    // 不加 ['filter' => 'auth']
    $routes->get('/', 'Api\SwaggerController::index');
    $routes->get('openapi.json', 'Api\SwaggerController::json');
});
```

**Example**:
```
無需 Token:
GET /api/docs → 200 OK

但在 Swagger UI 中測試需要認證的 API 時:
需要先在 "Authorize" 輸入 JWT Token
```

**Error Handling**:
- N/A (公開端點)

**Rationale**:
- 方便開發者快速查看 API 文件
- 實際 API 測試時仍需提供有效 Token

---

## BR-003: OpenAPI Specification Validation

**Rule**: 生成的 OpenAPI JSON 必須符合 OpenAPI 3.0.0 規範

**Implementation**:
- Application validation: 使用 `zircote/swagger-php` 庫自動驗證
- 掃描時發生錯誤返回 500 錯誤

**Logic**:
```php
try {
    $openapi = \OpenApi\Generator::scan([$controllersPath]);

    // 驗證規格
    if (!$openapi || $openapi->openapi !== '3.0.0') {
        throw new \Exception('Invalid OpenAPI specification');
    }

    return $openapi->toJson();

} catch (\Exception $e) {
    log_message('error', 'Swagger generation failed: ' . $e->getMessage());
    return $this->failServerError('Failed to generate OpenAPI specification');
}
```

**Example**:
```
Valid 註解:
@OA\Get(path="/api/test", ...)
→ 成功生成 OpenAPI JSON

Invalid 註解 (語法錯誤):
@OA\Get(path="/api/test", tags="invalid")  // tags 應該是陣列
→ HTTP 500, message: "Failed to generate OpenAPI specification"
```

**Error Handling**:
- HTTP Status: 500 Internal Server Error
- Error Message: "Failed to generate OpenAPI specification"
- Error Detail: `scan_error` 包含具體錯誤訊息
- Logging: 記錄錯誤到 `writable/logs/log-{date}.log`

**Rationale**: 確保生成的文件符合標準，可被 Swagger UI 正確解析

---

## BR-004: Controller Scanning

**Rule**: 僅掃描 `app/Controllers/Api/` 目錄下的 Controller

**Implementation**:
- Application logic: 限定掃描路徑
- 排除其他目錄（如 `app/Controllers/Home.php`）

**Logic**:
```php
protected $controllersPath = APPPATH . 'Controllers/Api';

$openapi = \OpenApi\Generator::scan([$this->controllersPath]);
```

**Example**:
```
掃描路徑: app/Controllers/Api/
包含:
  - AuthController.php ✓
  - SearchController.php ✓
  - SalespersonController.php ✓
  - AdminController.php ✓
  - SwaggerController.php ✓

不包含:
  - app/Controllers/Home.php ✗
  - app/Controllers/Portfolio.php ✗
```

**Error Handling**:
- 若目錄不存在 → HTTP 500
- 若無可掃描的 Controller → 返回空的 paths 物件

**Rationale**:
- 僅記錄 API 端點，不包含網頁 Controller
- 避免掃描不必要的檔案

---

## BR-005: JWT Security Scheme Definition

**Rule**: 所有需要認證的 API 必須標記 `bearerAuth` Security

**Implementation**:
- Annotation requirement: 在 OpenAPI 註解中定義 `@OA\SecurityScheme`
- 在需要認證的端點加上 `security={{"bearerAuth":{}}}`

**Logic**:
```php
// 定義 Security Scheme (在任一 Controller 類別中定義一次)
/**
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="輸入 JWT Token（不需要加 'Bearer ' 前綴）"
 * )
 */

// 在需要認證的端點標記
/**
 * @OA\Get(
 *     path="/api/auth/me",
 *     security={{"bearerAuth":{}}},
 *     ...
 * )
 */
```

**Example**:
```
公開端點 (無需認證):
POST /api/auth/register → 無 security 標記
POST /api/auth/login → 無 security 標記

需要認證的端點:
GET /api/auth/me → security={{"bearerAuth":{}}}
GET /api/salesperson/profile → security={{"bearerAuth":{}}}
GET /api/admin/users → security={{"bearerAuth":{}}}
```

**Error Handling**:
- N/A (僅影響文件顯示)

**Rationale**:
- 清楚標示哪些 API 需要認證
- 在 Swagger UI 中顯示鎖頭圖示
- 方便測試時輸入 Token

---

## BR-006: Annotation Completeness

**Rule**: 每個 API 端點必須包含完整的 Swagger 註解

**Implementation**:
- Development guideline: 強制包含以下元素
  - `path`: API 路徑
  - `tags`: 所屬分類
  - `summary`: 簡短說明
  - `description`: 詳細說明（可選）
  - `requestBody`: 請求內容（POST/PUT/PATCH）
  - `responses`: 至少包含成功回應（200/201）和錯誤回應（400/401/403/404/422）

**Checklist**:
```
✓ path - API 路徑
✓ tags - 分類標籤 (如 "認證", "搜尋", "業務員", "管理員")
✓ summary - 一句話說明 (如 "業務員註冊")
✓ description - 詳細說明 (可選，但建議加)
✓ requestBody - Request Body 定義 (POST/PUT/PATCH 必填)
✓ parameters - 路徑參數、查詢參數
✓ responses - 至少 200/201 + 錯誤回應
✓ security - 是否需要認證
```

**Example - Incomplete** ❌:
```php
/**
 * @OA\Post(path="/api/auth/register")
 */
public function register() { ... }

// 問題: 缺少 tags, summary, requestBody, responses
```

**Example - Complete** ✅:
```php
/**
 * @OA\Post(
 *     path="/api/auth/register",
 *     tags={"認證"},
 *     summary="業務員註冊",
 *     description="建立新的業務員帳號並自動建立個人檔案",
 *     @OA\RequestBody(...),
 *     @OA\Response(response=201, ...),
 *     @OA\Response(response=422, ...)
 * )
 */
public function register() { ... }
```

**Error Handling**:
- N/A (不影響執行，但文件不完整)

**Rationale**: 確保 API 文件品質，提供完整資訊給使用者

---

## BR-007: Response Schema Consistency

**Rule**: 所有 API 的回應格式必須遵循統一的 Schema 結構

**Implementation**:
- Application validation: 使用預定義的 Schema
- 定義 `@OA\Schema` 在 Controller 中複用

**Standard Schemas**:

1. **Success Response**:
```json
{
    "status": "success",
    "message": "操作成功訊息",
    "data": { /* 實際資料 */ }
}
```

2. **Error Response**:
```json
{
    "status": "error",
    "message": "錯誤訊息"
}
```

3. **Validation Error**:
```json
{
    "status": "error",
    "message": "驗證失敗",
    "errors": {
        "field_name": ["錯誤訊息"]
    }
}
```

**Logic**:
```php
// 定義可複用的 Schema
/**
 * @OA\Schema(
 *     schema="SuccessResponse",
 *     type="object",
 *     @OA\Property(property="status", type="string", example="success"),
 *     @OA\Property(property="message", type="string"),
 *     @OA\Property(property="data", type="object")
 * )
 */

// 在端點中引用
/**
 * @OA\Response(
 *     response=200,
 *     description="成功",
 *     @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
 * )
 */
```

**Example**:
```
正確:
GET /api/auth/me → {"status": "success", "data": {...}}
POST /api/auth/register → {"status": "success", "data": {...}}

錯誤 (不一致):
GET /api/users → {"success": true, "result": {...}}  // ❌ 欄位名稱不同
```

**Error Handling**:
- N/A (僅影響文件一致性)

**Rationale**:
- 統一的回應格式便於前端處理
- 文件一致性提升可讀性

---

## BR-008: No Sensitive Information in Documentation

**Rule**: Swagger 註解和範例不得包含敏感資訊

**Implementation**:
- Development guideline: 檢查註解內容
- Code review: 確保無敏感資訊洩漏

**Forbidden Information**:
- ❌ 真實的資料庫連線字串
- ❌ 真實的 API 金鑰或密鑰
- ❌ 真實的使用者密碼（即使是範例）
- ❌ 真實的 JWT Token
- ❌ 真實的使用者個資（姓名、電話、Email 使用假資料）

**Allowed Examples**:
- ✅ 假資料：`example="john@example.com"`
- ✅ 範例密碼：`example="SecurePass123"` (明確標示為範例)
- ✅ 範例 Token：`example="eyJ0eXAiOiJKV1Qi..."` (截斷的範例)

**Example - Forbidden** ❌:
```php
/**
 * @OA\Property(
 *     property="db_password",
 *     example="my_real_db_pass_123"  // ❌ 真實密碼
 * )
 */
```

**Example - Allowed** ✅:
```php
/**
 * @OA\Property(
 *     property="password",
 *     type="string",
 *     format="password",
 *     example="SecurePass123"  // ✅ 範例密碼
 * )
 */
```

**Error Handling**:
- N/A (開發規範，透過 Code Review 把關)

**Rationale**: 防止透過文件洩漏系統敏感資訊

---

## BR-009: Tag Categorization

**Rule**: API 端點必須按照功能模組分類 Tag

**Implementation**:
- Annotation requirement: 每個端點必須有 `tags` 屬性
- 使用預定義的 Tag 分類

**Allowed Tags**:
1. **認證** (Authentication) - 登入、註冊、登出等
2. **搜尋** (Search) - 搜尋業務員
3. **業務員** (Salesperson) - 業務員個人檔案管理
4. **管理員** (Admin) - 管理員後台功能

**Logic**:
```php
/**
 * @OA\Tag(
 *     name="認證",
 *     description="使用者認證與授權相關 API"
 * )
 * @OA\Tag(
 *     name="搜尋",
 *     description="業務員搜尋相關 API"
 * )
 * @OA\Tag(
 *     name="業務員",
 *     description="業務員個人檔案管理 API"
 * )
 * @OA\Tag(
 *     name="管理員",
 *     description="管理員後台管理 API"
 * )
 */
```

**Example**:
```
POST /api/auth/register → tags={"認證"}
GET /api/search/salespersons → tags={"搜尋"}
GET /api/salesperson/profile → tags={"業務員"}
GET /api/admin/users → tags={"管理員"}
```

**Error Handling**:
- N/A (僅影響文件組織)

**Rationale**:
- 方便在 Swagger UI 中按功能瀏覽
- 提升文件可讀性

---

## BR-010: Example Data Quality

**Rule**: API 範例必須使用真實可用的資料格式

**Implementation**:
- Development guideline: 提供完整、正確的範例
- 範例資料必須符合驗證規則

**Requirements**:
- ✅ 符合欄位驗證規則 (如 email 格式、手機號碼格式)
- ✅ 範例值有意義 (不使用 `"string"`, `123` 等無意義值)
- ✅ 日期時間使用 ISO 8601 格式
- ✅ 繁體中文範例使用真實的中文詞彙

**Example - Poor** ❌:
```php
/**
 * @OA\Property(property="email", type="string", example="string")
 * @OA\Property(property="phone", type="string", example="123")
 * @OA\Property(property="full_name", type="string", example="name")
 */
```

**Example - Good** ✅:
```php
/**
 * @OA\Property(property="email", type="string", format="email", example="john@example.com")
 * @OA\Property(property="phone", type="string", pattern="^09\d{8}$", example="0912345678")
 * @OA\Property(property="full_name", type="string", example="王小明")
 */
```

**Error Handling**:
- N/A (僅影響文件品質)

**Rationale**:
- 高品質範例幫助開發者快速理解 API
- 可直接複製範例進行測試

---

## BR-011: HTTP Status Code Standards

**Rule**: API 回應必須使用正確的 HTTP 狀態碼

**Implementation**:
- Documentation requirement: 在 `@OA\Response` 中正確標記狀態碼
- Application implementation: Controller 實際回應也要一致

**Standard Status Codes**:

| Status Code | Usage | Example Scenario |
|------------|-------|------------------|
| 200 OK | 成功 (GET, PUT, DELETE) | 查詢成功、更新成功、刪除成功 |
| 201 Created | 建立成功 (POST) | 註冊成功、新增資料成功 |
| 400 Bad Request | 請求格式錯誤 | JSON 格式不正確、必填欄位遺漏 |
| 401 Unauthorized | 未認證 | Token 無效或過期、未提供 Token |
| 403 Forbidden | 權限不足 | 非管理員訪問管理員 API |
| 404 Not Found | 資源不存在 | 查詢的 ID 不存在 |
| 422 Unprocessable Entity | 驗證失敗 | 欄位驗證不通過、業務規則違反 |
| 500 Internal Server Error | 伺服器錯誤 | 程式異常、資料庫連線失敗 |

**Logic**:
```php
/**
 * @OA\Response(response=200, description="查詢成功", ...)
 * @OA\Response(response=401, description="未認證", ...)
 * @OA\Response(response=404, description="資源不存在", ...)
 */
public function show($id)
{
    // 實際回應也要對應
    if (!$this->isAuthenticated()) {
        return $this->failUnauthorized('未認證');  // 401
    }

    $data = $this->model->find($id);
    if (!$data) {
        return $this->failNotFound('資源不存在');  // 404
    }

    return $this->respond($data);  // 200
}
```

**Example**:
```
GET /api/salesperson/profile (已登入) → 200 OK
GET /api/salesperson/profile (未登入) → 401 Unauthorized
DELETE /api/admin/users/999 (ID 不存在) → 404 Not Found
POST /api/auth/register (username 已存在) → 422 Unprocessable Entity
```

**Error Handling**:
- 每個狀態碼都必須在 Swagger 註解中定義對應的回應

**Rationale**:
- 符合 RESTful API 標準
- 前端可依據狀態碼正確處理回應

---

## BR-012: Deprecation Warning

**Rule**: 若 API 即將淘汰，必須在文件中標記 `deprecated`

**Implementation**:
- Annotation: 使用 `deprecated=true`
- Description: 說明淘汰原因和替代方案

**Logic**:
```php
/**
 * @OA\Get(
 *     path="/api/v1/old-endpoint",
 *     deprecated=true,
 *     description="此端點已棄用，請改用 POST /api/v2/new-endpoint",
 *     ...
 * )
 */
public function oldEndpoint()
{
    // ...
}
```

**Example**:
```
GET /api/v1/search (deprecated) → 顯示刪除線
建議: 改用 GET /api/v2/search/salespersons
```

**Error Handling**:
- N/A (僅影響文件顯示)

**Rationale**:
- 提前通知開發者 API 變更
- 平滑過渡到新版本 API

---

## BR-013: Cache Control

**Rule**: OpenAPI JSON 回應不得快取（開發環境）

**Implementation**:
- Response header: `Cache-Control: no-cache`

**Logic**:
```php
return $this->response
    ->setContentType('application/json')
    ->setHeader('Cache-Control', 'no-cache')
    ->setJSON($openapi->toJson());
```

**Example**:
```
GET /api/docs/openapi.json

Response Headers:
Cache-Control: no-cache
Content-Type: application/json
```

**Error Handling**:
- N/A

**Rationale**:
- 開發環境修改註解後立即生效
- 避免瀏覽器快取舊版本文件

---

## Summary

### Critical Rules (Must Follow)

- ✅ **BR-001**: 生產環境完全隱藏 Swagger UI
- ✅ **BR-003**: 生成的 OpenAPI JSON 必須符合規範
- ✅ **BR-005**: 正確標記需要認證的端點
- ✅ **BR-008**: 不得洩漏敏感資訊
- ✅ **BR-011**: 使用正確的 HTTP 狀態碼

### Important Rules (Should Follow)

- ✅ **BR-006**: 註解完整性
- ✅ **BR-007**: 回應格式一致性
- ✅ **BR-009**: Tag 分類
- ✅ **BR-010**: 範例資料品質

### Nice-to-Have Rules (Can Follow)

- ✅ **BR-012**: 淘汰警告
- ✅ **BR-013**: Cache Control

---

## Validation Checklist

開發完成後，使用此檢查清單驗證業務規則實作：

- [ ] BR-001: 生產環境訪問 `/api/docs` 返回 404
- [ ] BR-002: 開發環境訪問 `/api/docs` 不需要 Token
- [ ] BR-003: 訪問 `/api/docs/openapi.json` 返回有效的 OpenAPI 3.0 JSON
- [ ] BR-004: 僅掃描 `app/Controllers/Api/` 目錄
- [ ] BR-005: Swagger UI 顯示 "Authorize" 按鈕
- [ ] BR-006: 所有 32 個 API 都有完整註解
- [ ] BR-007: 所有回應格式一致 (`status`, `message`, `data`)
- [ ] BR-008: 範例中無真實密碼或 Token
- [ ] BR-009: API 正確分類到 4 個 Tag
- [ ] BR-010: 範例資料格式正確（email, phone, date）
- [ ] BR-011: 每個端點都定義了正確的狀態碼回應
- [ ] BR-013: OpenAPI JSON 回應 Header 包含 `Cache-Control: no-cache`

---

## References

- OpenAPI 3.0 Security: https://swagger.io/docs/specification/authentication/
- HTTP Status Codes: https://developer.mozilla.org/en-US/docs/Web/HTTP/Status
- swagger-php Best Practices: https://zircote.github.io/swagger-php/guide/best-practices.html
