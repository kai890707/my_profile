# 業務規則規格文檔

**功能**: 聯繫機制功能（Contact Mechanism）
**版本**: 1.0
**最後更新**: 2026-01-23

---

## 概述

本文檔定義「聯繫機制功能」的所有業務規則，包含：
1. 驗證規則（Validation Rules）
2. 授權規則（Authorization Rules）
3. 業務流程規則（Business Flow Rules）
4. 資料完整性規則（Data Integrity Rules）
5. Rate Limiting 規則
6. Email 發送規則

---

## 規則命名規範

**規則編號格式**: `BR-<類別>-<編號>`

**類別代碼**:
- `VR`: Validation Rule（驗證規則）
- `AR`: Authorization Rule（授權規則）
- `BF`: Business Flow Rule（業務流程規則）
- `DI`: Data Integrity Rule（資料完整性規則）
- `RL`: Rate Limiting Rule（頻率限制規則）
- `EM`: Email Rule（Email 規則）

---

## 1. 驗證規則（Validation Rules）

### BR-VR-001: 業務員必須至少提供一種聯繫方式

**描述**: 業務員更新聯繫方式時，`phone`, `email_public`, `line_id`, `wechat_id` 至少一個非空

**實作層級**: 應用層驗證（Laravel Custom Validation）

**實作位置**: `app/Http/Requests/UpdateContactMethodsRequest.php`

**實作程式碼**:
```php
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
```

**錯誤回應**:
```json
{
  "status": "error",
  "message": "驗證失敗",
  "errors": {
    "contact_methods": ["必須至少提供一種聯繫方式（電話、Email、LINE 或 WeChat）"]
  }
}
```

**HTTP 狀態碼**: 422 Unprocessable Entity

---

### BR-VR-002: 電話號碼格式驗證

**描述**: 電話號碼必須符合台灣手機或市話格式

**實作層級**: 應用層驗證（Laravel Regex Validation）

**允許格式**:
- 手機: `0912345678` 或 `0912-345-678`
- 市話: `02-12345678` 或 `04-1234567`

**Regex**:
```
^09\d{8}$|^0\d-\d{7,8}$
```

**實作程式碼**:
```php
'phone' => ['nullable', 'string', 'regex:/^09\d{8}$|^0\d-\d{7,8}$/']
```

**錯誤訊息**:
```
電話格式不正確（手機: 0912345678 或市話: 02-12345678）
```

**測試案例**:
```php
// ✅ 合法
'0912345678'
'0912-345-678'
'02-12345678'
'04-1234567'

// ❌ 不合法
'12345678'          // 缺少區碼
'+886912345678'     // 不支援國際格式
'0912-34-5678'      // 格式錯誤
```

---

### BR-VR-003: Email 格式驗證

**描述**: 公開 Email 必須符合 RFC 5322 標準

**實作層級**: 應用層驗證（Laravel Email Validation）

**實作程式碼**:
```php
'email_public' => ['nullable', 'email', 'max:255']
```

**錯誤訊息**:
```
Email 格式不正確
```

---

### BR-VR-004: LINE ID 格式驗證

**描述**: LINE ID 必須 3-20 字元，僅允許英數字和底線

**實作層級**: 應用層驗證（Laravel Regex Validation）

**Regex**:
```
^[a-zA-Z0-9_]{3,20}$
```

**實作程式碼**:
```php
'line_id' => ['nullable', 'string', 'min:3', 'max:20', 'regex:/^[a-zA-Z0-9_]+$/']
```

**錯誤訊息**:
- `min`: LINE ID 最少 3 個字元
- `max`: LINE ID 最多 20 個字元
- `regex`: LINE ID 格式不正確（僅允許英數字和底線）

**測試案例**:
```php
// ✅ 合法
'abc123'
'my_line_id'
'LINE_ID_2024'

// ❌ 不合法
'ab'              // 少於 3 字元
'line@123'        // 不允許 @
'line-id'         // 不允許減號
'this_is_a_very_long_line_id_123' // 超過 20 字元
```

---

### BR-VR-005: WeChat ID 格式驗證

**描述**: WeChat ID 必須 6-20 字元，僅允許英數字、底線和減號

**實作層級**: 應用層驗證（Laravel Regex Validation）

**Regex**:
```
^[a-zA-Z0-9_-]{6,20}$
```

