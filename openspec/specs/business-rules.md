# Business Rules Specification

**Project**: 業務推廣系統
**Last Updated**: 2026-01-08

本文件記錄系統所有業務規則，包括驗證邏輯、約束條件、權限控制等。

---

## Common Business Rules & Best Practices

**Last Updated**: 2026-01-21
**Source**: Extracted from production changes

### BR-100: API Response Unwrapping

**Rule**: Frontend hooks must unwrap nested API responses to prevent data access issues.

**Problem Pattern**:
```json
// Backend returns
{
  "success": true,
  "data": {
    "user": { "id": 1, "role": "salesperson" }
  }
}

// ❌ Bad: Frontend directly uses response.data
// Results in: { user: { id: 1, role: "salesperson" } }
// Accessing: user.role → undefined (because user is actually {user: {...}})
```

**Correct Implementation**:
```typescript
export function useAuth() {
  return useQuery({
    queryFn: async () => {
      const response = await getCurrentUser();
      const data = response.data as { user?: any } | any;
      return data?.user ?? data;  // Unwrap nested structure
    },
  });
}
```

**Rationale**: Prevents runtime errors when accessing nested properties like `user.role`.

**Related Changes**:
- 20260115-fix-header-dropdown-and-dashboard-access
- 20260115-fix-dashboard-profile-edit-prefill

---

### BR-101: Form Validation Consistency (Backend)

**Rule**: All request validation must use Form Request classes, not inline `Validator::make()`.

**Implementation**:
```php
// ❌ Bad: Inline validation
public function rejectSalesperson(Request $request, int $id) {
    $validator = Validator::make($request->all(), [
        'reason' => 'required|string|max:500'
    ]);
}

// ✅ Good: Form Request class
public function rejectSalesperson(RejectSalespersonRequest $request, int $id) {
    $reason = $request->validated('reason');
}
```

**Benefits**:
- Centralized validation logic
- Type-safe (PHPStan Level 9)
- Reusable across controllers
- Custom error messages

**Related Change**: 20260120-fix-backend-code-quality

---

### BR-102: Null Safety and Optional Chaining

**Rule**: Always use null-safe operators when accessing potentially null/undefined properties.

**Backend (PHP 8.0+)**:
```php
// ❌ Bad
$userName = $user->salespersonProfile->full_name;

// ✅ Good
$userName = $user->salespersonProfile?->full_name ?? 'Unknown';
```

**Frontend (TypeScript)**:
```typescript
// ❌ Bad
const initial = profile.full_name.substring(0, 2);

// ✅ Good
const initial = profile.full_name?.substring(0, 2) ?? 'U';
```

**Rationale**: Prevents runtime errors (TypeError, NullPointerException) when data is missing.

**Related Changes**:
- All bug fix changes
- 20260112-fix-avatar-fallback-typeerror

---

### BR-103: Rate Limiting by API Type

**Rule**: Apply different rate limits based on API type and authentication status.

**Configuration**:
| API Type | Rate Limit | Identifier |
|----------|------------|------------|
| Public (auth endpoints) | 60 req/min | IP address |
| Authenticated | 120 req/min | User ID |
| Admin | 300 req/min | Admin ID |

**Implementation**:
```php
RateLimiter::for('public-api', function (Request $request) {
    return Limit::perMinute(60)->by($request->ip());
});

RateLimiter::for('authenticated-api', function (Request $request) {
    return Limit::perMinute(120)->by(optional($request->user())->id ?: $request->ip());
});
```

**Error Response** (429 Too Many Requests):
```json
{
  "status": "error",
  "message": "Too Many Requests"
}
```

**Rationale**: Protect API from abuse while allowing legitimate heavy usage.

**Related Change**: 20260120-fix-backend-code-quality

---

### BR-104: Cache Strategy for Query Performance

**Rule**: Cache expensive queries with appropriate TTL based on data freshness requirements.

**Cache TTL Guidelines**:
| Data Type | TTL | Rationale |
|-----------|-----|-----------|
| Statistics (dashboard) | 5 minutes | Acceptable slight delay |
| List queries (salespersons) | 1 minute | Balance performance and freshness |
| Detail queries | 5 minutes | Less frequently updated |
| Search results | 30 seconds | Frequently changing |

**Implementation**:
```php
public function statistics(): JsonResponse
{
    $stats = Cache::remember('admin:statistics', 300, function () {
        return [
            'total_salespeople' => User::salespersons()->count(),
            // ... other stats
        ];
    });

    return response()->json(['data' => $stats]);
}
```

**Cache Invalidation**:
```php
// Clear cache when relevant data changes
public function updateProfile(Request $request): JsonResponse
{
    $profile->update($request->validated());

    // Clear related caches
    Cache::forget('salespersons:list:*');
    Cache::forget('admin:statistics');

    return response()->json(['success' => true]);
}
```

