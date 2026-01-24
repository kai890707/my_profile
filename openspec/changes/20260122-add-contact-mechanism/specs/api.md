# API 規格文檔

**功能**: 聯繫機制功能（Contact Mechanism）
**版本**: 1.0
**最後更新**: 2026-01-23

---

## 概述

本文檔定義「聯繫機制功能」的所有 API 端點，包含：
1. 業務員更新聯繫方式
2. 客戶提交聯繫請求
3. 查詢業務員聯繫資訊
4. 追蹤聯繫事件
5. Admin 管理聯繫請求

---

## 認證機制

所有需要認證的 API 使用 JWT 雙令牌機制：

```http
Authorization: Bearer {access_token}
```

**Token 過期時間**:
- Access Token: 1 小時
- Refresh Token: 7 天

**角色定義**:
- `user`: 一般用戶（客戶）
- `salesperson`: 業務員（已註冊且 approved）
- `admin`: 管理員

---

## API 端點總覽

| 端點 | 方法 | 認證 | 角色 | 說明 |
|------|------|------|------|------|
| `/api/salesperson/profile/contact` | PUT | ✅ | salesperson | 更新聯繫方式 |
| `/api/contact-requests` | POST | ✅ | user, salesperson | 提交聯繫請求 |
| `/api/salesperson/{id}/contact-info` | GET | ❌ | - | 查詢聯繫資訊 |
| `/api/events/track` | POST | ❌ | - | 追蹤事件 |
| `/api/admin/contact-requests` | GET | ✅ | admin | 查詢聯繫請求列表 |

---

## API 詳細規格

### 1. 業務員更新聯繫方式

**端點**: `PUT /api/salesperson/profile/contact`

**描述**: 業務員更新個人聯繫方式（電話、Email、LINE、WeChat）

**認證**: Required (JWT, salesperson role)

**權限**: 業務員只能更新自己的聯繫方式

#### 請求參數

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

#### 請求欄位說明

| 欄位 | 型別 | 必填 | 說明 | 驗證規則 |
|------|------|------|------|---------|
| phone | string | 否 | 聯繫電話 | nullable, regex:/^09\d{8}$\|^0\d-\d{7,8}$/ |
| email_public | string | 否 | 公開 Email | nullable, email, max:255 |
| line_id | string | 否 | LINE ID | nullable, string, min:3, max:20, regex:/^[a-zA-Z0-9_]+$/ |
| wechat_id | string | 否 | WeChat ID | nullable, string, min:6, max:20, regex:/^[a-zA-Z0-9_-]+$/ |
| contact_preferences | array | 否 | 聯繫偏好順序 | nullable, array, in:phone,email,line,wechat |

#### 驗證規則詳細說明

**Phone 驗證**:
```
✅ 合法格式:
- 0912-345-678 (手機)
- 0912345678 (手機)
- 02-12345678 (市話)
- 04-1234567 (市話)

❌ 不合法格式:
- 12345678 (缺少區碼)
- +886912345678 (不支援國際格式)
```

**LINE ID 驗證**:
```
✅ 合法格式:
- abc123 (3-20 字元)
- my_line_id (允許底線)

❌ 不合法格式:
- ab (少於 3 字元)
- line@123 (不允許 @)
- line-id (不允許減號)
```

**WeChat ID 驗證**:
```
✅ 合法格式:
- abc123 (6-20 字元)
- my_wechat (允許底線)
- wechat-id (允許減號)

❌ 不合法格式:
- abc12 (少於 6 字元)
- wechat@123 (不允許 @)
```

**Custom Validation Rules**:
- **BR-001**: 至少提供一種聯繫方式（phone, email_public, line_id, wechat_id 至少一個非空）

#### Laravel Validation 實作範例

