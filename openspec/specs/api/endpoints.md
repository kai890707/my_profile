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
**Description**: Get all work experiences for the authenticated salesperson
**Access**: Protected (requires auth + salesperson role)
**Headers**: `Authorization: Bearer {token}`

**Query Parameters**:
- `sort_by` (optional, default: start_date): Field to sort by
  - Allowed values: `start_date`, `company`, `position`, `sort_order`
- `order` (optional, default: desc): Sort order
  - Allowed values: `asc`, `desc`

**Response (200)**:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "user_id": 10,
      "company": "台積電",
      "position": "業務經理",
      "start_date": "2020-01-01",
      "end_date": "2023-12-31",
      "description": "負責企業客戶開發與維護，達成120%業績目標",
      "approval_status": "approved",
      "rejected_reason": null,
      "approved_by": 1,
      "approved_at": "2020-01-15T08:30:00Z",
      "sort_order": 0,
      "created_at": "2026-01-10T12:00:00Z",
      "updated_at": "2026-01-10T12:00:00Z"
    }
  ],
  "message": "Experiences retrieved successfully"
}
```

**Response (401)**:
```json
{
  "success": false,
  "error": {
    "code": "UNAUTHORIZED",
    "message": "Authentication required"
  }
}
```

**Response (403)**:
```json
{
  "success": false,
  "error": {
    "code": "FORBIDDEN",
    "message": "Only salespeople can access experiences"
  }
}
```

### POST /salesperson/experiences
**Description**: Create a new work experience (auto-approved)
**Access**: Protected (requires auth + salesperson role)
**Headers**: `Authorization: Bearer {token}`

**Request Body**:
```json
{
  "company": "string (required, max:200)",
  "position": "string (required, max:200)",
  "start_date": "YYYY-MM-DD (required)",
  "end_date": "YYYY-MM-DD (optional, must be >= start_date)",
  "description": "string (optional)",
  "sort_order": "integer (optional, min:0, default:0)"
}
```

**Response (201)**:
```json
{
  "success": true,
  "data": {
    "id": 3,
    "user_id": 10,
    "company": "聯發科技",
    "position": "資深業務代表",
    "start_date": "2024-01-01",
    "end_date": null,
    "description": "負責半導體產品銷售",
    "approval_status": "approved",
    "rejected_reason": null,
    "approved_by": null,
    "approved_at": null,
    "sort_order": 0,
    "created_at": "2026-01-11T10:00:00Z",
    "updated_at": "2026-01-11T10:00:00Z"
  },
  "message": "Experience created successfully"
}
```

**Response (401)**:
```json
{
  "success": false,
  "error": {
    "code": "UNAUTHORIZED",
    "message": "Authentication required"
  }
}
```

**Response (403)**:
```json
{
  "success": false,
  "error": {
    "code": "FORBIDDEN",
    "message": "Only salespeople can create experiences"
  }
}
```

**Response (422)**:
```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "The given data was invalid",
    "details": {
      "company": ["公司名稱為必填項目"],
      "position": ["職位名稱為必填項目"],
      "end_date": ["結束日期必須等於或晚於開始日期"]
    }
  }
}
```

**Note**: Experience records are automatically approved (approval_status = 'approved') upon creation.

### PUT /salesperson/experiences/{id}
**Description**: Update an existing work experience
**Access**: Protected (requires auth + salesperson role + ownership)
**Headers**: `Authorization: Bearer {token}`
**Path Parameter**: `id` (integer) - Experience ID

**Request Body**:
```json
{
  "company": "string (required, max:200)",
  "position": "string (required, max:200)",
  "start_date": "YYYY-MM-DD (required)",
  "end_date": "YYYY-MM-DD (optional, must be >= start_date)",
  "description": "string (optional)",
  "sort_order": "integer (optional, min:0)"
}
```

**Response (200)**:
```json
{
  "success": true,
  "data": {
    "id": 1,
    "user_id": 10,
    "company": "聯發科技股份有限公司",
    "position": "資深業務經理",
    "start_date": "2020-01-01",
    "end_date": "2024-12-31",
    "description": "更新後的描述",
    "approval_status": "approved",
    "rejected_reason": null,
    "approved_by": 1,
    "approved_at": "2020-01-15T08:30:00Z",
    "sort_order": 1,
    "created_at": "2026-01-10T12:00:00Z",
    "updated_at": "2026-01-22T14:30:00Z"
  },
  "message": "Experience updated successfully"
}
```

**Response (401)**:
```json
{
  "success": false,
  "error": {
    "code": "UNAUTHORIZED",
    "message": "Authentication required"
  }
}
```

**Response (403)**:
```json
{
  "success": false,
  "error": {
    "code": "FORBIDDEN",
    "message": "You can only update your own experiences"
  }
}
```

**Response (404)**:
```json
{
  "success": false,
  "error": {
    "code": "NOT_FOUND",
    "message": "Experience not found"
  }
}
```

**Response (422)**:
```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "The given data was invalid",
    "details": {
      "end_date": ["結束日期必須等於或晚於開始日期"]
    }
  }
}
```

**Business Rule**: Only the owner (BR-EXP-001) can update their own experiences.

### DELETE /salesperson/experiences/{id}
**Description**: Delete a work experience
**Access**: Protected (requires auth + salesperson role + ownership)
**Headers**: `Authorization: Bearer {token}`
**Path Parameter**: `id` (integer) - Experience ID

**Response (200)**:
```json
{
  "success": true,
  "message": "Experience deleted successfully"
}
```

**Response (401)**:
```json
{
  "success": false,
  "error": {
    "code": "UNAUTHORIZED",
    "message": "Authentication required"
  }
}
```

**Response (403)**:
```json
{
  "success": false,
  "error": {
    "code": "FORBIDDEN",
    "message": "You can only delete your own experiences"
  }
}
```

**Response (404)**:
```json
{
  "success": false,
  "error": {
    "code": "NOT_FOUND",
    "message": "Experience not found"
  }
}
```

**Business Rule**: Only the owner (BR-EXP-001) can delete their own experiences.

### GET /salesperson/certifications
**Description**: Get all certifications for the authenticated salesperson
**Access**: Protected (requires auth + salesperson role)
**Headers**: `Authorization: Bearer {token}`

**Query Parameters**: None

**Response (200)**:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "user_id": 10,
      "name": "Certified Sales Professional (CSP)",
      "issuer": "Sales and Marketing Institute",
      "issue_date": "2022-01-15",
      "expiry_date": "2025-01-15",
      "description": "Advanced sales certification with focus on B2B strategies",
      "file_data": null,
      "file_mime": "application/pdf",
      "file_size": 2048576,
      "file_size_mb": 1.95,
      "has_file": true,
      "approval_status": "approved",
      "rejected_reason": null,
      "approved_by": 1,
      "approved_at": "2022-01-20T10:00:00Z",
      "created_at": "2026-01-10T12:00:00Z",
      "updated_at": "2026-01-10T12:00:00Z"
    }
  ],
  "message": "Certifications retrieved successfully"
}
```