**實作程式碼**:
```php
'wechat_id' => ['nullable', 'string', 'min:6', 'max:20', 'regex:/^[a-zA-Z0-9_-]+$/']
```

**錯誤訊息**:
- `min`: WeChat ID 最少 6 個字元
- `max`: WeChat ID 最多 20 個字元
- `regex`: WeChat ID 格式不正確（僅允許英數字、底線和減號）

**測試案例**:
```php
// ✅ 合法
'abc123'
'my_wechat'
'wechat-id'
'WeChat_ID_2024'

// ❌ 不合法
'abc12'           // 少於 6 字元
'wechat@123'      // 不允許 @
'wechat.id'       // 不允許點
```

---

### BR-VR-006: 訊息內容長度驗證

**描述**: 聯繫訊息內容必須 10-500 字元

**實作層級**: 應用層驗證（Laravel Min/Max Validation）

**實作程式碼**:
```php
'message' => ['required', 'string', 'min:10', 'max:500']
```

**錯誤訊息**:
- `required`: 請填寫訊息內容
- `min`: 訊息內容最少 10 個字元
- `max`: 訊息內容最多 500 個字元

**理由**:
- **最少 10 字**: 避免無意義的垃圾訊息（例如「你好」）
- **最多 500 字**: 避免過長的訊息影響 Email 閱讀體驗

---

## 2. 授權規則（Authorization Rules）

### BR-AR-001: 業務員只能編輯自己的聯繫方式

**描述**: 業務員更新聯繫方式時，只能更新自己的 `salesperson_profile`

**實作層級**: Controller 層驗證

**實作程式碼**:
```php
public function updateContactMethods(UpdateContactMethodsRequest $request)
{
    $user = auth()->user();

    // 確認是業務員
    if ($user->role !== 'salesperson') {
        abort(403, '僅業務員可以更新聯繫方式');
    }

    // 取得自己的 profile
    $profile = $user->salespersonProfile;

    // 更新聯繫方式
    $profile->update($request->validated());

    return response()->json([...]);
}
```

**錯誤回應**:
```json
{
  "status": "error",
  "message": "僅業務員可以更新聯繫方式",
  "errors": {}
}
```

**HTTP 狀態碼**: 403 Forbidden

---

### BR-AR-002: Admin 可以代為編輯任何業務員的聯繫方式

**描述**: Admin 可以編輯任何業務員的聯繫方式（未來功能）

**實作層級**: Controller 層驗證

**實作程式碼**:
```php
public function updateContactMethodsForSalesperson(
    UpdateContactMethodsRequest $request,
    int $salespersonId
) {
    $user = auth()->user();

    // 確認是 Admin
    if ($user->role !== 'admin') {
        abort(403, '僅管理員可以執行此操作');
    }

    // 取得目標業務員的 profile
    $profile = SalespersonProfile::where('user_id', $salespersonId)->firstOrFail();

    // 更新聯繫方式
    $profile->update($request->validated());

    return response()->json([...]);
}
```

**HTTP 狀態碼**: 403 Forbidden

---

### BR-AR-003: 只有登入用戶可以提交聯繫請求

**描述**: 客戶必須登入才能提交聯繫請求

**實作層級**: Middleware 層驗證

**實作程式碼**:
```php
Route::post('/contact-requests', [ContactRequestController::class, 'store'])
    ->middleware(['auth:sanctum']);
```

**錯誤回應**:
```json
{
  "status": "error",
  "message": "未經授權，請先登入",
  "errors": {}
}
```

**HTTP 狀態碼**: 401 Unauthorized

**前端處理**:
- 未登入用戶點擊「聯繫」按鈕 → 導向登入頁面
- 登入成功後返回業務員檔案頁

---

### BR-AR-004: 只有 Admin 可以查看所有聯繫請求

**描述**: 一般用戶和業務員無法查看聯繫請求列表

**實作層級**: Controller 層驗證

**實作程式碼**:
```php
public function index(Request $request)
{
    $user = auth()->user();

    if ($user->role !== 'admin') {
        abort(403, '僅管理員可以查看此資源');
    }

    // 查詢聯繫請求...
}
```

**HTTP 狀態碼**: 403 Forbidden

---

## 3. 業務流程規則（Business Flow Rules）

### BR-BF-001: 業務員設定聯繫方式流程

