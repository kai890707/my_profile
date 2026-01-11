# API Endpoints Specification

**Base URL**: `http://localhost:8080/api`
**Protocol**: HTTP/HTTPS
**Content-Type**: `application/json`
**Authentication**: JWT Bearer Token (except public endpoints)

---

## Authentication Module

### POST /auth/register
**Description**: Salesperson registration
**Access**: Public
**Request Body**:
```json
{
  "username": "string (3-50 chars, unique)",
  "email": "string (valid email, unique)",
  "password": "string (min 8 chars)",
  "full_name": "string (2-100 chars)",
  "phone": "string (optional, format: 09xxxxxxxx)",
  "bio": "string (optional)"
}
```
**Response (201)**:
```json
{
  "status": "success",
  "message": "註冊成功，請等待管理員審核",
  "data": {
    "user_id": 1,
    "username": "john_doe",
    "email": "john@example.com",
    "status": "pending"
  }
}
```

### POST /auth/login
**Description**: User login
**Access**: Public
**Request Body**:
```json
{
  "email": "string",
  "password": "string"
}
```
**Response (200)**:
```json
{
  "status": "success",
  "message": "登入成功",
  "data": {
    "access_token": "eyJ0eXAi...",
    "refresh_token": "eyJ0eXAi...",
    "token_type": "Bearer",
    "expires_in": 3600,
    "user": {
      "id": 1,
      "username": "admin",
      "email": "admin@example.com",
      "role": "admin",
      "status": "active"
    }
  }
}
```

### POST /auth/refresh
**Description**: Refresh access token
**Access**: Public
**Request Body**:
```json
{
  "refresh_token": "string"
}
```
**Response (200)**:
```json
{
  "status": "success",
  "message": "Token刷新成功",
  "data": {
    "access_token": "eyJ0eXAi...",
    "token_type": "Bearer",
    "expires_in": 3600
  }
}
```

### GET /auth/me
**Description**: Get current user info
**Access**: Protected (requires auth token)
**Headers**: `Authorization: Bearer {token}`
**Response (200)**:
```json
{
  "status": "success",
  "message": "取得使用者資訊成功",
  "data": {
    "id": 1,
    "username": "admin",
    "email": "admin@example.com",
    "role": "admin",
    "status": "active",
    "profile": { /* if salesperson */ }
  }
}
```

---

## Search Module (Public)

### GET /search/salespersons
**Description**: Search salespersons
**Access**: Public
**Query Parameters**:
- `keyword` (optional): Search in name, bio, company
- `company` (optional): Filter by company name
- `industry_id` (optional): Filter by industry
- `region_id` (optional): Filter by service region
- `page` (optional, default: 1): Page number
- `per_page` (optional, default: 20): Items per page

**Response (200)**:
```json
{
  "status": "success",
  "message": "操作成功",
  "data": {
    "data": [
      {
        "id": 1,
        "full_name": "測試業務員",
        "phone": "0912345678",
        "bio": "...",
        "company_name": "台積電",
        "industry_name": "科技資訊"
      }
    ],
    "pagination": {
      "current_page": 1,
      "per_page": 20,
      "total": 100,
      "last_page": 5
    }
  }
}
```

### GET /search/salespersons/:id
**Description**: Get salesperson details
**Access**: Public
**Response (200)**:
```json
{
  "status": "success",
  "data": {
    "id": 1,
    "full_name": "測試業務員",
    "phone": "0912345678",
    "bio": "詳細介紹...",
    "specialties": "軟體銷售, 系統整合",
    "service_regions": "[\"台北市\",\"新北市\"]",
    "company_name": "台積電",
    "company_tax_id": "12345678",
    "industry_name": "科技資訊",
    "username": "salesperson_test",
    "email": "salesperson@example.com"
  }
}
```

---

## Salesperson Module

**Access**: Protected (requires auth + salesperson role)
**Headers**: `Authorization: Bearer {token}`

### GET /salesperson/profile
**Description**: Get own profile
**Response (200)**:
```json
{
  "status": "success",
  "data": {
    "id": 1,
    "user_id": 2,
    "full_name": "測試業務員",
    "phone": "0912345678",
    "bio": "...",
    "approval_status": "approved"
  }
}
```

### PUT /salesperson/profile
**Description**: Update profile
**Request Body**:
```json
{
  "full_name": "string (optional)",
  "phone": "string (optional)",
  "bio": "string (optional)",
  "specialties": "string (optional)",
  "service_regions": ["string"] (optional),
  "avatar": "base64 string (optional, triggers re-approval)"
}
```

### POST /salesperson/company
**Description**: Submit company info (requires approval)
**Request Body**:
```json
{
  "name": "string",
  "tax_id": "string (8 digits)",
  "industry_id": "integer",
  "address": "string (optional)",
  "phone": "string (optional)"
}
```