**Response (401)**:
```json
{
  "success": false,
  "error": {
    "code": "UNAUTHORIZED",
    "message": "Authentication required"
  }
}
```

**Response (403)**:
```json
{
  "success": false,
  "error": {
    "code": "FORBIDDEN",
    "message": "Only salespeople can access certifications"
  }
}
```

**Note**:
- Certifications are sorted by `created_at DESC` (newest first)
- `file_data` is never returned in API responses for performance reasons
- Use `has_file` to check if certification has an attached file
- `file_size_mb` is calculated and rounded to 2 decimal places

### POST /salesperson/certifications
**Description**: Upload a new certification with Base64-encoded file (requires admin approval)
**Access**: Protected (requires auth + salesperson role)
**Headers**: `Authorization: Bearer {token}`

**Request Body**:
```json
{
  "name": "string (required, max:200)",
  "issuer": "string (required, max:200)",
  "issue_date": "YYYY-MM-DD (optional)",
  "expiry_date": "YYYY-MM-DD (optional, must be >= issue_date)",
  "description": "string (optional)",
  "file": "string (required, Base64 encoded file)",
  "file_mime": "string (required, allowed: image/jpeg, image/jpg, image/png, application/pdf)"
}
```

**File Requirements**:
- Format: Base64 encoded string
- Supported MIME types: `image/jpeg`, `image/jpg`, `image/png`, `application/pdf`
- Max size: 16MB (16,777,216 bytes)
- Can include data URL prefix (e.g., `data:application/pdf;base64,`) or just the Base64 string

**Response (201)**:
```json
{
  "success": true,
  "data": {
    "id": 2,
    "user_id": 10,
    "name": "Project Management Professional (PMP)",
    "issuer": "Project Management Institute",
    "issue_date": "2023-06-01",
    "expiry_date": "2026-06-01",
    "description": "PMP certification for project management",
    "file_data": null,
    "file_mime": "application/pdf",
    "file_size": 3145728,
    "file_size_mb": 3.0,
    "has_file": true,
    "approval_status": "pending",
    "rejected_reason": null,
    "approved_by": null,
    "approved_at": null,
    "created_at": "2026-01-11T10:00:00Z",
    "updated_at": "2026-01-11T10:00:00Z"
  },
  "message": "Certification created successfully. Pending approval."
}
```

**Response (401)**:
```json
{
  "success": false,
  "error": {
    "code": "UNAUTHORIZED",
    "message": "Authentication required"
  }
}
```