**流程步驟**:
1. 業務員登入系統
2. 進入「編輯個人檔案」頁面
3. 填寫聯繫方式（至少一種）
4. 提交表單
5. 系統驗證資料（格式、至少一種聯繫方式）
6. 更新 `salesperson_profiles` 資料表
7. 清除 Redis 快取（如有）
8. 回傳成功訊息

**錯誤處理**:
- 驗證失敗 → 顯示具體錯誤訊息
- 網路錯誤 → 顯示「網路連線失敗，請稍後再試」

---

### BR-BF-002: 客戶聯繫業務員流程

**流程步驟**:
1. 客戶登入系統
2. 瀏覽業務員檔案頁
3. 系統追蹤 `profile_view` 事件
4. 點擊「聯繫」按鈕（開啟 Modal）
5. 填寫訊息內容（姓名、Email 自動填入）
6. 提交表單
7. 系統驗證:
   - 業務員是否為 approved 狀態
   - 24h 內是否已聯繫過
   - 今天是否已聯繫 5 次
   - IP Rate Limiting
8. 建立 `contact_requests` 記錄
9. 追蹤 `contact_form_submission` 事件
10. Dispatch Email Queue Job
11. 回傳成功訊息
12. 顯示「已成功送出聯繫請求！業務員將透過 Email 與您聯繫。」

**Side Effects**:
- 建立 `ContactRequest` 記錄
- 建立 `ContactEvent` 記錄
- 發送 Email 給業務員（非同步）

---

### BR-BF-003: Email 通知業務員流程

**觸發時機**: 客戶成功提交聯繫請求

**流程步驟**:
1. Controller Dispatch `SendContactRequestNotification` Job
2. Job 加入 Redis Queue
3. Queue Worker 處理 Job
4. 發送 Email 給業務員（使用 Laravel Mailable）
5. Email 發送成功 → 更新 `contact_requests.email_sent_at`
6. Email 發送失敗 → 自動重試（最多 3 次）

**重試策略**:
- 第 1 次失敗: 等待 1 分鐘後重試
- 第 2 次失敗: 等待 5 分鐘後重試
- 第 3 次失敗: 等待 15 分鐘後重試
- 第 4 次失敗: 記錄到 Failed Jobs（人工處理）

**實作程式碼**:
```php
class SendContactRequestNotification implements ShouldQueue
{
    use Queueable;

    public $tries = 3;
    public $backoff = [60, 300, 900]; // 1min, 5min, 15min

    public function __construct(
        private ContactRequest $contactRequest
    ) {}

    public function handle(): void
    {
        Mail::to($this->contactRequest->salesperson->email)
            ->send(new ContactRequestNotificationMail($this->contactRequest));

        $this->contactRequest->update([
            'email_sent_at' => now()
        ]);
    }
}
```

---

### BR-BF-004: 業務員無聯繫方式時的處理

**條件**: 業務員的 `phone`, `email_public`, `line_id`, `wechat_id` 全部為 NULL

**前端處理**:
- 隱藏「聯繫」按鈕
- 顯示提示訊息:「此業務員尚未提供聯繫方式」

**API 處理**:
- `GET /api/salesperson/{id}/contact-info` 回傳 `has_contact_methods: false`
- 不影響業務員檔案頁的其他內容顯示

---

### BR-BF-005: 業務員未 approved 時的處理

**條件**: 業務員的 `approval_status` 為 `pending` 或 `rejected`

**前端處理**:
- 隱藏「聯繫」按鈕
- 可顯示業務員基本資訊，但不顯示聯繫方式

**API 處理**:
- `GET /api/salesperson/{id}` 回傳 404 Not Found（或回傳資料但不含聯繫方式）
- `POST /api/contact-requests` 驗證失敗，錯誤訊息:「無法聯繫此業務員，業務員不存在或尚未通過審核」

**實作程式碼**:
```php
// 檢查業務員是否為 approved 狀態
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
}
```

---

## 4. 資料完整性規則（Data Integrity Rules）

### BR-DI-001: 級聯刪除業務員時刪除聯繫請求

**描述**: 業務員被刪除時，其收到的所有聯繫請求也一併刪除

**實作層級**: Database Foreign Key Constraint

**實作程式碼**:
```sql
FOREIGN KEY (salesperson_id) REFERENCES users(id) ON DELETE CASCADE
```

**理由**: 業務員不存在時，其聯繫請求已無意義

---

