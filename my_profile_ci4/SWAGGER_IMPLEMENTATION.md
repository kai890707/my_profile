# Swagger API 文件實作摘要

## 專案資訊

- **專案名稱**: 業務推廣系統 API
- **版本**: 1.0.0
- **OpenAPI 版本**: 3.0.0
- **實作日期**: 2026-01-08
- **開發框架**: CodeIgniter 4.6.4
- **PHP 版本**: 8.2+

## 實作概述

本專案已完成 Swagger/OpenAPI 3.0 API 文件的完整實作,使用 PHP 8 Attributes 語法和 swagger-php 4.x 套件,為所有 RESTful API 端點提供互動式文件介面。

## 實作成果

### API 統計

- **總端點數**: 31 個 API 操作
- **路徑數**: 26 個唯一路徑
- **標籤分組**: 4 個功能模組
- **認證方式**: JWT Bearer Token

### 控制器覆蓋

#### 1. AuthController (認證) - 5 APIs
- `POST /api/auth/register` - 業務員註冊
- `POST /api/auth/login` - 使用者登入
- `POST /api/auth/refresh` - 刷新 Access Token
- `POST /api/auth/logout` - 使用者登出
- `GET /api/auth/me` - 取得當前使用者資訊

#### 2. SearchController (搜尋) - 2 APIs
- `GET /api/search/salespersons` - 搜尋業務員(支援關鍵字、公司、產業、地區篩選)
- `GET /api/search/salespersons/{id}` - 查看業務員詳細資料

#### 3. SalespersonController (業務員) - 9 APIs
- `GET /api/salesperson/profile` - 取得個人檔案
- `PUT /api/salesperson/profile` - 更新個人檔案
- `POST /api/salesperson/company` - 儲存公司資訊
- `GET /api/salesperson/experiences` - 取得工作經驗清單
- `POST /api/salesperson/experiences` - 新增工作經驗
- `DELETE /api/salesperson/experiences/{id}` - 刪除工作經驗
- `GET /api/salesperson/certifications` - 取得證照清單
- `POST /api/salesperson/certifications` - 上傳證照
- `GET /api/salesperson/approval-status` - 查詢審核狀態

#### 4. AdminController (管理員) - 15 APIs
**審核管理 (7 APIs)**:
- `GET /api/admin/pending-approvals` - 取得待審核項目
- `POST /api/admin/approve-user/{id}` - 審核通過業務員註冊
- `POST /api/admin/reject-user/{id}` - 拒絕業務員註冊
- `POST /api/admin/approve-company/{id}` - 審核通過公司資訊
- `POST /api/admin/reject-company/{id}` - 拒絕公司資訊
- `POST /api/admin/approve-certification/{id}` - 審核通過證照
- `POST /api/admin/reject-certification/{id}` - 拒絕證照

**使用者管理 (3 APIs)**:
- `GET /api/admin/users` - 取得使用者清單(支援角色和狀態篩選)
- `PUT /api/admin/users/{id}/status` - 更新使用者狀態
- `DELETE /api/admin/users/{id}` - 刪除使用者

**系統設定 (4 APIs)**:
- `GET /api/admin/settings/industries` - 取得產業類別清單
- `POST /api/admin/settings/industries` - 新增產業類別
- `GET /api/admin/settings/regions` - 取得地區清單
- `POST /api/admin/settings/regions` - 新增地區

**統計資訊 (1 API)**:
- `GET /api/admin/statistics` - 取得統計資料

## 技術實作細節

### 1. 套件安裝

```bash
composer require zircote/swagger-php:^4.0
```

### 2. 核心檔案

#### SwaggerController.php
```php
<?php
namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use OpenApi\Generator;

class SwaggerController extends ResourceController
{
    // 掃描 Controllers 目錄生成 OpenAPI JSON
    // 提供 Swagger UI 介面
    // 環境檢查:僅在開發環境可用
}
```

**路由配置** (`app/Config/Routes.php`):
```php
$routes->group('api/docs', function($routes) {
    $routes->get('/', 'Api\SwaggerController::index');
    $routes->get('openapi.json', 'Api\SwaggerController::json');
});
```

### 3. 註解語法

