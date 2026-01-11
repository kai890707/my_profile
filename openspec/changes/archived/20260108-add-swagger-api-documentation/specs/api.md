# API Specification: Swagger API Documentation

**Feature**: swagger-api-documentation

**Date**: 2026-01-08

**Version**: 1.0

---

## Overview

本規格定義 Swagger API 文件系統的兩個端點：
1. Swagger UI 介面端點
2. OpenAPI JSON 規格端點

這些端點僅在開發環境可見，生產環境自動隱藏。

---

## Endpoints

### 1. GET /api/docs

**Description**: 顯示 Swagger UI 互動式 API 文件介面

**Authentication**: Not required

**Authorization**: Public (僅開發環境)

**Request Parameters**: None

**Request Headers**:
- None required

**Response (200 OK)**: HTML 頁面
```html
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>API Documentation - 業務推廣系統</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5/swagger-ui.css">
</head>
<body>
    <div id="swagger-ui"></div>
    <script src="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5/swagger-ui-bundle.js"></script>
    <script>
        window.onload = function() {
            SwaggerUIBundle({
                url: "/api/docs/openapi.json",
                dom_id: '#swagger-ui',
                deepLinking: true,
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIBundle.SwaggerUIStandalonePreset
                ],
                layout: "BaseLayout"
            })
        }
    </script>
</body>
</html>
```

**Response Headers**:
```
Content-Type: text/html; charset=UTF-8
```

**Error Responses**:

**404 Not Found** (生產環境):
```json
{
    "status": "error",
    "message": "Not Found"
}
```

**Business Rules**:
- BR-001: Environment-Based Visibility
- BR-002: No Authentication Required

**Example**:
```bash
# 開發環境 - 成功
curl -X GET http://localhost:8080/api/docs

# 返回: HTML 頁面（Swagger UI）

# 生產環境 - 失敗
curl -X GET https://production.example.com/api/docs

# 返回: 404 Not Found
```

---

### 2. GET /api/docs/openapi.json

**Description**: 返回 OpenAPI 3.0 規格 JSON，描述所有 API 端點

**Authentication**: Not required

**Authorization**: Public (僅開發環境)

**Request Parameters**: None

**Request Headers**:
- None required

**Response (200 OK)**:
```json
{
    "openapi": "3.0.0",
    "info": {
        "title": "業務推廣系統 API",
        "description": "業務員管理與搜尋平台的 RESTful API",
        "version": "1.0.0",
        "contact": {
            "name": "API Support"
        }
    },
    "servers": [
        {
            "url": "http://localhost:8080",
            "description": "開發環境"
        }
    ],
    "paths": {
        "/api/auth/register": {
            "post": {
                "tags": ["認證"],
                "summary": "業務員註冊",
                "description": "建立新的業務員帳號並自動建立個人檔案",
                "requestBody": {
                    "required": true,
                    "content": {
                        "application/json": {
                            "schema": {
                                "$ref": "#/components/schemas/RegisterRequest"
                            }
                        }
                    }
                },
                "responses": {
                    "201": {
                        "description": "註冊成功",
                        "content": {
                            "application/json": {
                                "schema": {
                                    "$ref": "#/components/schemas/AuthResponse"
                                }
                            }
                        }
                    },
                    "422": {
                        "description": "驗證失敗",
                        "content": {
                            "application/json": {
                                "schema": {
                                    "$ref": "#/components/schemas/ValidationError"
                                }
                            }
                        }
                    }
                }
            }
        }
        // ... 其他 31 個端點（完整定義在實作時補充）
    },
    "components": {
        "securitySchemes": {
            "bearerAuth": {
                "type": "http",
                "scheme": "bearer",
                "bearerFormat": "JWT",
                "description": "輸入 JWT Token（不需要加 'Bearer ' 前綴）"
            }
        },
        "schemas": {
            "RegisterRequest": {
                "type": "object",
                "required": ["username", "email", "password", "full_name"],
                "properties": {
                    "username": {
                        "type": "string",
                        "minLength": 3,
                        "maxLength": 50,
                        "example": "john_doe"
                    },
                    "email": {
                        "type": "string",
                        "format": "email",
                        "example": "john@example.com"
                    },
                    "password": {
                        "type": "string",
                        "format": "password",
                        "minLength": 8,
                        "example": "SecurePass123"
                    },
                    "full_name": {
                        "type": "string",
                        "minLength": 2,
                        "maxLength": 100,
                        "example": "王小明"
                    },
                    "phone": {
                        "type": "string",
                        "pattern": "^09\\d{8}$",
                        "example": "0912345678"
                    }
                }
            },
            "AuthResponse": {
                "type": "object",
                "properties": {
                    "status": {
                        "type": "string",
                        "example": "success"
                    },
                    "message": {
                        "type": "string",
                        "example": "註冊成功"
                    },
                    "data": {
                        "type": "object",
                        "properties": {
                            "user": {
                                "$ref": "#/components/schemas/User"
                            },
                            "access_token": {
                                "type": "string",
                                "example": "eyJ0eXAiOiJKV1QiLCJhbGc..."
                            },
                            "refresh_token": {
                                "type": "string",
                                "example": "eyJ0eXAiOiJKV1QiLCJhbGc..."
                            },
                            "expires_in": {
                                "type": "integer",
                                "example": 3600
                            }
                        }
                    }
                }
            },
            "User": {
                "type": "object",
                "properties": {
                    "id": {
                        "type": "integer",
                        "example": 1
                    },
                    "username": {
                        "type": "string",
                        "example": "john_doe"
                    },
                    "email": {
                        "type": "string",
                        "example": "john@example.com"
                    },
                    "full_name": {
                        "type": "string",
                        "example": "王小明"
                    },
                    "role": {
                        "type": "string",
                        "enum": ["admin", "salesperson", "user"],
                        "example": "salesperson"
                    }
                }
            },
            "ValidationError": {
                "type": "object",
                "properties": {
                    "status": {
                        "type": "string",
                        "example": "error"
                    },
                    "message": {
                        "type": "string",
                        "example": "驗證失敗"
                    },
                    "errors": {
                        "type": "object",
                        "additionalProperties": {
                            "type": "array",
                            "items": {
                                "type": "string"
                            }
                        },
                        "example": {
                            "username": ["username 已被使用"],
                            "email": ["email 格式不正確"]
                        }
                    }
                }
            },
            "ErrorResponse": {
                "type": "object",
                "properties": {
                    "status": {
                        "type": "string",
                        "example": "error"
                    },
                    "message": {
                        "type": "string",
                        "example": "錯誤訊息"
                    }
                }
            }
        }
    },
    "security": []
}
```