**Rationale**: Reduce database load for frequently accessed, slowly changing data.

**Related Change**: 20260120-fix-backend-code-quality

---

### BR-105: Company Search Strategy

**Rule**: Support both exact match (tax_id) and fuzzy search (name) in a single endpoint.

**Search Logic**:
1. If input matches 8-digit pattern → Exact match on `tax_id`
2. Otherwise → Fuzzy search on `name` (LIKE %keyword%)
3. Limit results to 10 items
4. Return different response formats based on search type

**Implementation**:
```php
public function search(Request $request): JsonResponse
{
    $keyword = $request->query('keyword');

    // Exact tax_id search
    if (preg_match('/^\d{8}$/', $keyword)) {
        $company = Company::where('tax_id', $keyword)->first();
        return response()->json([
            'exists' => (bool) $company,
            'company' => $company
        ]);
    }

    // Fuzzy name search
    $companies = Company::where('name', 'LIKE', "%{$keyword}%")
        ->limit(10)
        ->get();

    return response()->json([
        'exists' => $companies->isNotEmpty(),
        'companies' => $companies
    ]);
}
```

**Rationale**: Allow users to search by either name (user-friendly) or tax_id (precise).

**Related Change**: 20260118-improve-company-selection

---

### BR-106: Confirmation Dialog for Data Loss

**Rule**: Show confirmation dialog when user actions will clear existing unsaved data.

**When to Apply**:
- Switching between mutually exclusive options (e.g., "Company" ↔ "Self-employed")
- Navigating away from forms with unsaved changes
- Destructive actions (delete, reset)

**Implementation**:
```tsx
const handleTypeChange = (newType: 'company' | 'self') => {
  // Check if user has filled data
  const hasData = (employmentType === 'company' && selectedCompany) ||
                  (employmentType === 'self' && businessName);

  if (hasData) {
    // Show confirmation dialog
    setShowConfirm(true);
    setPendingType(newType);
  } else {
    // Direct switch (no data loss)
    setEmploymentType(newType);
  }
};
```

**Dialog Content**:
```
⚠️ 確認切換為自營業者？

切換後，您先前選擇的公司資訊將被清除。

[取消]  [確認切換]
```

**Rationale**: Prevent accidental data loss and improve user trust.

**Related Change**: 20260118-improve-company-selection

---

### BR-107: Form Pre-filling Dependency Management

**Rule**: When pre-filling edit forms, include both data and edit mode in useEffect dependencies.

**Problem Pattern**:
```tsx
// ❌ Bad: Only monitors profile.id
useEffect(() => {
  if (profile) {
    resetForm(profile);
  }
}, [profile?.id]);  // Won't re-run when entering edit mode
```

**Correct Implementation**:
```tsx
// ✅ Good: Monitors both profile and editMode
useEffect(() => {
  if (profile && editMode) {
    resetForm({
      full_name: profile.full_name || '',
      phone: profile.phone || '',
      // ... other fields
    });
  }
}, [profile?.id, editMode]);  // Re-runs when entering edit mode
```

**Additional Rule**: Disable edit button during data loading.
```tsx
<Button
  onClick={() => setEditMode(true)}
  disabled={profileLoading}  // Prevent editing before data loads
>
  編輯資料
</Button>
```

**Rationale**: Ensure form fields are always populated when entering edit mode.

**Related Change**: 20260115-fix-dashboard-profile-edit-prefill

---

### BR-108: Avatar Fallback Priority

**Rule**: Generate avatar fallback text using a 5-tier priority system.

**Priority Order**:
1. `full_name` (e.g., "張小明" → "張小")
2. `name` (e.g., "John" → "JO")
3. `username` (e.g., "john_doe" → "JO")
4. `email` (e.g., "john@example.com" → "JO")
5. Default: "U"

**Implementation**:
```typescript
export function getAvatarFallback(user: {
  full_name?: string | null;
  name?: string | null;
  username?: string | null;
  email?: string | null;
}): string {
  const fullName = user.full_name?.trim();
  if (fullName && fullName.length >= 2) {
    return fullName.substring(0, 2).toUpperCase();
  }

  // Try name, username, email...
  // (See full implementation in ui-components.md)

  return 'U';  // Final fallback
}
```

**Usage**:
```tsx
<Avatar
  src={user.avatar}
  fallback={getAvatarFallback(user)}
  size="lg"
/>
```

**Rationale**: Provide consistent, meaningful fallback text across all components.

**Related Change**: 20260112-fix-avatar-fallback-typeerror

---

### BR-109: Skeleton Loading States

**Rule**: Use skeleton screens instead of spinners for content loading.

**When to Use**:
- List/grid content loading
- Card content loading
- Form loading states
- Timeline loading