**Response (403)**:
```json
{
  "success": false,
  "error": {
    "code": "FORBIDDEN",
    "message": "Only salespeople can create certifications"
  }
}
```

**Response (422) - Validation Error**:
```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "The given data was invalid",
    "details": {
      "name": ["證照名稱為必填項目"],
      "file": ["證照檔案為必填項目"],
      "file_mime": ["只接受 JPEG、PNG 或 PDF 格式的檔案"]
    }
  }
}
```

**Response (422) - Invalid Base64**:
```json
{
  "success": false,
  "error": {
    "code": "INVALID_FILE",
    "message": "Invalid Base64 file data"
  }
}
```

**Response (422) - File Too Large**:
```json
{
  "success": false,
  "error": {
    "code": "FILE_TOO_LARGE",
    "message": "File size exceeds 16MB limit"
  }
}
```

**Important Notes**:
- Certification `approval_status` is automatically set to `"pending"` upon creation
- Admin approval is required before certification becomes visible to public
- File is stored in database as BLOB (file_data column)
- Base64 encoding increases file size by ~33%, so actual upload limit is ~12MB of original file

**Example Base64 Upload**:
```json
{
  "name": "Sales Excellence Award",
  "issuer": "National Sales Association",
  "issue_date": "2024-01-01",
  "file": "data:application/pdf;base64,JVBERi0xLjQKJeLjz9MKNCAwIG9iaiA8P...",
  "file_mime": "application/pdf"
}
```

### DELETE /salesperson/certifications/{id}
**Description**: Delete a certification
**Access**: Protected (requires auth + salesperson role + ownership)
**Headers**: `Authorization: Bearer {token}`
**Path Parameter**: `id` (integer) - Certification ID

**Response (200)**:
```json
{
  "success": true,
  "message": "Certification deleted successfully"
}
```

**Response (401)**:
```json
{
  "success": false,
  "error": {
    "code": "UNAUTHORIZED",
    "message": "Authentication required"
  }
}
```

**Response (403)**:
```json
{
  "success": false,
  "error": {
    "code": "FORBIDDEN",
    "message": "You can only delete your own certifications"
  }
}
```

**Response (404)**:
```json
{
  "success": false,
  "error": {
    "code": "NOT_FOUND",
    "message": "Certification not found"
  }
}
```

**Business Rule**: Only the owner (BR-CERT-001) can delete their own certifications.

### GET /salesperson/approval-status
**Description**: Get aggregated approval status for profile, company, experiences, and certifications
**Access**: Protected (salesperson role)
**Response (200)**:
```json
{
  "success": true,
  "data": {
    "profile_status": "approved",
    "company_status": "pending",
    "experiences": [
      {
        "id": 1,
        "company": "ABC公司",
        "position": "業務經理",
        "approval_status": "approved"
      }
    ],
    "certifications": [
      {
        "id": 1,
        "name": "Certified Sales Professional",
        "approval_status": "pending"
      }
    ]
  },
  "message": "Approval status retrieved successfully"
}
```

---

## Admin Module

**Access**: Protected (requires auth + admin role)
**Headers**: `Authorization: Bearer {token}`

### GET /admin/statistics
**Description**: 取得管理員統計資訊，包含業務員、公司、待審核項目的數量

**Response (200)**:
```json
{
  "success": true,
  "data": {
    "total_salespeople": 150,
    "active_salespeople": 120,
    "pending_salespeople": 30,
    "total_companies": 80,
    "pending_approvals": 15
  }
}
```

**Error Responses**:
- 401: Unauthorized
- 403: Forbidden (非管理員)

### GET /admin/pending-approvals
**Description**: 取得所有待審核的公司、業務員檔案、工作經驗和證照列表

**Response (200)**:
```json
{
  "success": true,
  "data": {
    "companies": [/* Company objects with approval_status: pending */],
    "profiles": [/* SalespersonProfile objects with approval_status: pending */],
    "experiences": [/* Experience objects with approval_status: pending */],
    "certifications": [/* Certification objects with approval_status: pending */]
  }
}
```

**Error Responses**:
- 401: Unauthorized
- 403: Forbidden (非管理員)

### GET /admin/salesperson-applications
**Description**: 取得所有待審核的業務員申請列表

**Response (200)**:
```json
{
  "success": true,
  "data": {
    "data": [/* User objects with role: salesperson, salesperson_status: pending */],
    "current_page": 1,
    "per_page": 20,
    "total": 30,
    "last_page": 2
  }
}
```

**Error Responses**:
- 401: Unauthorized
- 403: Forbidden (非管理員)

### POST /admin/salesperson-applications/{id}/approve
**Description**: 批准業務員申請，將業務員狀態設為已審核通過

**Response (200)**:
```json
{
  "success": true,
  "user": {/* User object with updated salesperson_status */},
  "message": "已批准業務員申請"
}
```