### GET /salesperson/experiences
**Description**: Get own work experiences
**Response (200)**:
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "company": "ABC公司",
      "position": "業務經理",
      "start_date": "2020-01-01",
      "end_date": "2023-12-31",
      "description": "..."
    }
  ]
}
```

### POST /salesperson/certifications
**Description**: Upload certification (requires approval)
**Request Body**:
```json
{
  "name": "string",
  "issuer": "string",
  "issue_date": "YYYY-MM-DD",
  "expiry_date": "YYYY-MM-DD (optional)",
  "file": "base64 encoded file (JPG/PNG/PDF, max 5MB)"
}
```

### GET /salesperson/approval-status
**Description**: Check approval status
**Response (200)**:
```json
{
  "status": "success",
  "data": {
    "profile": {
      "id": 1,
      "approval_status": "approved",
      "approved_at": "2026-01-08 12:00:00"
    },
    "certifications": [
      {
        "id": 1,
        "name": "證照名稱",
        "approval_status": "pending"
      }
    ]
  }
}
```

---

## Admin Module

**Access**: Protected (requires auth + admin role)
**Headers**: `Authorization: Bearer {token}`

### GET /admin/pending-approvals
**Description**: Get all pending items
**Response (200)**:
```json
{
  "status": "success",
  "data": {
    "users": [/* pending salesperson registrations */],
    "profiles": [/* pending profile updates */],
    "companies": [/* pending company info */],
    "certifications": [/* pending certifications */]
  }
}
```

### POST /admin/approve-user/:id
**Description**: Approve salesperson registration
**Response (200)**:
```json
{
  "status": "success",
  "message": "業務員註冊已審核通過"
}
```

### POST /admin/reject-user/:id
**Description**: Reject salesperson registration
**Request Body**:
```json
{
  "reason": "string (optional)"
}
```

### GET /admin/users
**Description**: List all users
**Query Parameters**:
- `role` (optional): Filter by role
- `status` (optional): Filter by status

### PUT /admin/users/:id/status
**Description**: Update user status
**Request Body**:
```json
{
  "status": "active|inactive"
}
```

### DELETE /admin/users/:id
**Description**: Delete user (soft delete)

### GET /admin/settings/industries
**Description**: Get all industries

### POST /admin/settings/industries
**Description**: Create industry
**Request Body**:
```json
{
  "name": "string",
  "slug": "string",
  "description": "string (optional)"
}
```

### GET /admin/statistics
**Description**: Get platform statistics
**Response (200)**:
```json
{
  "status": "success",
  "data": {
    "total_salespersons": 10,
    "active_salespersons": 8,
    "pending_salespersons": 2,
    "total_companies": 5,
    "pending_approvals": 3
  }
}
```

---

## Error Responses

All endpoints may return these error responses:

**400 Bad Request**:
```json
{
  "status": "error",
  "message": "操作失敗",
  "errors": { /* validation errors */ }
}
```

**401 Unauthorized**:
```json
{
  "status": "error",
  "message": "未授權，請先登入"
}
```

**403 Forbidden**:
```json
{
  "status": "error",
  "message": "權限不足",
  "required_role": ["admin"],
  "your_role": "salesperson"
}
```

**404 Not Found**:
```json
{
  "status": "error",
  "message": "資源不存在"
}
```

**422 Validation Error**:
```json
{
  "status": "error",
  "message": "資料驗證失敗",
  "errors": {
    "email": "Email 格式不正確"
  }
}
```

**500 Internal Server Error**:
```json
{
  "status": "error",
  "message": "伺服器錯誤"
}
```

---

## Complete Endpoint List (35 total)

**Authentication (5)**:
- POST /auth/register
- POST /auth/login
- POST /auth/refresh
- POST /auth/logout
- GET /auth/me

**Search (2)**:
- GET /search/salespersons
- GET /search/salespersons/:id

**Salesperson (9)**:
- GET /salesperson/profile
- PUT /salesperson/profile
- POST /salesperson/company
- GET /salesperson/experiences
- POST /salesperson/experiences
- DELETE /salesperson/experiences/:id
- GET /salesperson/certifications
- POST /salesperson/certifications
- GET /salesperson/approval-status

**Admin (19)**:
- GET /admin/pending-approvals
- POST /admin/approve-user/:id
- POST /admin/reject-user/:id
- POST /admin/approve-company/:id
- POST /admin/reject-company/:id
- POST /admin/approve-certification/:id
- POST /admin/reject-certification/:id
- GET /admin/users
- PUT /admin/users/:id/status
- DELETE /admin/users/:id
- GET /admin/settings/industries
- POST /admin/settings/industries
- GET /admin/settings/regions
- POST /admin/settings/regions
- GET /admin/statistics

---

## Feature: Swagger API Documentation

**Added**: 2026-01-08
**Change**: swagger-api-documentation

### GET /api/docs

**Description**: 顯示 Swagger UI 互動式 API 文件介面

**Authentication**: Not required

**Authorization**: Public (僅開發環境)

**Request Parameters**: None

**Response (200 OK)**: HTML 頁面 (Swagger UI)

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

# 生產環境 - 失敗
curl -X GET https://production.example.com/api/docs
# 返回: 404 Not Found
```