```php
// app/Http/Requests/UpdateContactMethodsRequest.php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateContactMethodsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->role === 'salesperson';
    }

    public function rules(): array
    {
        return [
            'phone' => ['nullable', 'string', 'regex:/^09\d{8}$|^0\d-\d{7,8}$/'],
            'email_public' => ['nullable', 'email', 'max:255'],
            'line_id' => ['nullable', 'string', 'min:3', 'max:20', 'regex:/^[a-zA-Z0-9_]+$/'],
            'wechat_id' => ['nullable', 'string', 'min:6', 'max:20', 'regex:/^[a-zA-Z0-9_-]+$/'],
            'contact_preferences' => ['nullable', 'array'],
            'contact_preferences.*' => ['string', 'in:phone,email,line,wechat'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $data = $this->all();
            $hasContact = !empty($data['phone']) ||
                         !empty($data['email_public']) ||
                         !empty($data['line_id']) ||
                         !empty($data['wechat_id']);

            if (!$hasContact) {
                $validator->errors()->add(
                    'contact_methods',
                    '必須至少提供一種聯繫方式（電話、Email、LINE 或 WeChat）'
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'phone.regex' => '電話格式不正確（手機: 0912345678 或市話: 02-12345678）',
            'email_public.email' => 'Email 格式不正確',
            'line_id.regex' => 'LINE ID 格式不正確（3-20 字元，僅允許英數字和底線）',
            'line_id.min' => 'LINE ID 最少 3 個字元',
            'wechat_id.regex' => 'WeChat ID 格式不正確（6-20 字元，僅允許英數字、底線和減號）',
            'wechat_id.min' => 'WeChat ID 最少 6 個字元',
        ];
    }
}
```

#### 成功回應 (200 OK)

```json
{
  "status": "success",
  "message": "聯繫方式已更新",
  "data": {
    "profile": {
      "id": 1,
      "user_id": 5,
      "phone": "0912-345-678",
      "email_public": "contact@example.com",
      "line_id": "my_line_id",
      "wechat_id": "my_wechat",
      "contact_preferences": ["line", "phone", "email", "wechat"],
      "updated_at": "2026-01-23T14:30:00Z"
    }
  }
}
```

#### 錯誤回應

**401 Unauthorized** (未登入或 Token 過期)
```json
{
  "status": "error",
  "message": "未經授權，請先登入",
  "errors": {}
}
```

**403 Forbidden** (非 salesperson 角色)
```json
{
  "status": "error",
  "message": "僅業務員可以更新聯繫方式",
  "errors": {}
}
```

**422 Unprocessable Entity** (驗證失敗)
```json
{
  "status": "error",
  "message": "驗證失敗",
  "errors": {
    "phone": ["電話格式不正確（手機: 0912345678 或市話: 02-12345678）"],
    "line_id": ["LINE ID 最少 3 個字元"],
    "contact_methods": ["必須至少提供一種聯繫方式（電話、Email、LINE 或 WeChat）"]
  }
}
```

#### 業務規則

- **BR-001**: 至少提供一種聯繫方式
- **BR-002**: 業務員只能編輯自己的聯繫方式

#### 效能要求

- **回應時間**: P95 < 200ms
- **併發支援**: 50 req/s

---

### 2. 客戶提交聯繫請求

**端點**: `POST /api/contact-requests`

**描述**: 已登入客戶透過站內表單聯繫業務員

**認證**: Required (JWT, user 或 salesperson role)

**Rate Limiting**:
- IP: 5 requests/hour (Laravel Throttle Middleware)
- Business Logic: 同業務員 24h 內 1 次，每天最多 5 次

#### 請求參數

```http
POST /api/contact-requests
Authorization: Bearer {access_token}
Content-Type: application/json
```

```json
{
  "salesperson_id": 2,
  "phone": "0987-654-321",
  "message": "您好，我想詢問保險相關服務，請問方便聯繫嗎？"
}
```

#### 請求欄位說明

| 欄位 | 型別 | 必填 | 說明 | 驗證規則 |
|------|------|------|------|---------|
| salesperson_id | integer | 是 | 業務員 ID | required, exists:users,id |
| phone | string | 否 | 客戶電話 | nullable, string, max:20 |
| message | string | 是 | 訊息內容 | required, string, min:10, max:500 |

#### 自動填充欄位