**Error Responses**:
- 400: Bad Request (status 不是 pending)
- 401: Unauthorized
- 403: Forbidden (非管理員)
- 404: Not Found

### POST /admin/salesperson-applications/{id}/reject
**Description**: 拒絕業務員申請，將業務員狀態設為已拒絕，需提供拒絕原因

**Request Body**:
```json
{
  "rejection_reason": "string - required"
}
```

**Response (200)**:
```json
{
  "success": true,
  "user": {/* User object with updated salesperson_status */},
  "message": "已拒絕業務員申請"
}
```

**Error Responses**:
- 400: Bad Request (status 不是 pending)
- 401: Unauthorized
- 403: Forbidden (非管理員)
- 404: Not Found
- 422: Validation Error (缺少 rejection_reason)

### POST /admin/approve-company/{id}
**Description**: 批准公司，將公司審核狀態設為已通過

**Response (200)**:
```json
{
  "success": true,
  "company": {/* Company object with approval_status: approved */},
  "message": "公司已批准"
}
```

**Error Responses**:
- 400: Bad Request (approval_status 不是 pending)
- 401: Unauthorized
- 403: Forbidden (非管理員)
- 404: Not Found

### POST /admin/approve-experience/{id}
**Description**: 批准工作經驗，將工作經驗審核狀態設為已通過

**Response (200)**:
```json
{
  "success": true,
  "experience": {/* Experience object with approval_status: approved */},
  "message": "工作經驗已批准"
}
```

**Error Responses**:
- 400: Bad Request (approval_status 不是 pending)
- 401: Unauthorized
- 403: Forbidden (非管理員)
- 404: Not Found

### POST /admin/reject-experience/{id}
**Description**: 拒絕工作經驗，將工作經驗審核狀態設為已拒絕

**Request Body** (optional):
```json
{
  "reason": "string - optional - 拒絕原因"
}
```

**Response (200)**:
```json
{
  "success": true,
  "experience": {/* Experience object with approval_status: rejected */},
  "message": "工作經驗已拒絕"
}
```

**Error Responses**:
- 400: Bad Request (approval_status 不是 pending)
- 401: Unauthorized
- 403: Forbidden (非管理員)
- 404: Not Found

### POST /admin/approve-certification/{id}
**Description**: 批准證照，將證照審核狀態設為已通過

**Response (200)**:
```json
{
  "success": true,
  "certification": {/* Certification object with approval_status: approved */},
  "message": "證照已批准"
}
```

**Error Responses**:
- 400: Bad Request (approval_status 不是 pending)
- 401: Unauthorized
- 403: Forbidden (非管理員)
- 404: Not Found

### POST /admin/reject-certification/{id}
**Description**: 拒絕證照，將證照審核狀態設為已拒絕

**Request Body** (optional):
```json
{
  "reason": "string - optional - 拒絕原因"
}
```

**Response (200)**:
```json
{
  "success": true,
  "certification": {/* Certification object with approval_status: rejected */},
  "message": "證照已拒絕"
}
```

**Error Responses**:
- 400: Bad Request (approval_status 不是 pending)
- 401: Unauthorized
- 403: Forbidden (非管理員)
- 404: Not Found

### GET /admin/users
**Description**: 取得使用者列表，支援角色和狀態篩選

**Query Parameters**:
- `role` (optional): 角色篩選 (admin, salesperson, user)
- `status` (optional): 狀態篩選 (active, inactive, pending)
- `page` (optional): 頁碼 (default: 1)
- `per_page` (optional): 每頁筆數 (default: 15, max: 100)

**Response (200)**:
```json
{
  "success": true,
  "data": [/* User objects */],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 100,
    "last_page": 7
  }
}
```

**Error Responses**:
- 401: Unauthorized
- 403: Forbidden (非管理員)

### PUT /admin/users/{id}/status
**Description**: 啟用或停用使用者帳號

**Request Body**:
```json
{
  "status": "active|inactive - required"
}
```

**Response (200)**:
```json
{
  "success": true,
  "data": {/* User object with updated status */},
  "message": "使用者狀態已更新"
}
```

**Error Responses**:
- 400: Bad Request (無法停用管理員或自己)
- 401: Unauthorized
- 403: Forbidden (非管理員)
- 404: Not Found
- 422: Validation Error

### DELETE /admin/users/{id}
**Description**: 永久刪除使用者帳號

**Response (200)**:
```json
{
  "success": true,
  "message": "使用者已刪除"
}
```

**Error Responses**:
- 400: Bad Request (無法刪除管理員或自己)
- 401: Unauthorized
- 403: Forbidden (非管理員)
- 404: Not Found

### GET /admin/settings/regions
**Description**: 取得系統中所有可用的地區列表