**Response Headers**:
```
Content-Type: application/json; charset=UTF-8
Cache-Control: no-cache
```

**Error Responses**:

**404 Not Found** (生產環境):
```json
{
    "status": "error",
    "message": "Not Found"
}
```

**500 Internal Server Error** (註解掃描失敗):
```json
{
    "status": "error",
    "message": "Failed to generate OpenAPI specification",
    "errors": {
        "scan_error": "Unable to scan controllers directory"
    }
}
```

**Business Rules**:
- BR-001: Environment-Based Visibility
- BR-003: OpenAPI Specification Validation
- BR-004: Controller Scanning

**Example**:
```bash
# 開發環境 - 成功
curl -X GET http://localhost:8080/api/docs/openapi.json

# 返回: OpenAPI JSON 規格

# 測試 Swagger UI 是否正確載入規格
curl -X GET http://localhost:8080/api/docs \
  -H "Accept: text/html" | grep "swagger-ui"

# 應該看到 HTML 包含 swagger-ui 元素
```

---

## Common Response Schemas

### Success Response (Generic)
```json
{
    "status": "success",
    "message": "操作成功訊息",
    "data": {
        // 回應資料（依端點而異）
    }
}
```

### Error Response (Generic)
```json
{
    "status": "error",
    "message": "錯誤訊息",
    "errors": {
        // 錯誤詳情（可選）
    }
}
```

### Validation Error (422)
```json
{
    "status": "error",
    "message": "驗證失敗",
    "errors": {
        "field_name": [
            "錯誤訊息 1",
            "錯誤訊息 2"
        ]
    }
}
```

### Authentication Error (401)
```json
{
    "status": "error",
    "message": "未認證或 Token 無效"
}
```

### Authorization Error (403)
```json
{
    "status": "error",
    "message": "權限不足"
}
```

### Not Found Error (404)
```json
{
    "status": "error",
    "message": "資源不存在"
}
```

---

## OpenAPI Annotations Coverage

本功能需要為以下 32 個現有 API 端點加上 Swagger 註解：

### 認證模組 (AuthController) - 5 endpoints
1. POST /api/auth/register
2. POST /api/auth/login
3. POST /api/auth/refresh
4. POST /api/auth/logout
5. GET /api/auth/me