使用 **PHP 8 Attributes** 語法(swagger-php 4.x 要求):

```php
use OpenApi\Attributes as OA;

#[OA\Get(
    path: "/api/resource",
    tags: ["標籤"],
    summary: "摘要說明",
    security: [["bearerAuth" => []]],
    parameters: [
        new OA\Parameter(
            name: "id",
            in: "path",
            required: true,
            schema: new OA\Schema(type: "integer")
        )
    ],
    responses: [
        new OA\Response(response: 200, description: "成功"),
        new OA\Response(response: 401, description: "未認證")
    ]
)]
public function method() { }
```

### 4. 全域配置註解

位於 `AuthController.php`:

```php
#[OA\Info(
    title: "業務推廣系統 API",
    version: "1.0.0",
    description: "業務員管理與搜尋平台的 RESTful API"
)]
#[OA\Server(
    url: "http://localhost:8080",
    description: "開發環境"
)]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    scheme: "bearer",
    bearerFormat: "JWT",
    description: "輸入 JWT Token（不需要加 'Bearer ' 前綴）"
)]
```

### 5. 環境配置

`.env` 檔案設定:

```ini
#--------------------------------------------------------------------
# SWAGGER CONFIGURATION
#--------------------------------------------------------------------
SWAGGER_ENABLED = true
SWAGGER_OPENAPI_VERSION = 3.0.0
SWAGGER_API_TITLE = "業務推廣系統 API"
SWAGGER_API_VERSION = "1.0.0"
SWAGGER_API_DESCRIPTION = "業務員管理與搜尋平台的 RESTful API"
SWAGGER_UI_DEEP_LINKING = true
SWAGGER_UI_DISPLAY_REQUEST_DURATION = true
SWAGGER_CACHE_ENABLED = false
SWAGGER_CACHE_TTL = 3600
```

## 環境管理

### 開發環境
- **Swagger UI**: http://localhost:8080/api/docs
- **OpenAPI JSON**: http://localhost:8080/api/docs/openapi.json
- **狀態**: 完全可見且可互動

### 生產環境
- **Swagger UI**: 404 Not Found
- **OpenAPI JSON**: 404 Not Found
- **安全性**: 自動隱藏,防止 API 結構洩露

**切換方式**:
```bash
# docker-compose.yml
environment:
  - CI_ENVIRONMENT=production  # 或 development
```

## 使用說明

### 1. 開啟 Swagger UI

瀏覽器訪問: `http://localhost:8080/api/docs`

### 2. 測試需要認證的端點

1. 使用 `/api/auth/login` 登入取得 Token
2. 點擊右上角 "Authorize" 按鈕
3. 輸入 JWT Token(不需要 "Bearer " 前綴)
4. 點擊 "Authorize" 確認
5. 現在可以測試所有需要認證的端點

### 3. 測試流程範例

```
1. POST /api/auth/register - 註冊新業務員
2. 等待管理員審核通過
3. POST /api/auth/login - 登入取得 Token
4. GET /api/auth/me - 確認身份
5. PUT /api/salesperson/profile - 更新個人資料
6. GET /api/salesperson/approval-status - 查詢審核狀態
```

## 關鍵技術決策

### 1. 使用 PHP 8 Attributes
**原因**: swagger-php 4.x 在 PHP 8.2 環境下要求使用 Attributes 語法,而非舊版 PHPDoc 註解。

**影響**: 語法更簡潔,IDE 支援更好,類型檢查更嚴格。

### 2. 環境檢查機制
```php
$this->enabled = env('SWAGGER_ENABLED', true) &&
                 env('CI_ENVIRONMENT') === 'development';
```

**原因**: 防止生產環境暴露 API 結構,增強安全性。

### 3. 集中式全域配置
將 `@OA\Info` 和 `@OA\SecurityScheme` 放在 AuthController,避免多個檔案重複定義。

## 驗證測試

### 開發環境測試
```bash
# 測試 Swagger UI
curl -I http://localhost:8080/api/docs
# 預期: HTTP/1.1 200 OK

# 測試 OpenAPI JSON
curl http://localhost:8080/api/docs/openapi.json | jq '.paths | keys | length'
# 預期: 26
```