**Response (200)**:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "台北市",
      "parent_id": null
    }
  ]
}
```

**Error Responses**:
- 401: Unauthorized
- 403: Forbidden (非管理員)

### GET /admin/settings/industries
**Description**: 取得系統中所有可用的產業列表

**Response (200)**:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "科技業"
    }
  ]
}
```

**Error Responses**:
- 401: Unauthorized
- 403: Forbidden (非管理員)

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

## Complete Endpoint List (33 total)

**Authentication (5)**:
- POST /auth/register
- POST /auth/login
- POST /auth/refresh
- POST /auth/logout
- GET /auth/me

**Search (2)**:
- GET /search/salespersons
- GET /search/salespersons/:id

**Salesperson (11)**:
- GET /salesperson/profile
- PUT /salesperson/profile
- POST /salesperson/company
- GET /salesperson/experiences
- POST /salesperson/experiences
- PUT /salesperson/experiences/:id
- DELETE /salesperson/experiences/:id
- GET /salesperson/certifications
- POST /salesperson/certifications
- DELETE /salesperson/certifications/:id
- GET /salesperson/approval-status

**Admin (15)**:
- GET /admin/statistics
- GET /admin/pending-approvals
- GET /admin/salesperson-applications
- POST /admin/salesperson-applications/{id}/approve
- POST /admin/salesperson-applications/{id}/reject
- POST /admin/approve-company/{id}
- POST /admin/approve-experience/{id}
- POST /admin/reject-experience/{id}
- POST /admin/approve-certification/{id}
- POST /admin/reject-certification/{id}
- GET /admin/users
- PUT /admin/users/{id}/status
- DELETE /admin/users/{id}
- GET /admin/settings/regions
- GET /admin/settings/industries

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

---

## API Design Patterns & Best Practices

**Last Updated**: 2026-01-21
**Source**: Extracted from production changes

### Pattern 1: API Response Data Unwrapping

**Issue**: Frontend hooks may receive nested response structures that need unwrapping.

**Backend Response Format**:
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "role": "salesperson"
    }
  }
}
```

**Frontend Hook Pattern**:
```typescript
// ❌ Bad: Direct return causes nested structure
return response.data; // Returns { user: {...} }

// ✅ Good: Unwrap the nested structure
export function useAuth() {
  return useQuery({
    queryKey: queryKeys.auth.me,
    queryFn: async () => {
      const response = await getCurrentUser();
      const data = response.data as { user?: any } | any;
      return data?.user ?? data;  // Unwrap user object
    },
  });
}
```

**When to Use**:
- `/api/auth/me` endpoint
- `/api/salesperson/profile` endpoint
- Any endpoint where backend wraps data in nested objects

**Related Changes**:
- 20260115-fix-header-dropdown-and-dashboard-access
- 20260115-fix-dashboard-profile-edit-prefill

---

### Pattern 2: Form Request Validation (Backend)

**Best Practice**: Use dedicated Form Request classes instead of inline validation.

```php
// ❌ Bad: Inline validation in controller
public function rejectSalesperson(Request $request, int $id) {
    $validator = Validator::make($request->all(), [
        'reason' => 'required|string|max:500'
    ]);
    // ...
}

// ✅ Good: Dedicated Form Request class
class RejectSalespersonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Handled by middleware
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'reason.required' => '拒絕原因為必填',
            'reason.max' => '拒絕原因不可超過 500 字元',
        ];
    }
}

// Controller usage
public function rejectSalesperson(RejectSalespersonRequest $request, int $id) {
    $reason = $request->validated('reason');
    // ...
}
```

**Benefits**:
- Centralized validation logic
- Reusable across controllers
- Better type safety (PHPStan Level 9 compatible)
- Cleaner controller code

**Related Change**: 20260120-fix-backend-code-quality

---

### Pattern 3: API Resources for Response Formatting

**Best Practice**: Use API Resources instead of returning raw models.

```php
// ❌ Bad: Direct model return
public function profile(): JsonResponse
{
    $profile = SalespersonProfile::find($id);
    return response()->json(['data' => $profile]);
}

// ✅ Good: Use API Resource
class SalespersonResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'user' => new UserResource($this->whenLoaded('user')),
            'company' => new CompanyResource($this->whenLoaded('company')),
            'bio' => $this->bio,
            'years_of_experience' => $this->years_of_experience,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}