### BR-DI-002: 軟刪除客戶時保留聯繫請求（設為 NULL）

**描述**: 客戶被刪除時，其發送的聯繫請求保留（`user_id` 設為 NULL）

**實作層級**: Database Foreign Key Constraint

**實作程式碼**:
```sql
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
```

**理由**: 保留歷史記錄供審計和數據分析

---

### BR-DI-003: IP 位址必須 Hash 後儲存

**描述**: 所有追蹤事件的 IP 位址使用 SHA256 hash 後儲存

**實作層級**: 應用層處理

**實作程式碼**:
```php
$ipHash = hash('sha256', $request->ip());

ContactEvent::create([
    'ip_address_hash' => $ipHash,
    // ...
]);
```

**理由**: 隱私保護（符合 GDPR/PDPA）

---

### BR-DI-004: 客戶個資加密儲存

**描述**: `customer_email` 和 `customer_phone` 必須加密儲存

**實作層級**: Laravel Model Cast

**實作程式碼**:
```php
protected $casts = [
    'customer_email' => 'encrypted',
    'customer_phone' => 'encrypted',
];
```

**理由**: 保護客戶隱私，防止資料庫洩漏

---

## 5. Rate Limiting 規則

### BR-RL-001: IP 層級頻率限制（每小時 5 次）

**描述**: 每個 IP 位址每小時最多提交 5 次聯繫請求

**實作層級**: Laravel Throttle Middleware

**實作程式碼**:
```php
Route::post('/contact-requests', [ContactRequestController::class, 'store'])
    ->middleware(['auth:sanctum', 'throttle:5,60']); // 每小時 5 次
```

**錯誤回應**:
```json
{
  "status": "error",
  "message": "您的操作過於頻繁，請稍後再試",
  "errors": {
    "rate_limit": ["每小時最多提交 5 次"]
  }
}
```

**HTTP 狀態碼**: 429 Too Many Requests

**Header 回傳**:
```
X-RateLimit-Limit: 5
X-RateLimit-Remaining: 0
Retry-After: 3600
```

---

### BR-RL-002: 24 小時內同業務員只能聯繫 1 次

**描述**: 同一客戶對同一業務員 24 小時內只能提交 1 次聯繫請求

**實作層級**: 應用層驗證（Database Query）

**實作程式碼**:
```php
use Carbon\Carbon;

$existingRequest = ContactRequest::where('user_id', $userId)
    ->where('salesperson_id', $salespersonId)
    ->where('created_at', '>=', Carbon::now()->subHours(24))
    ->exists();

if ($existingRequest) {
    $validator->errors()->add(
        'salesperson_id',
        '您已在 24 小時內聯繫過此業務員，請稍後再試'
    );
}
```

**錯誤回應**:
```json
{
  "status": "error",
  "message": "驗證失敗",
  "errors": {
    "salesperson_id": ["您已在 24 小時內聯繫過此業務員，請稍後再試"]
  }
}
```

**HTTP 狀態碼**: 422 Unprocessable Entity

**查詢效能**: 使用 `idx_user_salesperson_created` 索引（< 10ms）

---

### BR-RL-003: 每天最多提交 5 次聯繫請求（跨業務員）

**描述**: 同一客戶每天最多提交 5 次聯繫請求（不限業務員）

**實作層級**: 應用層驗證（Database Query）

**實作程式碼**:
```php
use Carbon\Carbon;

$todayCount = ContactRequest::where('user_id', $userId)
    ->whereDate('created_at', Carbon::today())
    ->count();

if ($todayCount >= 5) {
    $validator->errors()->add(
        'rate_limit',
        '您今日的聯繫次數已達上限（每天最多 5 次），請明天再試'
    );
}
```

**錯誤回應**:
```json
{
  "status": "error",
  "message": "驗證失敗",
  "errors": {
    "rate_limit": ["您今日的聯繫次數已達上限（每天最多 5 次），請明天再試"]
  }
}
```

**HTTP 狀態碼**: 422 Unprocessable Entity

**查詢效能**: 使用 `idx_user_created` 索引（< 10ms）

---

## 6. Email 發送規則

### BR-EM-001: Email 使用非同步 Queue 發送

**描述**: 聯繫請求的 Email 通知使用 Laravel Queue 非同步發送

**實作層級**: Queue Job

