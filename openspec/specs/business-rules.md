# Business Rules Specification

**Project**: 業務推廣系統
**Last Updated**: 2026-01-08

本文件記錄系統所有業務規則，包括驗證邏輯、約束條件、權限控制等。

---

## Feature: Swagger API Documentation

**Added**: 2026-01-08
**Change**: swagger-api-documentation

### BR-001: Environment-Based Visibility

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
```

**Error Handling**:
- HTTP Status: 404 Not Found
- Error Message: "Not Found"

**Rationale**: 防止生產環境暴露 API 內部結構和實作細節

---

### BR-002: No Authentication Required for Swagger UI

**Rule**: 訪問 Swagger UI 本身不需要 JWT 認證

**Implementation**:
- Route configuration: `/api/docs` 不加 `auth` filter
- Public access in development environment

**Rationale**:
- 方便開發者快速查看 API 文件
- 實際 API 測試時仍需提供有效 Token

---

### BR-003: OpenAPI Specification Validation

**Rule**: 生成的 OpenAPI JSON 必須符合 OpenAPI 3.0.0 規範

**Implementation**:
- Application validation: 使用 `zircote/swagger-php` 庫自動驗證
- 掃描時發生錯誤返回 500 錯誤

**Error Handling**:
- HTTP Status: 500 Internal Server Error
- Error Message: "Failed to generate OpenAPI specification"
- Logging: 記錄錯誤到 `writable/logs/log-{date}.log`

**Rationale**: 確保生成的文件符合標準，可被 Swagger UI 正確解析

---

### BR-004: Controller Scanning

**Rule**: 僅掃描 `app/Controllers/Api/` 目錄下的 Controller

**Implementation**:
- Application logic: 限定掃描路徑
- 排除其他目錄（如 `app/Controllers/Home.php`）

**Rationale**:
- 僅記錄 API 端點，不包含網頁 Controller
- 避免掃描不必要的檔案

---

### BR-005: JWT Security Scheme Definition

**Rule**: 所有需要認證的 API 必須標記 `bearerAuth` Security

**Implementation**:
- Annotation requirement: 在 OpenAPI 註解中定義 `@OA\SecurityScheme`
- 在需要認證的端點加上 `security={{"bearerAuth":{}}}`

**Example**:
```
公開端點 (無需認證):
POST /api/auth/register → 無 security 標記

需要認證的端點:
GET /api/auth/me → security={{"bearerAuth":{}}}
GET /api/salesperson/profile → security={{"bearerAuth":{}}}
```

**Rationale**:
- 清楚標示哪些 API 需要認證
- 在 Swagger UI 中顯示鎖頭圖示
- 方便測試時輸入 Token

---

### BR-006: Annotation Completeness

**Rule**: 每個 API 端點必須包含完整的 Swagger 註解

**Required Elements**:
- `path`: API 路徑
- `tags`: 所屬分類
- `summary`: 簡短說明
- `requestBody`: 請求內容（POST/PUT/PATCH）
- `responses`: 至少包含成功回應和錯誤回應

**Rationale**: 確保 API 文件品質，提供完整資訊給使用者

---

### BR-007: Response Schema Consistency

**Rule**: 所有 API 的回應格式必須遵循統一的 Schema 結構

**Standard Success Response**:
```json
{
    "status": "success",
    "message": "操作成功訊息",
    "data": { /* 實際資料 */ }
}
```

**Standard Error Response**:
```json
{
    "status": "error",
    "message": "錯誤訊息"
}
```

**Rationale**:
- 統一的回應格式便於前端處理
- 文件一致性提升可讀性

---

### BR-008: No Sensitive Information in Documentation

**Rule**: Swagger 註解和範例不得包含敏感資訊

**Forbidden Information**:
- ❌ 真實的資料庫連線字串
- ❌ 真實的 API 金鑰或密鑰
- ❌ 真實的使用者密碼
- ❌ 真實的 JWT Token
- ❌ 真實的使用者個資

**Allowed Examples**:
- ✅ 假資料：`example="john@example.com"`
- ✅ 範例密碼：`example="SecurePass123"`
- ✅ 範例 Token：`example="eyJ0eXAiOiJKV1Qi..."`

**Rationale**: 防止透過文件洩漏系統敏感資訊

---

### BR-009: HTTP Status Code Standards

**Rule**: API 回應必須使用正確的 HTTP 狀態碼

**Standard Status Codes**:
- 200 OK - 成功 (GET, PUT, DELETE)
- 201 Created - 建立成功 (POST)
- 400 Bad Request - 請求格式錯誤
- 401 Unauthorized - 未認證
- 403 Forbidden - 權限不足
- 404 Not Found - 資源不存在
- 422 Unprocessable Entity - 驗證失敗
- 500 Internal Server Error - 伺服器錯誤

**Rationale**:
- 符合 RESTful API 標準
- 前端可依據狀態碼正確處理回應

---

### BR-010: Cache Control

**Rule**: OpenAPI JSON 回應不得快取（開發環境）

**Implementation**:
- Response header: `Cache-Control: no-cache`

**Rationale**:
- 開發環境修改註解後立即生效
- 避免瀏覽器快取舊版本文件

---