以下欄位由後端自動從 Auth User 取得，前端不需傳遞：

| 欄位 | 來源 | 說明 |
|------|------|------|
| user_id | auth()->id() | 登入用戶 ID |
| customer_name | auth()->user()->name | 客戶姓名 |
| customer_email | auth()->user()->email | 客戶 Email |

#### Laravel Validation 實作範例

```php
// app/Http/Requests/StoreContactRequestRequest.php
<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Models\ContactRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Carbon\Carbon;

class StoreContactRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'salesperson_id' => ['required', 'integer', 'exists:users,id'],
            'phone' => ['nullable', 'string', 'max:20'],
            'message' => ['required', 'string', 'min:10', 'max:500'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $salespersonId = $this->input('salesperson_id');
            $userId = auth()->id();

            // BR-003: 檢查業務員是否為 approved 狀態
            $salesperson = User::with('salespersonProfile')
                ->where('id', $salespersonId)
                ->where('role', 'salesperson')
                ->first();

            if (!$salesperson ||
                !$salesperson->salespersonProfile ||
                $salesperson->salespersonProfile->approval_status !== 'approved') {
                $validator->errors()->add(
                    'salesperson_id',
                    '無法聯繫此業務員，業務員不存在或尚未通過審核'
                );
                return;
            }

            // BR-004: 檢查 24 小時內是否已聯繫過此業務員
            $existingRequest = ContactRequest::where('user_id', $userId)
                ->where('salesperson_id', $salespersonId)
                ->where('created_at', '>=', Carbon::now()->subHours(24))
                ->exists();

            if ($existingRequest) {
                $validator->errors()->add(
                    'salesperson_id',
                    '您已在 24 小時內聯繫過此業務員，請稍後再試'
                );
                return;
            }

            // BR-005: 檢查今天是否已聯繫 5 次（跨業務員）
            $todayCount = ContactRequest::where('user_id', $userId)
                ->whereDate('created_at', Carbon::today())
                ->count();

            if ($todayCount >= 5) {
                $validator->errors()->add(
                    'rate_limit',
                    '您今日的聯繫次數已達上限（每天最多 5 次），請明天再試'
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'salesperson_id.required' => '請選擇要聯繫的業務員',
            'salesperson_id.exists' => '業務員不存在',
            'message.required' => '請填寫訊息內容',
            'message.min' => '訊息內容最少 10 個字元',
            'message.max' => '訊息內容最多 500 個字元',
        ];
    }
}
```

#### 成功回應 (201 Created)

```json
{
  "status": "success",
  "message": "聯繫請求已送出，業務員將盡快回覆您",
  "data": {
    "contact_request": {
      "id": 123,
      "salesperson_id": 2,
      "customer_name": "王小明",
      "customer_email": "user@example.com",
      "customer_phone": "0987-654-321",
      "message": "您好，我想詢問保險相關服務，請問方便聯繫嗎？",
      "status": "pending",
      "created_at": "2026-01-22T14:30:00Z"
    }
  }
}
```

#### 錯誤回應

**401 Unauthorized** (未登入)
```json
{
  "status": "error",
  "message": "未經授權，請先登入",
  "errors": {}
}
```

**422 Unprocessable Entity** (驗證失敗)

```json
{
  "status": "error",
  "message": "驗證失敗",
  "errors": {
    "salesperson_id": ["您已在 24 小時內聯繫過此業務員，請稍後再試"],
    "message": ["訊息內容最少 10 個字元"]
  }
}
```

**429 Too Many Requests** (IP 頻率限制)

```json
{
  "status": "error",
  "message": "您的操作過於頻繁，請稍後再試",
  "errors": {
    "rate_limit": ["每小時最多提交 5 次"]
  }
}
```

#### Side Effects（副作用）

此 API 成功執行後會觸發以下動作：

1. **建立 ContactRequest 記錄**
   - 儲存到 `contact_requests` 資料表
   - IP 位址 hash 後儲存（SHA256）

2. **追蹤聯繫事件**
   - 記錄 `contact_form_submission` 事件到 `contact_events` 資料表