**實作程式碼**:
```php
// Controller
SendContactRequestNotification::dispatch($contactRequest);

// Job
class SendContactRequestNotification implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        Mail::to($this->contactRequest->salesperson->email)
            ->send(new ContactRequestNotificationMail($this->contactRequest));
    }
}
```

**理由**:
- 不阻塞 API 回應（提升用戶體驗）
- 失敗時可自動重試
- 減少 API 回應時間（< 500ms）

---

### BR-EM-002: Email 發送失敗自動重試 3 次

**描述**: Email 發送失敗時自動重試，最多 3 次

**實作層級**: Queue Job Configuration

**重試間隔**:
- 第 1 次失敗: 等待 1 分鐘
- 第 2 次失敗: 等待 5 分鐘
- 第 3 次失敗: 等待 15 分鐘

**實作程式碼**:
```php
class SendContactRequestNotification implements ShouldQueue
{
    public $tries = 3;
    public $backoff = [60, 300, 900]; // seconds
}
```

**失敗處理**:
- 3 次重試後仍失敗 → 記錄到 `failed_jobs` 資料表
- Admin 可手動重新發送

---

### BR-EM-003: 業務員強制接收通知（MVP 階段）

**描述**: MVP 階段業務員無法關閉 Email 通知

**實作層級**: 不提供關閉選項

**未來規劃**: Phase 2 提供「通知設定」頁面

---

### BR-EM-004: Email 寄件者設定

**描述**: Email 寄件者統一使用 `noreply@yamu.com`

**實作層級**: `.env` 配置

**實作程式碼**:
```env
MAIL_FROM_ADDRESS=noreply@yamu.com
MAIL_FROM_NAME="YAMU 業務員推廣平台"
```

---

### BR-EM-005: Email 包含「回覆客戶」連結

**描述**: Email 內容必須包含 `mailto:` 連結，方便業務員直接回覆

**實作層級**: Email Template

**實作程式碼**:
```php
// Mailable
public function build(): self
{
    $replyUrl = "mailto:{$this->contactRequest->customer_email}?subject=回覆：您的諮詢";

    return $this->markdown('emails.contact-request-notification')
                ->with(['replyUrl' => $replyUrl]);
}
```

**Email Template**:
```markdown
[回覆客戶]({{ $replyUrl }})
```

---

## 7. 安全性規則

### BR-SEC-001: XSS 防護（訊息內容過濾）

**描述**: 訊息內容不允許 HTML 標籤

**實作層級**: 應用層處理

**實作程式碼**:
```php
$message = strip_tags($request->input('message'));
$message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
```

**理由**: 防止 XSS 攻擊

---

### BR-SEC-002: SQL Injection 防護

**描述**: 所有 Database Query 使用 Laravel Eloquent ORM 或 Prepared Statements

**實作層級**: 開發規範

**禁止**:
```php
// ❌ 不安全
$sql = "SELECT * FROM contact_requests WHERE user_id = " . $userId;
DB::select($sql);
```

**允許**:
```php
// ✅ 安全
ContactRequest::where('user_id', $userId)->get();

// ✅ 安全（使用 Prepared Statement）
DB::select('SELECT * FROM contact_requests WHERE user_id = ?', [$userId]);
```

---

### BR-SEC-003: CSRF 防護

**描述**: 所有 POST/PUT/DELETE 端點自動驗證 CSRF Token

**實作層級**: Laravel Middleware（自動啟用）

**前端處理**:
- Next.js API Client 自動帶入 CSRF Token（從 Cookie 讀取）

---

## 8. 效能規則

### BR-PERF-001: 追蹤事件寫入必須 < 100ms

**描述**: 追蹤事件寫入不應阻塞 API 回應

**實作層級**: 應用層優化

**策略**:
- 使用簡單的 INSERT 語句（不使用 JOIN）
- 適當索引加速寫入後的查詢

**測試方法**:
```php
$start = microtime(true);

ContactEvent::track(
    salespersonId: 1,
    eventType: 'profile_view',
    userId: 2,
    ipAddress: '127.0.0.1'
);

$duration = (microtime(true) - $start) * 1000;

$this->assertLessThan(100, $duration); // < 100ms
```

---

### BR-PERF-002: 頻率限制查詢必須 < 50ms

**描述**: 檢查頻率限制的 Database Query 必須 < 50ms

**實作層級**: Database 索引優化