// Controller usage
public function profile(): JsonResponse
{
    $profile = SalespersonProfile::with(['user', 'company'])->find($id);
    return response()->json([
        'success' => true,
        'data' => new SalespersonResource($profile)
    ]);
}
```

**Benefits**:
- Consistent response format
- Hide sensitive fields (e.g., internal IDs, timestamps)
- Type-safe transformations
- Easy to version (ResourceV1, ResourceV2)

**Related Change**: 20260120-fix-backend-code-quality

---

### Pattern 4: Company Search with Multiple Criteria

**Use Case**: Allow users to search companies by name (fuzzy) or tax_id (exact).

**API Endpoint**:
```
GET /api/companies/search?keyword={keyword}
```

**Backend Logic**:
```php
public function search(Request $request): JsonResponse
{
    $keyword = $request->query('keyword');

    $query = Company::query();

    if ($keyword) {
        // Try exact match on tax_id first
        if (preg_match('/^\d{8}$/', $keyword)) {
            $company = $query->where('tax_id', $keyword)->first();
            return response()->json([
                'exists' => (bool) $company,
                'company' => $company ? new CompanyResource($company) : null
            ]);
        }

        // Fuzzy search on name
        $companies = $query->where('name', 'LIKE', "%{$keyword}%")
            ->limit(10)
            ->get();

        return response()->json([
            'exists' => $companies->isNotEmpty(),
            'companies' => CompanyResource::collection($companies)
        ]);
    }

    return response()->json(['exists' => false, 'companies' => []]);
}
```

**Search Strategy**:
1. Exact match on `tax_id` (8 digits)
2. Fuzzy match on `name` (LIKE %keyword%)
3. Limit results to 10 items
4. Return different response formats based on search type

**Related Change**: 20260118-improve-company-selection

---

### Pattern 5: Rate Limiting Configuration

**Best Practice**: Configure different rate limits for different API types.

```php
// app/Providers/RouteServiceProvider.php
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

RateLimiter::for('public-api', function (Request $request) {
    return Limit::perMinute(60)->by($request->ip());
});

RateLimiter::for('authenticated-api', function (Request $request) {
    return Limit::perMinute(120)->by(optional($request->user())->id ?: $request->ip());
});

RateLimiter::for('admin-api', function (Request $request) {
    return Limit::perMinute(300)->by(optional($request->user())->id ?: $request->ip());
});

// routes/api.php
Route::middleware(['throttle:public-api'])->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/register', [AuthController::class, 'register']);
});

Route::middleware(['auth:api', 'throttle:authenticated-api'])->group(function () {
    Route::get('/salesperson/profile', [SalespersonController::class, 'profile']);
});

Route::middleware(['auth:api', 'role:admin', 'throttle:admin-api'])->group(function () {
    Route::get('/admin/statistics', [AdminController::class, 'statistics']);
});
```

**Rate Limit Guidelines**:
- Public APIs: 60 req/min (by IP)
- Authenticated APIs: 120 req/min (by user ID)
- Admin APIs: 300 req/min (by admin ID)

**Related Change**: 20260120-fix-backend-code-quality

---

### Pattern 6: Null Safety and Optional Chaining

**Issue**: Prevent errors when accessing potentially null/undefined values.

**Backend (PHP)**:
```php
// ❌ Bad: Can cause null pointer errors
$userName = $user->salespersonProfile->full_name;

// ✅ Good: Use null-safe operator (PHP 8.0+)
$userName = $user->salespersonProfile?->full_name ?? 'Unknown';

// ✅ Good: Use optional() helper
$userName = optional($user->salespersonProfile)->full_name ?? 'Unknown';
```

**Frontend (TypeScript)**:
```typescript
// ❌ Bad: Can cause runtime errors
const fullName = profile.full_name.substring(0, 2);

// ✅ Good: Optional chaining + nullish coalescing
const fullName = profile.full_name?.substring(0, 2) ?? 'U';

// ✅ Good: Early return pattern
if (!profile?.full_name) {
    return 'U';
}
return profile.full_name.substring(0, 2);
```

**Related Changes**:
- 20260115-fix-header-dropdown-and-dashboard-access
- 20260115-fix-dashboard-profile-edit-prefill

---

### Pattern 7: Cache Strategy for Expensive Queries

**Use Case**: Cache statistics and list queries to reduce database load.

```php
use Illuminate\Support\Facades\Cache;

// Cache for 5 minutes
public function statistics(): JsonResponse
{
    $stats = Cache::remember('admin:statistics', 300, function () {
        return [
            'total_salespeople' => User::salespersons()->count(),
            'active_salespeople' => User::salespersons()->active()->count(),
            'pending_salespersons' => User::salespersons()->pending()->count(),
            'total_companies' => Company::count(),
        ];
    });

    return response()->json([
        'success' => true,
        'data' => $stats
    ]);
}