3. **發送 Email 通知業務員（非同步 Queue）**
   - Dispatch `SendContactRequestNotification` Job
   - Job 會發送 Email 給業務員

#### 業務規則

- **BR-003**: 必須登入才能提交聯繫請求
- **BR-004**: 同業務員 24 小時內只能聯繫 1 次
- **BR-005**: 每天最多提交 5 次聯繫請求（跨業務員）
- **BR-006**: 只有 approved 業務員接受聯繫請求

#### 效能要求

- **回應時間**: P95 < 500ms (含 Queue dispatch)
- **併發支援**: 100 req/s

---

### 3. 查詢業務員聯繫資訊

**端點**: `GET /api/salesperson/{id}/contact-info`

**描述**: 查詢特定業務員的聯繫資訊（公開端點，可匿名查看）

**認證**: Optional (可匿名查看)

**授權**: 只能查看 approved 業務員

#### 請求參數

```http
GET /api/salesperson/2/contact-info
Authorization: Bearer {access_token} (optional)
```

**Path Parameters**:
| 參數 | 型別 | 必填 | 說明 |
|------|------|------|------|
| id | integer | 是 | 業務員 user_id |

#### 成功回應 (200 OK)

**有聯繫方式的業務員**:
```json
{
  "status": "success",
  "data": {
    "contact_info": {
      "phone": "0912-345-678",
      "email_public": "contact@example.com",
      "line_id": "my_line_id",
      "wechat_id": "my_wechat",
      "contact_preferences": ["line", "phone", "email", "wechat"],
      "has_contact_methods": true
    }
  }
}
```

**無聯繫方式的業務員**:
```json
{
  "status": "success",
  "data": {
    "contact_info": {
      "phone": null,
      "email_public": null,
      "line_id": null,
      "wechat_id": null,
      "contact_preferences": null,
      "has_contact_methods": false
    }
  }
}
```

#### 錯誤回應

**404 Not Found** (業務員不存在或未 approved)
```json
{
  "status": "error",
  "message": "業務員不存在或尚未通過審核",
  "errors": {}
}
```

#### 業務規則

- 僅回傳 approved 業務員的聯繫方式
- pending/rejected 業務員回傳 404

#### 效能要求

- **回應時間**: P95 < 100ms
- **快取策略**: Redis Cache, TTL = 5 minutes

---

### 4. 追蹤聯繫事件

**端點**: `POST /api/events/track`

**描述**: 追蹤用戶與業務員的互動事件（瀏覽檔案、提交表單）

**認證**: Optional (可匿名追蹤)

#### 請求參數

```http
POST /api/events/track
Authorization: Bearer {access_token} (optional)
Content-Type: application/json
```

```json
{
  "event_type": "profile_view",
  "salesperson_id": 2
}
```

#### 請求欄位說明

| 欄位 | 型別 | 必填 | 說明 | 驗證規則 |
|------|------|------|------|---------|
| event_type | string | 是 | 事件類型 | required, in:profile_view,contact_form_submission |
| salesperson_id | integer | 是 | 業務員 ID | required, exists:users,id |

#### 自動收集欄位

| 欄位 | 來源 | 說明 |
|------|------|------|
| user_id | auth()->id() | 登入用戶 ID（未登入為 null） |
| ip_address_hash | hash('sha256', request()->ip()) | IP 位址 Hash (SHA256) |
| user_agent | request()->userAgent() | User Agent |

#### Laravel Validation 實作範例

```php
// app/Http/Requests/TrackEventRequest.php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TrackEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // 允許匿名追蹤
    }

    public function rules(): array
    {
        return [
            'event_type' => ['required', 'string', 'in:profile_view,contact_form_submission'],
            'salesperson_id' => ['required', 'integer', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'event_type.required' => '請提供事件類型',
            'event_type.in' => '事件類型不正確',
            'salesperson_id.required' => '請提供業務員 ID',
            'salesperson_id.exists' => '業務員不存在',
        ];
    }
}
```

#### 成功回應 (201 Created)