**Implementation**:
```tsx
{isLoading ? (
  <div className="space-y-4">
    {[1, 2, 3].map((i) => (
      <Card key={i} className="p-6">
        <Skeleton className="h-5 w-48 mb-3" />
        <Skeleton className="h-4 w-32 mb-2" />
        <Skeleton className="h-4 w-full" />
      </Card>
    ))}
  </div>
) : (
  <ContentList data={data} />
)}
```

**Benefits**:
- Better perceived performance
- Users see layout structure immediately
- Reduces Cumulative Layout Shift (CLS)

**Rationale**: Improve user experience during data loading.

**Related Change**: 20260120-enhance-salesperson-experience-certifications-ui

---

### BR-110: Role-Based Navigation Links

**Rule**: Generate navigation menu links dynamically based on user role.

**Role-Link Mapping**:
| Role | Links |
|------|-------|
| Admin | 管理後台, 使用者管理, 統計資料 |
| Salesperson | 個人中心, 工作經驗, 專業證照 |
| User | 首頁, 搜尋業務員 |

**Implementation**:
```tsx
const getDashboardLinks = () => {
  if (user?.role === 'admin') return adminLinks;
  if (user?.role === 'salesperson') return salespersonLinks;
  return userLinks;  // Regular users
};
```

**Error Handling**: If role is undefined, fall back to user links (prevent empty menu).

**Rationale**: Provide role-appropriate navigation without hardcoding conditional logic everywhere.

**Related Change**: 20260115-fix-header-dropdown-and-dashboard-access

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

---

# Contact Mechanism Business Rules

**Feature**: Contact Mechanism (聯繫機制)  
**Version**: 1.0  
**Last Updated**: 2026-01-23

## Overview

Contact Mechanism 功能的業務規則定義，涵蓋驗證、rate limiting、安全性、資料完整性和 email 通知。

---

## BR-VR: Validation Rules (驗證規則)

### BR-VR-001: 至少一種聯繫方式
**Description**: 業務員更新聯繫方式時，至少要提供一種聯繫方法

**Applies to**:
- PUT /api/salesperson/profile/contact

**Rule**:
```
至少以下一項不為 null:
- phone
- email_public
- line_id
- wechat_id
```

**Implementation**:
- Custom Validation Rule: `AtLeastOneContactMethod`
- Error Message: "請至少提供一種聯繫方式（電話、Email、LINE ID 或 WeChat ID）"

**Test Cases**:
- ✅ 只提供 phone → 通過
- ✅ 提供 phone + email → 通過
- ❌ 全部為 null → 失敗

---

### BR-VR-002: 業務員審核狀態驗證
**Description**: 只有已通過審核的業務員可以接收聯繫請求

**Applies to**:
- POST /api/contact-requests (salesperson_id validation)

**Rule**:
```
salesperson_id 必須滿足:
- User exists
- role = 'salesperson'
- salesperson_status = 'approved'
```

**Implementation**:
- Custom Validation Rule: `ApprovedSalespersonExists`
- Error Message: "選擇的業務員不存在或尚未通過審核"

**Test Cases**:
- ✅ Approved salesperson → 通過
- ❌ Pending salesperson → 失敗
- ❌ Rejected salesperson → 失敗
- ❌ Regular user → 失敗

---

## BR-RL: Rate Limiting Rules (頻率限制規則)

### BR-RL-001: IP-based Rate Limiting
**Description**: IP 層級的請求頻率限制（Laravel Throttle Middleware）

**Applies to**:
- POST /api/contact-requests: 5 requests/hour
- GET /api/salespersons/{id}/contact-info: 60 requests/minute
- POST /api/events/track: 100 requests/minute

**Implementation**:
```php
Route::middleware('throttle:5,60') // 5 requests per 60 minutes
```

**Response** (429 Too Many Requests):
```json
{
  "message": "Too Many Attempts."
}
```

---

### BR-RL-002: 同一業務員 24 小時限制
**Description**: 同一用戶對同一業務員，24 小時內只能提交一次聯繫請求

**Applies to**:
- POST /api/contact-requests

**Rule**:
```
IF 過去 24 小時內，同一 user_id + salesperson_id 組合已有請求
THEN 拒絕新請求
```

**Implementation**:
- Service: `RateLimitService::canContactSalesperson()`
- Storage: Redis Cache
- Key: `contact_limit:{user_id}:{salesperson_id}`
- TTL: 24 hours

**Response** (429):
```json
{
  "success": false,
  "message": "您在 24 小時內已聯繫過此業務員，請稍後再試"
}
```

**Test Cases**:
- ✅ 首次聯繫 → 成功
- ❌ 24小時內第二次聯繫同一業務員 → 失敗
- ✅ 24小時內聯繫不同業務員 → 成功
- ✅ 24小時後再次聯繫 → 成功