// Cache with dynamic keys
public function salespersons(Request $request): JsonResponse
{
    $page = $request->query('page', 1);
    $filters = $request->only(['keyword', 'region', 'industry']);
    $cacheKey = 'salespersons:list:' . md5(json_encode(['page' => $page, 'filters' => $filters]));

    $result = Cache::remember($cacheKey, 60, function () use ($page, $filters) {
        return Salesperson::filter($filters)->paginate(20, ['*'], 'page', $page);
    });

    return response()->json($result);
}
```

**Cache Invalidation**:
```php
// Clear cache when data changes
public function updateProfile(Request $request): JsonResponse
{
    $profile->update($request->validated());

    // Clear related caches
    Cache::forget('salespersons:list:*'); // Use Cache::tags() for better management
    Cache::forget('admin:statistics');

    return response()->json(['success' => true]);
}
```

**Cache TTL Guidelines**:
- Statistics: 5 minutes (300s)
- List queries: 1 minute (60s)
- Detail queries: 5 minutes (300s)
- Search results: 30 seconds (30s)

**Related Change**: 20260120-fix-backend-code-quality

---

## Frontend 規格
---

---

# Contact Mechanism API (聯繫機制)

**Version**: 1.0  
**Last Updated**: 2026-01-23

## Overview

Contact Mechanism 功能提供業務員與客戶之間的聯繫管道，包含：
1. 業務員設定聯繫方式
2. 客戶提交聯繫請求
3. 聯繫事件追蹤
4. Email 通知

## API Endpoints

| Endpoint | Method | Auth | Role | Description |
|----------|--------|------|------|-------------|
| `/api/salesperson/profile/contact` | PUT | ✅ | salesperson | 更新聯繫方式 |
| `/api/salespersons/{id}/contact-info` | GET | ❌ | - | 查詢聯繫資訊 |
| `/api/contact-requests` | POST | ✅ | user, salesperson | 提交聯繫請求 |
| `/api/events/track` | POST | ❌ | - | 追蹤事件 |

---

## PUT /api/salesperson/profile/contact

**Description**: 業務員更新個人聯繫方式

**Authentication**: Required (JWT, salesperson role)

**Rate Limit**: 120 requests/minute

### Request

```http
PUT /api/salesperson/profile/contact
Authorization: Bearer {access_token}
Content-Type: application/json
```

```json
{
  "phone": "0912-345-678",
  "email_public": "contact@example.com",
  "line_id": "my_line_id",
  "wechat_id": "my_wechat",
  "contact_preferences": ["line", "phone", "email", "wechat"]
}
```

### Request Fields

| Field | Type | Required | Description | Validation |
|-------|------|----------|-------------|------------|
| phone | string | No | 聯繫電話 | nullable, regex:/^09\d{8}$\|^0\d-\d{7,8}$/ |
| email_public | string | No | 公開 Email | nullable, email, max:255 |
| line_id | string | No | LINE ID | nullable, min:3, max:20, regex:/^[a-zA-Z0-9_]+$/ |
| wechat_id | string | No | WeChat ID | nullable, min:6, max:20, regex:/^[a-zA-Z0-9_-]+$/ |
| contact_preferences | array | No | 聯繫偏好順序 | nullable, array, in:phone,email,line,wechat |

**Business Rules**:
- BR-VR-001: 至少要有一種聯繫方式（phone, email_public, line_id, wechat_id）

### Response (200 OK)

```json
{
  "success": true,
  "data": {
    "phone": "0912-345-678",
    "email_public": "contact@example.com",
    "line_id": "my_line_id",
    "wechat_id": "my_wechat",
    "contact_preferences": ["line", "phone", "email", "wechat"]
  },
  "message": "聯繫方式已更新"
}
```

### Error Responses

**422 Validation Error**:
```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "驗證失敗",
    "details": {
      "contact_methods": ["請至少提供一種聯繫方式"]
    }
  }
}
```

---

## GET /api/salespersons/{id}/contact-info

**Description**: 查詢業務員的公開聯繫資訊

**Authentication**: Not Required

**Rate Limit**: 60 requests/minute

### Request

```http
GET /api/salespersons/{id}/contact-info
```

### Path Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| id | integer | 業務員 ID |

### Response (200 OK)

```json
{
  "success": true,
  "data": {
    "phone": "0912-345-678",
    "email_public": "contact@example.com",
    "line_id": "my_line_id",
    "wechat_id": "my_wechat",
    "contact_preferences": ["line", "phone", "email", "wechat"]
  }
}
```

### Error Responses

**404 Not Found**:
```json
{
  "success": false,
  "error": {
    "code": "NOT_FOUND",
    "message": "業務員不存在或尚未通過審核"
  }
}
```

---

## POST /api/contact-requests

**Description**: 客戶提交聯繫請求給業務員

**Authentication**: Required (JWT)

**Rate Limit**: 5 requests/hour (IP-based)

**Business Logic Rate Limit**:
- 同一業務員: 24 小時內只能提交一次
- 每日上限: 5 次聯繫請求

### Request

```http
POST /api/contact-requests
Authorization: Bearer {access_token}
Content-Type: application/json
```

```json
{
  "salesperson_id": 123,
  "customer_phone": "0912345678",
  "message": "您好，我想了解保險相關服務"
}
```

### Request Fields

| Field | Type | Required | Description | Validation |
|-------|------|----------|-------------|------------|
| salesperson_id | integer | Yes | 業務員 ID | required, integer, exists:users,id, ApprovedSalespersonExists |
| customer_phone | string | No | 客戶手機 | nullable, regex:/^09\d{8}$/ |
| message | string | Yes | 訊息內容 | required, min:10, max:500 |

**Business Rules**:
- BR-VR-002: salesperson_id 必須是已通過審核的業務員
- BR-RL-002: 24小時內同一業務員只能聯繫一次
- BR-RL-003: 每日最多 5 次聯繫請求
- BR-SEC-001: message 會經過 XSS 防護 (strip_tags)

### Response (201 Created)

```json
{
  "success": true,
  "data": {
    "id": 1,
    "salesperson_id": 123,
    "customer_name": "王小明",
    "customer_email": "customer@example.com",
    "customer_phone": "0912345678",
    "message": "您好，我想了解保險相關服務",
    "status": "pending",
    "created_at": "2026-01-23T10:30:00.000000Z"
  },
  "message": "聯繫請求已送出，業務員將盡快回覆您"
}
```

### Error Responses

**429 Too Many Requests (Rate Limit - Same Salesperson)**:
```json
{
  "success": false,
  "message": "您在 24 小時內已聯繫過此業務員，請稍後再試"
}
```

**429 Too Many Requests (Rate Limit - Daily Limit)**:
```json
{
  "success": false,
  "message": "您今天已達到聯繫上限（5 次），請明天再試"
}
```

**422 Validation Error**:
```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "驗證失敗",
    "details": {
      "salesperson_id": ["選擇的業務員不存在或尚未通過審核"],
      "message": ["訊息至少需要 10 個字元"]
    }
  }
}
```

---

## POST /api/events/track

**Description**: 追蹤聯繫相關事件（用於分析）

**Authentication**: Not Required (支援匿名追蹤)

**Rate Limit**: 100 requests/minute

### Request

```http
POST /api/events/track
Content-Type: application/json
```

```json
{
  "event_type": "profile_view",
  "salesperson_id": 123
}
```

### Request Fields

| Field | Type | Required | Description | Validation |
|-------|------|----------|-------------|------------|
| event_type | string | Yes | 事件類型 | required, in:profile_view,contact_form_submission |
| salesperson_id | integer | Yes | 業務員 ID | required, integer, exists:users,id |

**Event Types**:
- `profile_view`: 瀏覽業務員檔案
- `contact_form_submission`: 提交聯繫表單

**Privacy**:
- IP 地址會經過 SHA256 hashing 後儲存
- 支援匿名追蹤（user_id 可為 null）

### Response (200 OK)

```json
{
  "success": true,
  "message": "事件已記錄"
}
```

### Error Responses

**422 Validation Error**:
```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "驗證失敗",
    "details": {
      "event_type": ["事件類型無效"],
      "salesperson_id": ["業務員不存在"]
    }
  }
}
```

---

## Security Features

### Data Protection

1. **Encryption**:
   - Customer email: AES-256-CBC encryption
   - Customer phone: AES-256-CBC encryption

2. **Privacy**:
   - IP addresses: SHA256 hashing
   - User agent: Stored as-is

3. **XSS Protection**:
   - Message content: `strip_tags()` applied

### Rate Limiting

1. **IP-based** (Middleware):
   - Contact requests: 5/hour
   - Contact info: 60/minute
   - Event tracking: 100/minute

2. **Business Logic** (Redis Cache):
   - Same salesperson: 24 hours cooldown
   - Daily limit: 5 requests per user

### Authentication

- JWT tokens required for:
  - Update contact methods
  - Submit contact requests
- Public endpoints:
  - Get contact info
  - Track events (optional auth)

---

## Email Notifications

### Contact Request Received

**Trigger**: 當客戶提交聯繫請求時

**Recipient**: 業務員 (salesperson email)

**Delivery**: Asynchronous via Queue

**Retry**: 3 attempts (backoff: 1min, 5min, 15min)

**Template**: `emails.contact-request-received`

**Content**:
```
新的客戶聯繫請求

客戶資訊：
- 姓名：{customer_name}
- Email：{customer_email}
- 電話：{customer_phone}

訊息內容：
{message}

[回覆客戶] (mailto link)
```

---

## Related Specifications

- **Data Model**: `openspec/changes/archived/20260122-add-contact-mechanism/specs/data-model.md`
- **Business Rules**: `openspec/changes/archived/20260122-add-contact-mechanism/specs/business-rules.md`
- **Architecture**: `openspec/changes/archived/20260122-add-contact-mechanism/specs/architecture.md`