**策略**:
- 使用 `idx_user_salesperson_created` 複合索引
- 使用 `whereDate()` 而非 `whereBetween()`

**測試方法**:
```sql
EXPLAIN SELECT * FROM contact_requests
WHERE user_id = 5 AND salesperson_id = 2
AND created_at >= '2026-01-22 00:00:00';
```

**預期**: `type = ref`, `key = idx_user_salesperson_created`

---

## 9. 測試規則

### BR-TEST-001: 所有業務規則必須有對應測試

**描述**: 每條業務規則都必須有 Feature Test 覆蓋

**實作層級**: Testing

**測試命名規範**:
```php
// tests/Feature/ContactMechanism/ValidationRulesTest.php
public function test_BR_VR_001_at_least_one_contact_method_required()
{
    // 測試 BR-VR-001
}

public function test_BR_RL_002_cannot_contact_same_salesperson_within_24_hours()
{
    // 測試 BR-RL-002
}
```

---

### BR-TEST-002: 測試覆蓋率目標

**描述**: 業務規則測試覆蓋率必須 ≥ 95%

**實作層級**: Testing

**檢查方法**:
```bash
php artisan test --coverage --min=95
```

---

## 10. 業務規則總覽表

| 規則編號 | 類別 | 規則名稱 | 實作層級 | 優先級 |
|---------|------|---------|---------|-------|
| BR-VR-001 | Validation | 至少一種聯繫方式 | 應用層 | High |
| BR-VR-002 | Validation | 電話格式驗證 | 應用層 | High |
| BR-VR-003 | Validation | Email 格式驗證 | 應用層 | High |
| BR-VR-004 | Validation | LINE ID 格式驗證 | 應用層 | High |
| BR-VR-005 | Validation | WeChat ID 格式驗證 | 應用層 | High |
| BR-VR-006 | Validation | 訊息長度驗證 | 應用層 | High |
| BR-AR-001 | Authorization | 業務員編輯自己資料 | 應用層 | High |
| BR-AR-002 | Authorization | Admin 可編輯任何資料 | 應用層 | Medium |
| BR-AR-003 | Authorization | 必須登入才能聯繫 | Middleware | High |
| BR-AR-004 | Authorization | Admin 查看所有請求 | 應用層 | Medium |
| BR-BF-001 | Business Flow | 設定聯繫方式流程 | 應用層 | High |
| BR-BF-002 | Business Flow | 聯繫業務員流程 | 應用層 | High |
| BR-BF-003 | Business Flow | Email 通知流程 | Queue | High |
| BR-BF-004 | Business Flow | 無聯繫方式處理 | 前端 | Medium |
| BR-BF-005 | Business Flow | 未 approved 處理 | 應用層 | High |
| BR-DI-001 | Data Integrity | 級聯刪除聯繫請求 | Database | High |
| BR-DI-002 | Data Integrity | 保留歷史記錄 | Database | High |
| BR-DI-003 | Data Integrity | IP Hash 儲存 | 應用層 | High |
| BR-DI-004 | Data Integrity | 個資加密儲存 | Model | High |
| BR-RL-001 | Rate Limiting | IP 頻率限制 | Middleware | High |
| BR-RL-002 | Rate Limiting | 24h 內不重複聯繫 | 應用層 | High |
| BR-RL-003 | Rate Limiting | 每天最多 5 次 | 應用層 | High |
| BR-EM-001 | Email | 非同步發送 | Queue | High |
| BR-EM-002 | Email | 自動重試 3 次 | Queue | High |
| BR-EM-003 | Email | 強制接收通知 | - | Low |
| BR-EM-004 | Email | 寄件者設定 | Config | High |
| BR-EM-005 | Email | 包含回覆連結 | Template | Medium |
| BR-SEC-001 | Security | XSS 防護 | 應用層 | High |
| BR-SEC-002 | Security | SQL Injection 防護 | 開發規範 | High |
| BR-SEC-003 | Security | CSRF 防護 | Middleware | High |
| BR-PERF-001 | Performance | 追蹤寫入 < 100ms | 應用層 | Medium |
| BR-PERF-002 | Performance | 頻率查詢 < 50ms | Database | Medium |

---

## Changelog

| 版本 | 日期 | 變更內容 |
|------|------|---------|
| 1.0 | 2026-01-23 | 初版業務規則規格 |

---

**下一步**: 參考 `architecture.md` 了解系統架構設計
