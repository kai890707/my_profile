# Data Model Specification: Swagger API Documentation

**Feature**: swagger-api-documentation

**Date**: 2026-01-08

**Version**: 1.0

---

## Overview

本功能不需要新增資料表，因為 Swagger API 文件是基於現有 Controller 註解動態生成的文件系統。

本文件記錄相關的**配置檔案結構**和**Controller 類別結構**。

---

## No Database Changes Required

### Reason

Swagger API Documentation 是一個文件生成功能，不需要儲存任何持久化資料：
- API 規格從 PHP 註解動態生成
- 不需要儲存文件版本歷史
- 不需要儲存用戶訪問記錄
- 不需要儲存配置選項（使用環境變數）

因此：
- ✅ 無需 Migration
- ✅ 無需 Model 類別
- ✅ 無需資料表

---

## Configuration Structure

### Environment Variables

**File**: `.env`

```ini
#--------------------------------------------------------------------
# SWAGGER CONFIGURATION
#--------------------------------------------------------------------

# 是否啟用 Swagger (開發環境: true, 生產環境: false)
SWAGGER_ENABLED=true

# OpenAPI 規格版本
SWAGGER_OPENAPI_VERSION=3.0.0

# API 基本資訊
SWAGGER_API_TITLE=業務推廣系統 API
SWAGGER_API_VERSION=1.0.0
SWAGGER_API_DESCRIPTION=業務員管理與搜尋平台的 RESTful API

# Swagger UI 配置
SWAGGER_UI_DEEP_LINKING=true
SWAGGER_UI_DISPLAY_REQUEST_DURATION=true

# 快取配置 (開發環境建議 false)
SWAGGER_CACHE_ENABLED=false
SWAGGER_CACHE_TTL=3600
```

### Field Definitions

| Field | Type | Required | Default | Description |
|-------|------|----------|---------|-------------|
| SWAGGER_ENABLED | boolean | NO | true | 是否啟用 Swagger UI |
| SWAGGER_OPENAPI_VERSION | string | NO | 3.0.0 | OpenAPI 規格版本 |
| SWAGGER_API_TITLE | string | NO | API Documentation | API 文件標題 |
| SWAGGER_API_VERSION | string | NO | 1.0.0 | API 版本號 |
| SWAGGER_API_DESCRIPTION | string | NO | - | API 說明 |
| SWAGGER_UI_DEEP_LINKING | boolean | NO | true | 是否啟用深度連結 |
| SWAGGER_UI_DISPLAY_REQUEST_DURATION | boolean | NO | true | 是否顯示請求時間 |
| SWAGGER_CACHE_ENABLED | boolean | NO | false | 是否快取 OpenAPI JSON |
| SWAGGER_CACHE_TTL | integer | NO | 3600 | 快取時間（秒） |

---

## Controller Structure

### SwaggerController Class

**File**: `app/Controllers/Api/SwaggerController.php`

```php
<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use OpenApi\Generator;

/**
 * Swagger API Documentation Controller
 *
 * 提供 Swagger UI 介面和 OpenAPI JSON 規格
 * 僅在開發環境可用
 */
class SwaggerController extends ResourceController
{
    protected $format = 'json';

    /**
     * @var string Controller 掃描路徑
     */
    protected $controllersPath;

    /**
     * @var bool 是否啟用 Swagger
     */
    protected $enabled;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->controllersPath = APPPATH . 'Controllers/Api';
        $this->enabled = env('SWAGGER_ENABLED', true) &&
                        env('CI_ENVIRONMENT') === 'development';
    }

    /**
     * GET /api/docs
     *
     * 顯示 Swagger UI 介面
     *
     * @return Response HTML 頁面或 404
     */
    public function index()
    {
        // 檢查環境
        if (!$this->enabled) {
            return $this->failNotFound('Not Found');
        }

        // 返回 Swagger UI HTML
        return $this->response
            ->setContentType('text/html')
            ->setBody($this->getSwaggerUI());
    }

    /**
     * GET /api/docs/openapi.json
     *
     * 返回 OpenAPI 3.0 JSON 規格
     *
     * @return Response JSON 規格或 404
     */
    public function json()
    {
        // 檢查環境
        if (!$this->enabled) {
            return $this->failNotFound('Not Found');
        }

        try {
            // 掃描 Controllers 目錄生成 OpenAPI JSON
            $openapi = Generator::scan([$this->controllersPath]);

            return $this->response
                ->setContentType('application/json')
                ->setHeader('Cache-Control', 'no-cache')
                ->setJSON($openapi->toJson());

        } catch (\Exception $e) {
            log_message('error', 'Swagger generation failed: ' . $e->getMessage());

            return $this->failServerError(
                'Failed to generate OpenAPI specification',
                500,
                ['scan_error' => $e->getMessage()]
            );
        }
    }

    /**
     * 生成 Swagger UI HTML
     *
     * @return string HTML 內容
     */
    protected function getSwaggerUI(): string
    {
        $apiTitle = env('SWAGGER_API_TITLE', 'API Documentation');
        $deepLinking = env('SWAGGER_UI_DEEP_LINKING', true) ? 'true' : 'false';
        $displayRequestDuration = env('SWAGGER_UI_DISPLAY_REQUEST_DURATION', true) ? 'true' : 'false';

        return <<<HTML
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$apiTitle}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5/swagger-ui.css">
    <style>
        body {
            margin: 0;
            padding: 0;
        }
    </style>
</head>
<body>
    <div id="swagger-ui"></div>
    <script src="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5/swagger-ui-bundle.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5/swagger-ui-standalone-preset.js"></script>
    <script>
        window.onload = function() {
            window.ui = SwaggerUIBundle({
                url: "/api/docs/openapi.json",
                dom_id: '#swagger-ui',
                deepLinking: {$deepLinking},
                displayRequestDuration: {$displayRequestDuration},
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIStandalonePreset
                ],
                layout: "StandaloneLayout",
                persistAuthorization: true
            });
        };
    </script>
</body>
</html>
HTML;
    }
}
```

