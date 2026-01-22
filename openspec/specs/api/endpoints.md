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
**Access**: Protected (salesperson role)
**Response (200)**:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "user_id": 10,
      "company": "ABC公司",
      "position": "業務經理",
      "start_date": "2020-01-01",
      "end_date": "2023-12-31",
      "description": "管理團隊，達成120%業績目標",
      "approval_status": "approved",
      "rejected_reason": null,
      "sort_order": 0,
      "created_at": "2026-01-10T12:00:00Z",
      "updated_at": "2026-01-10T12:00:00Z"
    }
  ],
  "message": "Experiences retrieved successfully"
}
```

### POST /salesperson/experiences
**Description**: Create new work experience
**Access**: Protected (salesperson role)
**Request Body**:
```json
{
  "company": "string (required, max:200)",
  "position": "string (required, max:200)",
  "start_date": "YYYY-MM-DD (required)",
  "end_date": "YYYY-MM-DD (optional, must be after start_date)",
  "description": "string (optional)"
}
```
**Response (201)**:
```json
{
  "success": true,
  "data": {
    "id": 3,
    "user_id": 10,
    "company": "ABC公司",
    "position": "業務經理",
    "start_date": "2020-01-01",
    "end_date": "2023-12-31",
    "description": "...",
    "approval_status": "approved",
    "sort_order": 0,
    "created_at": "2026-01-11T10:00:00Z",
    "updated_at": "2026-01-11T10:00:00Z"
  },
  "message": "Experience created successfully"
}
```

### PUT /salesperson/experiences/{id}
**Description**: Update work experience
**Access**: Protected (salesperson role, owner only)
**Request Body**: Same as POST
**Response (200)**:
```json
{
  "success": true,
  "data": {
    "id": 1,
    "company": "Updated Company",
    "position": "Updated Position",
    ...
  },
  "message": "Experience updated successfully"
}
```

### DELETE /salesperson/experiences/{id}
**Description**: Delete work experience
**Access**: Protected (salesperson role, owner only)
**Response (200)**:
```json
{
  "success": true,
  "message": "Experience deleted successfully"
}
```

### GET /salesperson/certifications
**Description**: Get own certifications
**Access**: Protected (salesperson role)
**Response (200)**:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "user_id": 10,
      "name": "Certified Sales Professional",
      "issuer": "Sales Institute",
      "issue_date": "2022-01-15",
      "expiry_date": "2025-01-15",
      "description": "Advanced sales certification",
      "file_path": "certifications/abc123.pdf",
      "file_url": "https://example.com/storage/certifications/abc123.pdf",
      "file_size": 1024000,
      "approval_status": "approved",
      "rejected_reason": null,
      "created_at": "2026-01-10T12:00:00Z",
      "updated_at": "2026-01-10T12:00:00Z"
    }
  ],
  "message": "Certifications retrieved successfully"
}
```

### POST /salesperson/certifications
**Description**: Upload certification (requires approval)
**Access**: Protected (salesperson role)
**Request Body**:
```json
{
  "name": "string (required, max:200)",
  "issuer": "string (required, max:200)",
  "issue_date": "YYYY-MM-DD (required)",
  "expiry_date": "YYYY-MM-DD (optional)",
  "description": "string (optional)",
  "file_data": "base64 encoded file (optional, JPG/PNG/PDF, max 5MB)"
}
```
**Response (201)**:
```json
{
  "success": true,
  "data": {
    "id": 2,
    "user_id": 10,
    "name": "Certified Sales Professional",
    "issuer": "Sales Institute",
    "issue_date": "2022-01-15",
    "expiry_date": "2025-01-15",
    "file_path": "certifications/xyz789.pdf",
    "file_url": "https://example.com/storage/certifications/xyz789.pdf",
    "approval_status": "pending",
    "created_at": "2026-01-11T10:00:00Z",
    "updated_at": "2026-01-11T10:00:00Z"
  },
  "message": "Certification created successfully and pending approval"
}
```

### DELETE /salesperson/certifications/{id}
**Description**: Delete certification
**Access**: Protected (salesperson role, owner only)
**Response (200)**:
```json
{
  "success": true,
  "message": "Certification deleted successfully"
}
```

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

## Complete Endpoint List (37 total)

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