```json
{
  "status": "success",
  "message": "事件已記錄"
}
```

#### 錯誤回應

**422 Unprocessable Entity** (驗證失敗)
```json
{
  "status": "error",
  "message": "驗證失敗",
  "errors": {
    "event_type": ["事件類型不正確"],
    "salesperson_id": ["業務員不存在"]
  }
}
```

#### Side Effects（副作用）

- 記錄事件到 `contact_events` 資料表
- IP 位址使用 SHA256 hash 後儲存

#### 效能要求

- **回應時間**: P95 < 100ms
- **寫入效能**: 異步寫入（可選）

---

### 5. Admin 查詢聯繫請求列表

**端點**: `GET /api/admin/contact-requests`

**描述**: Admin 查詢所有聯繫請求記錄（分頁、篩選、排序）

**認證**: Required (JWT, admin role)

#### 請求參數

```http
GET /api/admin/contact-requests?page=1&per_page=20&salesperson_id=2&sort=created_at&order=desc
Authorization: Bearer {access_token}
```

**Query Parameters**:
| 參數 | 型別 | 必填 | 預設值 | 說明 |
|------|------|------|-------|------|
| page | integer | 否 | 1 | 頁碼 |
| per_page | integer | 否 | 20 | 每頁筆數（最大 100） |
| salesperson_id | integer | 否 | - | 篩選特定業務員 |
| user_id | integer | 否 | - | 篩選特定客戶 |
| status | string | 否 | - | 篩選狀態（pending, contacted, closed） |
| sort | string | 否 | created_at | 排序欄位 |
| order | string | 否 | desc | 排序方向（asc, desc） |

#### 成功回應 (200 OK)

```json
{
  "status": "success",
  "data": [
    {
      "id": 456,
      "salesperson": {
        "id": 2,
        "name": "張業務",
        "email": "salesperson@example.com"
      },
      "customer": {
        "id": 5,
        "name": "王客戶",
        "email": "customer@example.com",
        "phone": "0912-345-678"
      },
      "message": "我想了解保險規劃...",
      "status": "pending",
      "created_at": "2026-01-23T10:30:00Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 100,
    "last_page": 5
  }
}
```

#### 錯誤回應

**401 Unauthorized** (未登入)
```json
{
  "status": "error",
  "message": "未經授權，請先登入",
  "errors": {}
}
```

**403 Forbidden** (非 admin 角色)
```json
{
  "status": "error",
  "message": "僅管理員可以查看此資源",
  "errors": {}
}
```

#### 效能要求

- **回應時間**: P95 < 300ms
- **併發支援**: 50 req/s

---

## 錯誤處理標準

所有 API 錯誤回應遵循統一格式：

```json
{
  "status": "error",
  "message": "錯誤訊息摘要",
  "errors": {
    "field_name": ["具體錯誤原因"]
  }
}
```

### HTTP 狀態碼使用標準

| 狀態碼 | 意義 | 使用情境 |
|-------|------|---------|
| 200 | OK | 成功取得資源 |
| 201 | Created | 成功建立資源 |
| 400 | Bad Request | 請求格式錯誤 |
| 401 | Unauthorized | 未認證（未登入或 Token 過期） |
| 403 | Forbidden | 已認證但無權限 |
| 404 | Not Found | 資源不存在 |
| 422 | Unprocessable Entity | 驗證失敗 |
| 429 | Too Many Requests | Rate Limiting 觸發 |
| 500 | Internal Server Error | 伺服器錯誤 |

---

## Rate Limiting 規範

### IP 層級限制（Laravel Throttle Middleware）

```php
// routes/api.php
Route::middleware(['throttle:api'])->group(function () {
    Route::post('/contact-requests', [ContactRequestController::class, 'store'])
        ->middleware(['throttle:5,60']); // 每小時 5 次
});
```

### 業務邏輯層級限制

| 限制類型 | 規則 | 實作方式 |
|---------|------|---------|
| 24h 內同業務員 | 1 次 | Database query + Validation |
| 每天跨業務員 | 5 次 | Database query + Validation |
| IP 每小時 | 5 次 | Laravel Throttle Middleware |