### Class Properties

| Property | Type | Description |
|----------|------|-------------|
| $format | string | 回應格式（預設 json） |
| $controllersPath | string | Controller 掃描路徑 |
| $enabled | bool | Swagger 是否啟用 |

### Class Methods

| Method | Visibility | Return Type | Description |
|--------|-----------|-------------|-------------|
| __construct() | public | void | 初始化 Controller，設定掃描路徑 |
| index() | public | Response | 顯示 Swagger UI HTML |
| json() | public | Response | 返回 OpenAPI JSON |
| getSwaggerUI() | protected | string | 生成 Swagger UI HTML 內容 |

---

## OpenAPI Base Information Object

生成的 OpenAPI JSON 包含以下基本資訊結構：

```json
{
    "openapi": "3.0.0",
    "info": {
        "title": "業務推廣系統 API",
        "description": "業務員管理與搜尋平台的 RESTful API",
        "version": "1.0.0",
        "contact": {
            "name": "API Support",
            "email": "support@example.com"
        },
        "license": {
            "name": "Proprietary"
        }
    },
    "servers": [
        {
            "url": "http://localhost:8080",
            "description": "開發環境"
        },
        {
            "url": "http://localhost:8080",
            "description": "測試環境"
        }
    ],
    "tags": [
        {
            "name": "認證",
            "description": "使用者認證與授權相關 API"
        },
        {
            "name": "搜尋",
            "description": "業務員搜尋相關 API"
        },
        {
            "name": "業務員",
            "description": "業務員個人檔案管理 API"
        },
        {
            "name": "管理員",
            "description": "管理員後台管理 API"
        }
    ]
}
```

---

## Annotation Structure Example

### Controller Class Level Annotation

每個 Controller 類別開頭需要加入 OpenAPI 註解：

```php
/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="業務推廣系統 API",
 *     description="業務員管理與搜尋平台的 RESTful API"
 * )
 *
 * @OA\Server(
 *     url="http://localhost:8080",
 *     description="開發環境"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="輸入 JWT Token（不需要加 'Bearer ' 前綴）"
 * )
 *
 * @OA\Tag(
 *     name="認證",
 *     description="使用者認證與授權相關 API"
 * )
 */
class AuthController extends ResourceController
{
    // ...
}
```

### Method Level Annotation

每個 API 方法需要加入完整註解：

```php
/**
 * @OA\Post(
 *     path="/api/auth/register",
 *     tags={"認證"},
 *     summary="業務員註冊",
 *     description="建立新的業務員帳號並自動建立個人檔案",
 *     @OA\RequestBody(
 *         required=true,
 *         description="註冊資料",
 *         @OA\JsonContent(
 *             required={"username", "email", "password", "full_name"},
 *             @OA\Property(property="username", type="string", minLength=3, maxLength=50, example="john_doe"),
 *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
 *             @OA\Property(property="password", type="string", format="password", minLength=8, example="SecurePass123"),
 *             @OA\Property(property="full_name", type="string", minLength=2, maxLength=100, example="王小明"),
 *             @OA\Property(property="phone", type="string", pattern="^09\d{8}$", example="0912345678")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="註冊成功",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(property="message", type="string", example="註冊成功"),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="user", ref="#/components/schemas/User"),
 *                 @OA\Property(property="access_token", type="string", example="eyJ0eXAiOiJKV1Qi..."),
 *                 @OA\Property(property="refresh_token", type="string", example="eyJ0eXAiOiJKV1Qi..."),
 *                 @OA\Property(property="expires_in", type="integer", example=3600)
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="驗證失敗",
 *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
 *     )
 * )
 */
public function register()
{
    // Implementation...
}
```