### 生產環境測試
```bash
# 1. 修改 docker-compose.yml: CI_ENVIRONMENT=production
# 2. 重新建立容器
docker-compose stop app && docker-compose rm -f app && docker-compose up -d app

# 3. 測試 Swagger UI
curl -I http://localhost:8080/api/docs
# 預期: HTTP/1.1 404 Not Found

# 4. 還原開發環境
# 修改 docker-compose.yml: CI_ENVIRONMENT=development
docker-compose stop app && docker-compose rm -f app && docker-compose up -d app
```

## 問題排查

### 問題 1: 掃描失敗 "Required @OA\PathItem() not found"
**原因**: 使用 PHPDoc 註解語法而非 PHP 8 Attributes

**解決方案**:
```php
// 錯誤 ❌
/** @OA\Get(...) */

// 正確 ✅
#[OA\Get(...)]
```

### 問題 2: Multiple @OA\Info() found
**原因**: 多個 Controller 包含全域配置註解

**解決方案**: 僅在一個 Controller(建議 AuthController)定義全域註解

### 問題 3: 生產環境仍可見 Swagger
**原因**: Docker 容器環境變數未更新

**解決方案**:
```bash
# 修改 docker-compose.yml 後必須重新建立容器
docker-compose stop app
docker-compose rm -f app
docker-compose up -d app
```

## 後續優化建議

### 1. 新增可複用 Schema 組件
```php
#[OA\Schema(
    schema: "User",
    properties: [
        new OA\Property(property: "id", type: "integer"),
        new OA\Property(property: "username", type: "string"),
        new OA\Property(property: "email", type: "string")
    ]
)]
```

### 2. 新增請求/回應範例
```php
#[OA\RequestBody(
    required: true,
    content: new OA\JsonContent(
        example: ["username" => "john_doe", "password" => "secret123"]
    )
)]
```

### 3. 新增 API 版本控制
```php
#[OA\Server(url: "http://localhost:8080/api/v1", description: "API Version 1")]
#[OA\Server(url: "http://localhost:8080/api/v2", description: "API Version 2")]
```

### 4. 整合 CI/CD 自動驗證
```bash
# 在 CI pipeline 中驗證 OpenAPI spec
vendor/bin/openapi --validate app/Controllers/Api
```

## 維護指南

### 新增 API 端點時

1. 在方法上方添加 `#[OA\...]` 註解
2. 指定正確的 HTTP 方法、路徑、標籤
3. 添加所有參數和回應碼說明
4. 需要認證的端點添加 `security: [["bearerAuth" => []]]`

### 修改 API 時

1. 更新對應的註解說明
2. 測試 Swagger UI 是否正確顯示
3. 檢查 OpenAPI JSON 是否正確生成

### 部署到生產環境前

1. 確認 `CI_ENVIRONMENT=production`
2. 驗證 Swagger 已被隱藏(404)
3. 確認 API 功能正常運作

## 相關資源

- **Swagger UI**: http://localhost:8080/api/docs
- **OpenAPI Spec**: http://localhost:8080/api/docs/openapi.json
- **swagger-php 文件**: https://github.com/zircote/swagger-php
- **OpenAPI 3.0 規範**: https://swagger.io/specification/

## 完成檢查清單

- [x] 安裝 swagger-php 套件
- [x] 建立 SwaggerController
- [x] 配置路由
- [x] 新增 .env 設定
- [x] 為 AuthController 添加註解(5 APIs)
- [x] 為 SearchController 添加註解(2 APIs)
- [x] 為 SalespersonController 添加註解(9 APIs)
- [x] 為 AdminController 添加註解(15 APIs)
- [x] 測試 Swagger UI 可訪問
- [x] 測試 OpenAPI JSON 生成正確
- [x] 測試生產環境隱藏功能
- [x] 驗證 JWT 認證機制
- [x] 建立實作文件

## 結論

本專案已完成完整的 Swagger/OpenAPI 3.0 API 文件實作,涵蓋所有 31 個 API 端點,提供互動式測試介面,並具備環境檢查機制確保生產環境安全。所有 API 均使用 JWT Bearer Token 認證,並按功能模組分為 4 個標籤群組,便於開發和測試。