---

## 效能要求總覽

| API 端點 | P95 回應時間 | 併發支援 | 快取策略 |
|---------|------------|---------|---------|
| PUT /api/salesperson/profile/contact | < 200ms | 50 req/s | - |
| POST /api/contact-requests | < 500ms | 100 req/s | - |
| GET /api/salesperson/{id}/contact-info | < 100ms | 200 req/s | Redis 5 min |
| POST /api/events/track | < 100ms | 500 req/s | - |
| GET /api/admin/contact-requests | < 300ms | 50 req/s | - |

---

## 安全性要求

### 1. HTTPS 強制使用

所有 API 端點強制使用 HTTPS（Production 環境）

### 2. CSRF 防護

所有 POST/PUT/DELETE 端點自動驗證 CSRF Token (Laravel Middleware)

### 3. XSS 防護

- 所有用戶輸入使用 `strip_tags()` 和 `htmlspecialchars()` 過濾
- 訊息內容不允許 HTML 標籤

### 4. SQL Injection 防護

- 所有 Database Query 使用 Laravel Eloquent ORM
- 不使用 Raw SQL（除非必要且已過濾參數）

### 5. 資料加密

- `customer_email` 欄位使用 Laravel Encryption 加密儲存
- `customer_phone` 欄位使用 Laravel Encryption 加密儲存
- IP 位址使用 SHA256 hash 儲存

---

## 測試案例清單

### PUT /api/salesperson/profile/contact

- [ ] 成功更新所有聯繫方式
- [ ] 成功更新部分聯繫方式（至少 1 項）
- [ ] 失敗：未登入（401）
- [ ] 失敗：非 salesperson 角色（403）
- [ ] 失敗：未提供任何聯繫方式（422）
- [ ] 失敗：電話格式錯誤（422）
- [ ] 失敗：Email 格式錯誤（422）
- [ ] 失敗：LINE ID 格式錯誤（422）
- [ ] 失敗：WeChat ID 格式錯誤（422）

### POST /api/contact-requests

- [ ] 成功提交聯繫請求
- [ ] 成功提交（無電話）
- [ ] 失敗：未登入（401）
- [ ] 失敗：業務員不存在（422）
- [ ] 失敗：業務員未 approved（422）
- [ ] 失敗：24h 內已聯繫過（422）
- [ ] 失敗：今日已聯繫 5 次（422）
- [ ] 失敗：訊息少於 10 字（422）
- [ ] 失敗：訊息超過 500 字（422）
- [ ] 失敗：IP 頻率限制（429）
- [ ] 驗證 Email 已發送（Queue Job）
- [ ] 驗證事件已追蹤（contact_form_submission）

### GET /api/salesperson/{id}/contact-info

- [ ] 成功取得聯繫資訊（有聯繫方式）
- [ ] 成功取得聯繫資訊（無聯繫方式）
- [ ] 失敗：業務員不存在（404）
- [ ] 失敗：業務員未 approved（404）

### POST /api/events/track

- [ ] 成功追蹤 profile_view 事件（已登入）
- [ ] 成功追蹤 profile_view 事件（未登入）
- [ ] 成功追蹤 contact_form_submission 事件
- [ ] 失敗：event_type 不正確（422）
- [ ] 失敗：salesperson_id 不存在（422）
- [ ] 驗證 IP hash 正確儲存

### GET /api/admin/contact-requests

- [ ] 成功取得列表（分頁）
- [ ] 成功篩選（salesperson_id）
- [ ] 成功篩選（user_id）
- [ ] 成功篩選（status）
- [ ] 成功排序（created_at desc）
- [ ] 失敗：未登入（401）
- [ ] 失敗：非 admin 角色（403）

---

## Changelog

| 版本 | 日期 | 變更內容 |
|------|------|---------|
| 1.0 | 2026-01-23 | 初版 API 規格 |

---

**下一步**: 參考 `data-model.md` 了解資料庫結構