---

### BR-RL-003: 每日 5 次聯繫上限
**Description**: 每個用戶每天最多提交 5 次聯繫請求（跨所有業務員）

**Applies to**:
- POST /api/contact-requests

**Rule**:
```
IF 今日（UTC）已提交 5 次請求
THEN 拒絕新請求
```

**Implementation**:
- Service: `RateLimitService::canSubmitToday()`
- Storage: Redis Cache
- Key: `daily_contact_count:{user_id}:{date}`
- TTL: End of day (UTC)

**Response** (429):
```json
{
  "success": false,
  "message": "您今天已達到聯繫上限（5 次），請明天再試"
}
```

**Test Cases**:
- ✅ 第 1-5 次請求 → 成功
- ❌ 第 6 次請求 → 失敗
- ✅ 隔天第 1 次請求 → 成功

---

## BR-SEC: Security Rules (安全規則)

### BR-SEC-001: XSS 防護
**Description**: 使用者輸入的訊息內容必須經過 XSS 防護

**Applies to**:
- POST /api/contact-requests (message field)

**Rule**:
```php
$sanitized_message = strip_tags($message);
```

**Implementation**:
- Service: `ContactRequestService::createRequest()`
- Function: `strip_tags()`

**Test Cases**:
- Input: `"Hello <script>alert('XSS')</script>"`
- Output: `"Hello alert('XSS')"`

---

## BR-DI: Data Integrity Rules (資料完整性規則)

### BR-DI-001: Customer Email 加密
**Description**: 客戶 email 使用 AES-256-CBC 加密儲存

**Applies to**:
- contact_requests.customer_email

**Implementation**:
- Model: `ContactRequest`
- Cast: `'customer_email' => 'encrypted'`
- Encryption: Laravel's built-in encryption (AES-256-CBC)

---

### BR-DI-002: Customer Phone 加密
**Description**: 客戶 phone 使用 AES-256-CBC 加密儲存

**Applies to**:
- contact_requests.customer_phone

**Implementation**:
- Model: `ContactRequest`
- Cast: `'customer_phone' => 'encrypted'`
- Encryption: Laravel's built-in encryption (AES-256-CBC)

---

### BR-DI-003: IP 地址 Hashing
**Description**: IP 地址使用 SHA256 hashing 儲存（隱私保護）

**Applies to**:
- contact_events.ip_address_hash

**Implementation**:
```php
$ip_hash = hash('sha256', $ip_address);
```

**Reason**: 
- 保護使用者隱私
- 仍可用於去重和分析
- 不可逆向還原 IP

---

### BR-DI-004: Event 不可變性
**Description**: Contact Event 記錄是不可變的（immutable）

**Applies to**:
- contact_events table

**Implementation**:
```php
// ContactEvent Model
public const UPDATED_AT = null;
```

**Reason**:
- 事件記錄用於分析，不應被修改
- 只有 created_at，沒有 updated_at

---

## BR-EM: Email Rules (Email 規則)

### BR-EM-001: 非同步 Email 發送
**Description**: Email 通知使用 Queue 非同步發送

**Applies to**:
- ContactRequestReceived email

**Implementation**:
- Queue: Redis
- Job: `SendContactRequestEmail`
- Dispatch: `SendContactRequestEmail::dispatch($contactRequest)`

**Retry Policy**:
- Attempts: 3
- Backoff: [60, 300, 900] (1min, 5min, 15min)

**Failure Handling**:
```php
public function failed(\Throwable $exception): void
{
    Log::error('Failed to send contact request email', [
        'contact_request_id' => $this->contactRequest->id,
        'error' => $exception->getMessage(),
    ]);
}
```

---

## BR-TR: Tracking Rules (追蹤規則)

### BR-TR-001: 匿名追蹤支援
**Description**: Event tracking 支援匿名使用者

**Applies to**:
- POST /api/events/track

**Rule**:
```
user_id 可為 null（代表匿名使用者）
```

**Use Cases**:
- 未登入使用者瀏覽業務員檔案
- 追蹤匿名流量

---

### BR-TR-002: IP Hashing for Privacy
**Description**: 追蹤事件時，IP 地址必須經過 hashing

**Applies to**:
- All contact_events records

**Implementation**:
```php
ContactEvent::track($eventType, $salespersonId, $userId);
// Internally hashes IP with SHA256
```

---

## Related Specifications

- **API Specs**: `openspec/specs/api/endpoints.md#contact-mechanism-api`
- **Data Model**: `openspec/changes/archived/20260122-add-contact-mechanism/specs/data-model.md`
- **Architecture**: `openspec/changes/archived/20260122-add-contact-mechanism/specs/architecture.md`