---

### GET /api/docs/openapi.json

**Description**: 返回 OpenAPI 3.0 規格 JSON，描述所有 API 端點

**Authentication**: Not required

**Authorization**: Public (僅開發環境)

**Request Parameters**: None

**Response (200 OK)**:
```json
{
    "openapi": "3.0.0",
    "info": {
        "title": "業務推廣系統 API",
        "description": "業務員管理與搜尋平台的 RESTful API",
        "version": "1.0.0"
    },
    "servers": [
        {
            "url": "http://localhost:8080",
            "description": "開發環境"
        }
    ],
    "paths": { /* 所有 API 端點定義 */ },
    "components": {
        "securitySchemes": {
            "bearerAuth": {
                "type": "http",
                "scheme": "bearer",
                "bearerFormat": "JWT"
            }
        }
    }
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

# 測試 Swagger UI 是否正確載入規格
curl -X GET http://localhost:8080/api/docs \
  -H "Accept: text/html" | grep "swagger-ui"
```

---


---

## Feature: User Registration Refactor

**Added**: 2026-01-11
**Change**: user-registration-refactor

### API Endpoints

#### Authentication Endpoints

```
POST /api/auth/register
Request:
{
    "name": "string",
    "email": "string|email|unique",
    "password": "string|min:8"
}
Response 201:
{
    "user": User,
    "token": "string"
}

POST /api/auth/register-salesperson
Request:
{
    "name": "string",
    "email": "string|email|unique",
    "password": "string|min:8",
    "full_name": "string",
    "phone": "string",
    "bio": "string|nullable",
    "specialties": "string|nullable",
    "service_regions": "array|nullable"
}
Response 201:
{
    "user": User (with salespersonProfile),
    "token": "string",
    "message": "註冊成功！..."
}
```

#### Salesperson Endpoints

```
POST /api/salesperson/upgrade
Middleware: auth:sanctum
Request:
{
    "full_name": "string",
    "phone": "string",
    "bio": "string|nullable",
    "specialties": "string|nullable",
    "service_regions": "array|nullable"
}
Response 200:
{
    "user": User (with salespersonProfile),
    "message": "升級成功！..."
}
Response 429 (Too Early):
{
    "error": "請於 YYYY-MM-DD 後重新申請",
    "can_reapply_at": "datetime"
}

GET /api/salesperson/status
Middleware: auth:sanctum
Response 200:
{
    "is_salesperson": boolean,
    "status": "pending|approved|rejected|null",
    "applied_at": "datetime|null",
    "approved_at": "datetime|null",
    "rejection_reason": "string|null",
    "can_reapply_at": "datetime|null",
    "can_reapply": boolean
}

PUT /api/salesperson/profile
Middleware: auth:sanctum, salesperson
Request:
{
    "company_id": "integer|nullable|exists:companies,id",
    "full_name": "string",
    "phone": "string",
    "bio": "string|nullable",
    "specialties": "string|nullable",
    "service_regions": "array|nullable"
}
Response 200:
{
    "profile": SalespersonProfile,
    "message": "個人資料已更新"
}

GET /api/salespeople
Response 200:
{
    "data": [User (with salespersonProfile)],
    "links": {...},
    "meta": {...}
}
```

#### Admin Endpoints

```
GET /api/admin/salesperson-applications
Middleware: auth:sanctum, admin
Response 200:
{
    "data": [User (with salespersonProfile)],
    "links": {...},
    "meta": {...}
}

POST /api/admin/salesperson-applications/{id}/approve
Middleware: auth:sanctum, admin
Response 200:
{
    "user": User (with salespersonProfile),
    "message": "已批准業務員申請"
}

POST /api/admin/salesperson-applications/{id}/reject
Middleware: auth:sanctum, admin
Request:
{
    "rejection_reason": "string|required",
    "reapply_days": "integer|min:0|max:90|nullable"
}
Response 200:
{
    "user": User,
    "message": "已拒絕業務員申請"
}
```

#### Company Endpoints

```
GET /api/companies/search
Query Parameters:
- tax_id: string (精確搜尋)
- name: string (模糊搜尋)

Response 200 (tax_id search):
{
    "exists": boolean,
    "company": Company|null
}

Response 200 (name search):
[
    {
        "id": integer,
        "name": "string",
        "tax_id": "string|null",
        "is_personal": boolean
    }
]

POST /api/companies
Middleware: auth:sanctum, approved_salesperson
Request:
{
    "name": "string|required|max:200",
    "tax_id": "string|nullable|max:50|unique:companies",
    "is_personal": "boolean"
}
Validation Rules:
- If is_personal=false, tax_id is required
Response 201:
{
    "company": Company,
    "message": "公司建立成功"
}
Response 422 (tax_id duplicate):
{
    "errors": {
        "tax_id": ["統一編號已被使用"]
    }
}
```

---

## Frontend 規格
---