---

## Schema Definitions

常用的 Schema 定義（需在一個 Controller 中定義一次）：

```php
/**
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     title="使用者物件",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="username", type="string", example="john_doe"),
 *     @OA\Property(property="email", type="string", example="john@example.com"),
 *     @OA\Property(property="full_name", type="string", example="王小明"),
 *     @OA\Property(property="role", type="string", enum={"admin", "salesperson", "user"}, example="salesperson"),
 *     @OA\Property(property="status", type="string", enum={"active", "inactive", "pending"}, example="active"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2026-01-08T10:30:00Z")
 * )
 *
 * @OA\Schema(
 *     schema="ValidationError",
 *     type="object",
 *     title="驗證錯誤回應",
 *     @OA\Property(property="status", type="string", example="error"),
 *     @OA\Property(property="message", type="string", example="驗證失敗"),
 *     @OA\Property(property="errors", type="object",
 *         @OA\AdditionalProperties(
 *             type="array",
 *             @OA\Items(type="string")
 *         ),
 *         example={"username": {"username 已被使用"}, "email": {"email 格式不正確"}}
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="ErrorResponse",
 *     type="object",
 *     title="一般錯誤回應",
 *     @OA\Property(property="status", type="string", example="error"),
 *     @OA\Property(property="message", type="string", example="錯誤訊息")
 * )
 */
```

---

## Directory Structure

```
app/
├── Controllers/
│   └── Api/
│       ├── AuthController.php         # 含 OpenAPI 註解
│       ├── SearchController.php       # 含 OpenAPI 註解
│       ├── SalespersonController.php  # 含 OpenAPI 註解
│       ├── AdminController.php        # 含 OpenAPI 註解
│       └── SwaggerController.php      # 新增（Swagger UI Controller）
└── Config/
    └── Routes.php                     # 新增 /api/docs 路由

.env                                   # 新增 Swagger 配置
composer.json                          # 新增 swagger-php 依賴
```

---

## Composer Dependencies

**File**: `composer.json`

```json
{
    "require": {
        "php": "^8.1",
        "codeigniter4/framework": "^4.6",
        "firebase/php-jwt": "^6.10",
        "zircote/swagger-php": "^4.0"
    }
}
```

安裝指令：
```bash
composer require zircote/swagger-php:^4.0
```

---

## Cache Structure (Optional)

如果啟用快取（`SWAGGER_CACHE_ENABLED=true`），OpenAPI JSON 會快取到：

```
writable/
└── cache/
    └── swagger/
        └── openapi.json    # 快取的 OpenAPI 規格
```

快取邏輯：
- 檢查快取是否存在且未過期
- 存在且有效 → 直接返回快取
- 不存在或過期 → 重新掃描生成並更新快取

---

## Testing Structure

測試時不需要真實資料庫資料，使用範例資料即可：

```php
// 範例：測試 Swagger UI 可訪問
public function testSwaggerUIAccessible()
{
    $result = $this->call('GET', '/api/docs');

    $this->assertResponseCode(200);
    $this->assertStringContainsString('swagger-ui', $result->getBody());
}

// 範例：測試 OpenAPI JSON 生成
public function testOpenAPIJsonGeneration()
{
    $result = $this->call('GET', '/api/docs/openapi.json');

    $this->assertResponseCode(200);

    $json = json_decode($result->getBody(), true);
    $this->assertEquals('3.0.0', $json['openapi']);
    $this->assertArrayHasKey('paths', $json);
}

// 範例：測試生產環境隱藏
public function testSwaggerHiddenInProduction()
{
    putenv('CI_ENVIRONMENT=production');

    $result = $this->call('GET', '/api/docs');
    $this->assertResponseCode(404);

    putenv('CI_ENVIRONMENT=development');
}
```

---

## Summary

- ✅ 不需要新增資料表或 Model
- ✅ 僅需新增 SwaggerController 和環境配置
- ✅ 透過 PHP 註解動態生成 OpenAPI 規格
- ✅ 所有文件資料來自 Controller 註解掃描
- ✅ 可選快取機制提升效能

---

## References

- swagger-php Annotations: https://zircote.github.io/swagger-php/reference/annotations.html
- OpenAPI 3.0 Components: https://swagger.io/specification/#components-object
- CodeIgniter 4 Controllers: https://codeigniter.com/user_guide/incoming/controllers.html