### 搜尋模組 (SearchController) - 2 endpoints
6. GET /api/search/salespersons
7. GET /api/search/salespersons/{id}

### 業務員模組 (SalespersonController) - 9 endpoints
8. GET /api/salesperson/profile
9. PUT /api/salesperson/profile
10. POST /api/salesperson/company
11. GET /api/salesperson/experiences
12. POST /api/salesperson/experiences
13. DELETE /api/salesperson/experiences/{id}
14. GET /api/salesperson/certifications
15. POST /api/salesperson/certifications
16. GET /api/salesperson/approval-status

### 管理員模組 (AdminController) - 16 endpoints
17. GET /api/admin/pending-approvals
18. POST /api/admin/approve-user/{id}
19. POST /api/admin/reject-user/{id}
20. POST /api/admin/approve-company/{id}
21. POST /api/admin/reject-company/{id}
22. POST /api/admin/approve-certification/{id}
23. POST /api/admin/reject-certification/{id}
24. GET /api/admin/users
25. PUT /api/admin/users/{id}/status
26. DELETE /api/admin/users/{id}
27. GET /api/admin/settings/industries
28. POST /api/admin/settings/industries
29. GET /api/admin/settings/regions
30. POST /api/admin/settings/regions
31. GET /api/admin/statistics

### Swagger 系統端點 - 2 endpoints (new)
32. GET /api/docs
33. GET /api/docs/openapi.json

**總計**: 33 個端點（31 現有 + 2 新增）

---

## Integration Requirements

### Composer Dependencies

```json
{
    "require": {
        "zircote/swagger-php": "^4.0"
    }
}
```

### Configuration

**Environment Variables** (`.env`):
```ini
# Swagger Configuration
SWAGGER_ENABLED=true          # 開發環境設為 true，生產環境設為 false
SWAGGER_CACHE_ENABLED=false   # 是否快取 OpenAPI JSON
```

### Routes Configuration

**File**: `app/Config/Routes.php`

新增路由：
```php
// Swagger API Documentation (僅開發環境)
$routes->group('api/docs', function($routes) {
    $routes->get('/', 'Api\SwaggerController::index');
    $routes->get('openapi.json', 'Api\SwaggerController::json');
});
```

---

## Security Considerations

### Environment Protection

- ✅ 生產環境完全隱藏 Swagger UI（返回 404）
- ✅ 不暴露敏感資訊（資料庫連線、密鑰）
- ✅ 範例資料使用假資料，不包含真實用戶資訊

### JWT Token Handling

- ✅ Swagger UI 支援 Bearer Token 輸入
- ✅ Token 僅存於瀏覽器記憶體，不寫入 localStorage
- ⚠️ 開發環境仍需注意不要使用生產環境 Token

---

## Testing Scenarios

### Scenario 1: 開發環境訪問 Swagger UI

```bash
# 前置條件: CI_ENVIRONMENT=development
curl -I http://localhost:8080/api/docs

# 預期結果:
# HTTP/1.1 200 OK
# Content-Type: text/html; charset=UTF-8
```

### Scenario 2: 生產環境訪問 Swagger UI

```bash
# 前置條件: CI_ENVIRONMENT=production
curl -I https://api.production.com/api/docs

# 預期結果:
# HTTP/1.1 404 Not Found
```

### Scenario 3: 取得 OpenAPI JSON

```bash
curl http://localhost:8080/api/docs/openapi.json | jq '.openapi'

# 預期結果:
# "3.0.0"
```

### Scenario 4: 使用 JWT 測試認證端點

```bash
# 步驟 1: 在 Swagger UI 點擊 "Authorize" 按鈕
# 步驟 2: 輸入 JWT Token (不含 "Bearer " 前綴)
# 步驟 3: 點擊 "Authorize"
# 步驟 4: 測試 GET /api/auth/me 端點
# 步驟 5: 點擊 "Try it out" → "Execute"

# 預期結果: 返回當前用戶資訊 (200 OK)
```

---

## Notes

- OpenAPI JSON 在開發環境動態生成（不快取），確保註解修改立即生效
- 所有 API 文件使用繁體中文撰寫
- Swagger UI 使用 CDN 版本（swagger-ui-dist v5.x）
- 支援深度連結（Deep Linking），可分享特定 API 的 URL

---

## References

- OpenAPI 3.0 Specification: https://spec.openapis.org/oas/v3.0.0
- swagger-php GitHub: https://github.com/zircote/swagger-php
- Swagger UI Configuration: https://swagger.io/docs/open-source-tools/swagger-ui/usage/configuration/
