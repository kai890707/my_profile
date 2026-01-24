# 系統架構規格文檔

**功能**: 聯繫機制功能（Contact Mechanism）
**版本**: 1.0
**最後更新**: 2026-01-23

---

## 概述

本文檔定義「聯繫機制功能」的系統架構設計，包含：
1. 系統架構圖
2. 技術棧選擇
3. 分層架構設計
4. Email Queue 架構
5. Rate Limiting 架構
6. 效能優化策略
7. 安全性設計
8. 部署架構

---

## 1. 系統架構圖

### 1.1 整體架構

```
┌─────────────────────────────────────────────────────────────┐
│                         Client Layer                        │
│  ┌──────────────┐        ┌──────────────┐                   │
│  │   Browser    │        │    Mobile    │                   │
│  │  (Next.js)   │        │    (React)   │                   │
│  └──────┬───────┘        └──────┬───────┘                   │
└─────────┼──────────────────────┼─────────────────────────────┘
          │                      │
          │  HTTPS (JWT Auth)    │
          │                      │
┌─────────▼──────────────────────▼─────────────────────────────┐
│                      API Gateway Layer                       │
│  ┌────────────────────────────────────────────────────────┐  │
│  │                    Nginx / Load Balancer               │  │
│  │  - HTTPS Termination                                   │  │
│  │  - Rate Limiting (Global)                              │  │
│  │  - Request Routing                                     │  │
│  └────────────────────────────────────────────────────────┘  │
└──────────────────────────┬───────────────────────────────────┘
                           │
┌──────────────────────────▼───────────────────────────────────┐
│                    Application Layer                         │
│  ┌────────────────────────────────────────────────────────┐  │
│  │              Laravel 11 Application                    │  │
│  │                                                        │  │
│  │  ┌──────────────┐  ┌──────────────┐  ┌─────────────┐ │  │
│  │  │  Controller  │  │   Service    │  │ Repository  │ │  │
│  │  │    Layer     │─>│    Layer     │─>│    Layer    │ │  │
│  │  └──────────────┘  └──────────────┘  └─────────────┘ │  │
│  │                                                        │  │
│  │  Middleware:                                           │  │
│  │  - Authentication (Sanctum)                            │  │
│  │  - Authorization (Policies)                            │  │
│  │  - Rate Limiting (Throttle)                            │  │
│  │  - CSRF Protection                                     │  │
│  └────────────────────────────────────────────────────────┘  │
└──────────────────────┬──────────────────┬────────────────────┘
                       │                  │
         ┌─────────────┴───┐      ┌───────┴──────────┐
         │                 │      │                  │
┌────────▼────────┐  ┌─────▼──────▼─────┐  ┌────────▼────────┐
│  Database Layer │  │   Cache Layer    │  │   Queue Layer   │
│                 │  │                  │  │                 │
│  ┌───────────┐  │  │  ┌────────────┐ │  │  ┌───────────┐  │
│  │   MySQL   │  │  │  │   Redis    │ │  │  │   Redis   │  │
│  │   8.0     │  │  │  │   7.0      │ │  │  │   Queue   │  │
│  └───────────┘  │  │  │            │ │  │  └─────┬─────┘  │
│                 │  │  │  - Session │ │  │        │        │
│  - salesperson_ │  │  │  - Cache   │ │  │  ┌─────▼─────┐  │
│    profiles     │  │  │  - Rate    │ │  │  │   Queue   │  │
│  - contact_     │  │  │    Limit   │ │  │  │  Worker   │  │
│    requests     │  │  └────────────┘ │  │  └─────┬─────┘  │
│  - contact_     │  │                  │  │        │        │
│    events       │  │                  │  │        │        │
└─────────────────┘  └──────────────────┘  └────────┼────────┘
                                                     │
                                             ┌───────▼───────┐
                                             │  Email SMTP   │
                                             │  (SendGrid)   │
                                             └───────────────┘
```

---

### 1.2 請求流程圖

#### 客戶提交聯繫請求流程

```
┌────────┐                                    ┌────────────┐
│ Client │                                    │   Server   │
└───┬────┘                                    └─────┬──────┘
    │                                               │
    │  POST /api/contact-requests                   │
    │  { salesperson_id, phone, message }           │
    ├──────────────────────────────────────────────>│
    │                                               │
    │                                         ┌─────▼──────┐
    │                                         │ Middleware │
    │                                         │ - Auth     │
    │                                         │ - Throttle │
    │                                         └─────┬──────┘
    │                                               │
    │                                         ┌─────▼──────────┐
    │                                         │   Controller   │
    │                                         │   - Validate   │
    │                                         └─────┬──────────┘
    │                                               │
    │                                         ┌─────▼──────────┐
    │                                         │    Service     │
    │                                         │ - Check rules  │
    │                                         │ - Create record│
    │                                         └─────┬──────────┘
    │                                               │
    │                                         ┌─────▼──────────┐
    │                                         │   Repository   │
    │                                         │ - Insert DB    │
    │                                         └─────┬──────────┘
    │                                               │
    │                                         ┌─────▼──────────┐
    │                                         │  Queue Job     │
    │                                         │ - Dispatch     │
    │                                         └─────┬──────────┘
    │                                               │
    │  201 Created                                  │
    │  { success, data }                            │
    │<──────────────────────────────────────────────┤
    │                                               │
    │                                         ┌─────▼──────────┐
    │                                         │ Queue Worker   │
    │                                         │ - Send Email   │
    │                                         └─────┬──────────┘
    │                                               │
    │                                         ┌─────▼──────────┐
    │                                         │  SMTP Server   │
    │                                         │ - SendGrid     │
    │                                         └────────────────┘
```

---

## 2. 技術棧選擇

### 2.1 Backend 技術棧

| 技術 | 版本 | 選擇理由 |
|------|------|---------|
| **PHP** | 8.4 | 現有專案技術棧 |
| **Laravel** | 11 | 現有專案框架，內建 Queue、Mail、Validation 支援 |
| **MySQL** | 8.0 | 現有資料庫，支援 JSON 欄位、Foreign Key、Transaction |
| **Redis** | 7.0 | 用於 Cache、Queue、Session、Rate Limiting |
| **Laravel Sanctum** | Latest | JWT 雙令牌認證機制 |
| **Laravel Queue** | Latest | 非同步 Email 發送 |
| **Laravel Mail** | Latest | Email 發送與 Template |

---

### 2.2 Frontend 技術棧

| 技術 | 版本 | 選擇理由 |
|------|------|---------|
| **Next.js** | 15 | 現有專案框架，SSR/CSR 支援 |
| **React** | 19 | 現有專案框架 |
| **TypeScript** | Latest | Type-safe 開發 |
| **shadcn/ui** | Latest | 現有 UI 組件庫，提供 Dialog、Form 組件 |
| **React Query** | Latest | API 狀態管理、快取 |
| **Zod** | Latest | 前端表單驗證 |
| **React Hook Form** | Latest | 表單狀態管理 |

---

### 2.3 Email 服務

| 環境 | 服務 | 說明 |
|------|------|------|
| **Development** | Mailtrap | Email 測試，不實際發送 |
| **Staging** | Mailtrap | Email 測試 |
| **Production** | SendGrid | 正式 Email 發送服務 |

**選擇 SendGrid 理由**:
- 免費方案提供 100 封/天（足夠 MVP 階段）
- 穩定可靠（99.99% SLA）
- 提供 API 和 SMTP 兩種方式
- 詳細的發送報告和監控

---

## 3. 分層架構設計

### 3.1 Laravel 分層架構

```
┌─────────────────────────────────────────────────────────────┐
│                      Presentation Layer                     │
│  ┌────────────────────────────────────────────────────────┐ │
│  │  app/Http/Controllers/                                 │ │
│  │  - ContactRequestController.php                        │ │
│  │  - SalespersonProfileController.php                    │ │
│  │  - EventTrackingController.php                         │ │
│  └────────────────────────────────────────────────────────┘ │
└──────────────────────────┬──────────────────────────────────┘
                           │
┌──────────────────────────▼──────────────────────────────────┐
│                      Application Layer                      │
│  ┌────────────────────────────────────────────────────────┐ │
│  │  app/Services/                                         │ │
│  │  - ContactRequestService.php                           │ │
│  │  - RateLimitService.php                                │ │
│  │  - EventTrackingService.php                            │ │
│  └────────────────────────────────────────────────────────┘ │
└──────────────────────────┬──────────────────────────────────┘
                           │
┌──────────────────────────▼──────────────────────────────────┐
│                        Domain Layer                         │
│  ┌────────────────────────────────────────────────────────┐ │
│  │  app/Models/                                           │ │
│  │  - ContactRequest.php                                  │ │
│  │  - ContactEvent.php                                    │ │
│  │  - SalespersonProfile.php (擴充)                       │ │
│  └────────────────────────────────────────────────────────┘ │
└──────────────────────────┬──────────────────────────────────┘
                           │
┌──────────────────────────▼──────────────────────────────────┐
│                    Infrastructure Layer                     │
│  ┌────────────────────────────────────────────────────────┐ │
│  │  app/Repositories/                                     │ │
│  │  - ContactRequestRepository.php                        │ │
│  │  - ContactEventRepository.php                          │ │
│  └────────────────────────────────────────────────────────┘ │
│  ┌────────────────────────────────────────────────────────┐ │
│  │  app/Jobs/                                             │ │
│  │  - SendContactRequestNotification.php                  │ │
│  └────────────────────────────────────────────────────────┘ │
│  ┌────────────────────────────────────────────────────────┐ │
│  │  app/Mail/                                             │ │
│  │  - ContactRequestNotificationMail.php                  │ │
│  └────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────┘
```

---

### 3.2 Controller → Service → Repository 流程

#### 範例：提交聯繫請求

**Controller (`ContactRequestController.php`)**:
```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactRequestRequest;
use App\Services\ContactRequestService;
use Illuminate\Http\JsonResponse;

class ContactRequestController extends Controller
{
    public function __construct(
        private ContactRequestService $contactRequestService
    ) {}

    public function store(StoreContactRequestRequest $request): JsonResponse
    {
        $contactRequest = $this->contactRequestService->createContactRequest(
            userId: auth()->id(),
            salespersonId: $request->input('salesperson_id'),
            phone: $request->input('phone'),
            message: $request->input('message'),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return response()->json([
            'status' => 'success',
            'message' => '聯繫請求已送出，業務員將盡快回覆您',
            'data' => [
                'contact_request' => $contactRequest,
            ],
        ], 201);
    }
}
```

**Service (`ContactRequestService.php`)**:
```php
<?php

namespace App\Services;

use App\Models\ContactRequest;
use App\Models\User;
use App\Jobs\SendContactRequestNotification;
use App\Repositories\ContactRequestRepository;
use App\Repositories\ContactEventRepository;
use Illuminate\Support\Facades\DB;

class ContactRequestService
{
    public function __construct(
        private ContactRequestRepository $contactRequestRepository,
        private ContactEventRepository $contactEventRepository
    ) {}

    public function createContactRequest(
        int $userId,
        int $salespersonId,
        ?string $phone,
        string $message,
        string $ipAddress,
        ?string $userAgent
    ): ContactRequest {
        return DB::transaction(function () use (
            $userId,
            $salespersonId,
            $phone,
            $message,
            $ipAddress,
            $userAgent
        ) {
            // 1. 取得用戶資訊
            $user = User::findOrFail($userId);

            // 2. 建立聯繫請求
            $contactRequest = $this->contactRequestRepository->create([
                'user_id' => $userId,
                'salesperson_id' => $salespersonId,
                'customer_name' => $user->name,
                'customer_email' => $user->email,
                'customer_phone' => $phone,
                'message' => strip_tags($message), // XSS 防護
            ]);

            // 3. 追蹤事件
            $this->contactEventRepository->track(
                salespersonId: $salespersonId,
                eventType: 'contact_form_submission',
                userId: $userId,
                ipAddress: $ipAddress,
                userAgent: $userAgent
            );

            // 4. 發送 Email 通知（非同步）
            SendContactRequestNotification::dispatch($contactRequest);

            return $contactRequest;
        });
    }
}
```

**Repository (`ContactRequestRepository.php`)**:
```php
<?php

namespace App\Repositories;

use App\Models\ContactRequest;

class ContactRequestRepository
{
    public function create(array $data): ContactRequest
    {
        return ContactRequest::create($data);
    }

    public function findById(int $id): ?ContactRequest
    {
        return ContactRequest::with(['user', 'salesperson'])->find($id);
    }

    public function hasContactedWithin24Hours(int $userId, int $salespersonId): bool
    {
        return ContactRequest::where('user_id', $userId)
            ->where('salesperson_id', $salespersonId)
            ->where('created_at', '>=', now()->subHours(24))
            ->exists();
    }

    public function getTodayCount(int $userId): int
    {
        return ContactRequest::where('user_id', $userId)
            ->whereDate('created_at', today())
            ->count();
    }
}
```

---

## 4. Email Queue 架構

### 4.1 Queue 流程圖

```
┌──────────────┐         ┌──────────────┐         ┌──────────────┐
│  Controller  │         │  Queue Job   │         │ Queue Worker │
└──────┬───────┘         └──────┬───────┘         └──────┬───────┘
       │                        │                        │
       │  Dispatch Job          │                        │
       ├───────────────────────>│                        │
       │                        │                        │
       │  Return 201            │  Push to Redis Queue   │
       │<───────────────────────┤                        │
       │                        │                        │
       │                        │                        │
       │                        │  Pop Job from Queue    │
       │                        │<───────────────────────┤
       │                        │                        │
       │                        │  Execute handle()      │
       │                        ├───────────────────────>│
       │                        │                        │
       │                        │  Send Email via SMTP   │
       │                        │───────────────────────>│
       │                        │                        │
       │                        │  Update email_sent_at  │
       │                        │<───────────────────────┤
       │                        │                        │
       │                    ┌───┴───┐                    │
       │                    │Success│                    │
       │                    └───┬───┘                    │
       │                        │                        │
       │                   ┌────┴─────┐                 │
       │                   │  Failed? │                 │
       │                   └────┬─────┘                 │
       │                        │                        │
       │                    Yes │                        │
       │                        │  Retry (1min later)    │
       │                        ├───────────────────────>│
       │                        │                        │
       │                   After 3 retries failed        │
       │                        │  Move to failed_jobs   │
       │                        └───────────────────────>│
```

---

### 4.2 Queue Job 實作

**Job (`SendContactRequestNotification.php`)**:
```php
<?php

namespace App\Jobs;

use App\Models\ContactRequest;
use App\Mail\ContactRequestNotificationMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendContactRequestNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * 最大重試次數
     */
    public int $tries = 3;

    /**
     * 重試間隔（秒）
     */
    public array $backoff = [60, 300, 900]; // 1min, 5min, 15min

    /**
     * Job 超時時間（秒）
     */
    public int $timeout = 120;

    /**
     * 建立新 Job 實例
     */
    public function __construct(
        private ContactRequest $contactRequest
    ) {}

    /**
     * 執行 Job
     */
    public function handle(): void
    {
        // 發送 Email
        Mail::to($this->contactRequest->salesperson->email)
            ->send(new ContactRequestNotificationMail($this->contactRequest));

        // 更新發送時間
        $this->contactRequest->update([
            'email_sent_at' => now(),
        ]);
    }

    /**
     * 處理失敗的 Job
     */
    public function failed(\Throwable $exception): void
    {
        // 記錄錯誤日誌
        \Log::error('Failed to send contact request notification', [
            'contact_request_id' => $this->contactRequest->id,
            'error' => $exception->getMessage(),
        ]);

        // 可選：通知管理員
        // Mail::to('admin@yamu.com')->send(new FailedEmailNotification(...));
    }
}
```

---

### 4.3 Mailable 實作

**Mailable (`ContactRequestNotificationMail.php`)**:
```php
<?php

namespace App\Mail;

use App\Models\ContactRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactRequestNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        private ContactRequest $contactRequest
    ) {}

    public function build(): self
    {
        $replyUrl = sprintf(
            'mailto:%s?subject=%s',
            $this->contactRequest->customer_email,
            urlencode('回覆：您的諮詢')
        );

        return $this
            ->subject("您收到一個新的客戶諮詢 - {$this->contactRequest->customer_name}")
            ->markdown('emails.contact-request-notification')
            ->with([
                'contactRequest' => $this->contactRequest,
                'replyUrl' => $replyUrl,
            ]);
    }
}
```

**Email Template (`resources/views/emails/contact-request-notification.blade.php`)**:
```blade
@component('mail::message')
# 您收到一個新的客戶諮詢

親愛的 {{ $contactRequest->salesperson->name }}，

您在 YAMU 平台收到一個新的客戶諮詢：

## 客戶資訊

- **姓名**: {{ $contactRequest->customer_name }}
- **Email**: {{ $contactRequest->customer_email }}
@if($contactRequest->customer_phone)
- **電話**: {{ $contactRequest->customer_phone }}
@endif
- **諮詢時間**: {{ $contactRequest->created_at->format('Y-m-d H:i') }}

## 訊息內容

{{ $contactRequest->message }}

@component('mail::button', ['url' => $replyUrl])
回覆客戶
@endcomponent

---

此 Email 由系統自動發送，請勿直接回覆。

感謝您使用 YAMU 業務員推廣平台

{{ config('app.name') }}
@endcomponent
```

---

### 4.4 Queue 配置

**`.env` 設定**:
```env
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_DB=0
```

**Queue Worker 啟動**:
```bash
# 開發環境
php artisan queue:work --tries=3 --timeout=120

# 生產環境（使用 Supervisor）
php artisan queue:work redis --tries=3 --timeout=120 --sleep=3 --max-jobs=1000
```

---

## 5. Rate Limiting 架構

### 5.1 多層級 Rate Limiting

```
┌─────────────────────────────────────────────────────────────┐
│                Layer 1: Nginx Rate Limiting                 │
│  - Global IP 限制: 100 req/s                                 │
│  - 防止 DDoS 攻擊                                            │
└──────────────────────────┬──────────────────────────────────┘
                           │
┌──────────────────────────▼──────────────────────────────────┐
│           Layer 2: Laravel Throttle Middleware              │
│  - API 層級限制: 5 req/hour (POST /contact-requests)         │
│  - 使用 Redis 計數                                           │
└──────────────────────────┬──────────────────────────────────┘
                           │
┌──────────────────────────▼──────────────────────────────────┐
│         Layer 3: Business Logic Rate Limiting               │
│  - 24h 內同業務員: 1 次                                      │
│  - 每天跨業務員: 5 次                                        │
│  - 使用 Database 查詢驗證                                    │
└─────────────────────────────────────────────────────────────┘
```

---

### 5.2 Laravel Throttle Middleware

**路由設定**:
```php
Route::middleware(['auth:sanctum', 'throttle:contact-requests'])
    ->post('/contact-requests', [ContactRequestController::class, 'store']);
```

**自訂 Rate Limiter (`app/Providers/RouteServiceProvider.php`)**:
```php
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

protected function boot(): void
{
    RateLimiter::for('contact-requests', function (Request $request) {
        return Limit::perHour(5)->by($request->ip());
    });
}
```

---

### 5.3 Business Logic Rate Limiting

**RateLimitService (`app/Services/RateLimitService.php`)**:
```php
<?php

namespace App\Services;

use App\Models\ContactRequest;
use Carbon\Carbon;

class RateLimitService
{
    /**
     * 檢查 24 小時內是否已聯繫過此業務員
     */
    public function hasContactedWithin24Hours(int $userId, int $salespersonId): bool
    {
        return ContactRequest::where('user_id', $userId)
            ->where('salesperson_id', $salespersonId)
            ->where('created_at', '>=', Carbon::now()->subHours(24))
            ->exists();
    }

    /**
     * 檢查今天是否已達上限（5 次）
     */
    public function hasReachedDailyLimit(int $userId, int $limit = 5): bool
    {
        $count = ContactRequest::where('user_id', $userId)
            ->whereDate('created_at', Carbon::today())
            ->count();

        return $count >= $limit;
    }

    /**
     * 取得下次可聯繫的時間
     */
    public function getNextAvailableTime(int $userId, int $salespersonId): ?Carbon
    {
        $lastRequest = ContactRequest::where('user_id', $userId)
            ->where('salesperson_id', $salespersonId)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$lastRequest) {
            return null;
        }

        return $lastRequest->created_at->addHours(24);
    }
}
```

---

## 6. 效能優化策略

### 6.1 Database 查詢優化

#### 使用 Eager Loading 避免 N+1 問題

**❌ 不好（N+1 問題）**:
```php
$requests = ContactRequest::all();
foreach ($requests as $request) {
    echo $request->salesperson->name; // 每次都查詢一次
    echo $request->user->email;       // 每次都查詢一次
}
// 總查詢次數: 1 + N + N = 2N + 1
```

**✅ 好（使用 Eager Loading）**:
```php
$requests = ContactRequest::with(['salesperson', 'user'])->get();
foreach ($requests as $request) {
    echo $request->salesperson->name;
    echo $request->user->email;
}
// 總查詢次數: 3 (ContactRequest + User + Salesperson)
```

---

#### 使用索引加速查詢

**查詢 1: 檢查 24h 內是否已聯繫過**
```sql
SELECT * FROM contact_requests
WHERE user_id = ? AND salesperson_id = ? AND created_at >= ?;

-- 使用索引: idx_user_salesperson_created
-- 查詢時間: < 10ms
```

**查詢 2: 業務員查詢未回覆的請求**
```sql
SELECT * FROM contact_requests
WHERE salesperson_id = ? AND status = 'pending'
ORDER BY created_at DESC;

-- 使用索引: idx_salesperson_status_created
-- 查詢時間: < 20ms
```

---

### 6.2 快取策略

#### 業務員聯繫方式快取

**快取設定**:
- **Cache Key**: `salesperson:{id}:contact_info`
- **TTL**: 5 minutes
- **Cache Backend**: Redis

**實作**:
```php
use Illuminate\Support\Facades\Cache;

public function getContactInfo(int $salespersonId): array
{
    return Cache::remember(
        "salesperson:{$salespersonId}:contact_info",
        300, // 5 minutes
        function () use ($salespersonId) {
            $profile = SalespersonProfile::where('user_id', $salespersonId)->first();

            return [
                'phone' => $profile->phone,
                'email_public' => $profile->email_public,
                'line_id' => $profile->line_id,
                'wechat_id' => $profile->wechat_id,
                'contact_preferences' => $profile->contact_preferences,
                'has_contact_methods' => $profile->hasContactMethods(),
            ];
        }
    );
}
```

**快取失效**:
```php
// 業務員更新聯繫方式時清除快取
Cache::forget("salesperson:{$salespersonId}:contact_info");
```

---

### 6.3 Database Connection Pool

**配置 (`config/database.php`)**:
```php
'mysql' => [
    'driver' => 'mysql',
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', '3306'),
    'database' => env('DB_DATABASE', 'yamu'),
    'username' => env('DB_USERNAME', 'root'),
    'password' => env('DB_PASSWORD', ''),
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
    'strict' => true,
    'engine' => null,
    'options' => extension_loaded('pdo_mysql') ? array_filter([
        PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
        PDO::ATTR_PERSISTENT => true, // 持久連接
    ]) : [],
],
```

---

## 7. 安全性設計

### 7.1 資料加密

#### Customer Email/Phone 加密

**加密方式**: Laravel Encryption (AES-256-CBC)

**實作**:
```php
// Model
protected $casts = [
    'customer_email' => 'encrypted',
    'customer_phone' => 'encrypted',
];

// 自動加密/解密
$request = ContactRequest::create([
    'customer_email' => 'customer@example.com', // 自動加密
]);

echo $request->customer_email; // 自動解密: customer@example.com
```

**加密 Key 管理**:
```env
APP_KEY=base64:xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx=
```

⚠️ **重要**: 絕對不要提交 `APP_KEY` 到 Git

---

#### IP 位址 Hash

**Hash 方式**: SHA256

**實作**:
```php
$ipHash = hash('sha256', $request->ip());

ContactEvent::create([
    'ip_address_hash' => $ipHash,
    // ...
]);
```

**優點**:
- 不可逆（無法從 hash 反推原始 IP）
- 相同 IP 產生相同 hash（可追蹤行為模式）
- 符合 GDPR/PDPA 隱私要求

---

### 7.2 XSS 防護

**訊息內容過濾**:
```php
$message = $request->input('message');
$message = strip_tags($message);           // 移除 HTML 標籤
$message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); // HTML Entities Encode
```

**Blade Template 自動轉義**:
```blade
{{ $contactRequest->message }}  <!-- 自動 HTML Entities Encode -->
{!! $contactRequest->message !!} <!-- 不轉義（危險，避免使用） -->
```

---

### 7.3 SQL Injection 防護

**使用 Eloquent ORM**:
```php
// ✅ 安全（使用 Eloquent）
ContactRequest::where('user_id', $userId)->get();

// ✅ 安全（使用 Prepared Statement）
DB::select('SELECT * FROM contact_requests WHERE user_id = ?', [$userId]);

// ❌ 危險（Raw SQL + 字串拼接）
DB::select("SELECT * FROM contact_requests WHERE user_id = $userId");
```

---

### 7.4 CSRF 防護

**Laravel Middleware**:
```php
// 自動啟用 CSRF 防護
'web' => [
    \App\Http\Middleware\VerifyCsrfToken::class,
],
```

**Next.js API Client**:
```typescript
// 自動從 Cookie 讀取 CSRF Token
axios.post('/api/contact-requests', data, {
  headers: {
    'X-CSRF-TOKEN': getCsrfToken(),
  },
});
```

---

## 8. 部署架構

### 8.1 開發環境

```
┌─────────────────────────────────────────┐
│         Docker Compose (Local)          │
│                                         │
│  ┌────────────┐  ┌────────────┐        │
│  │   Laravel  │  │  Next.js   │        │
│  │   (8080)   │  │   (3001)   │        │
│  └────────────┘  └────────────┘        │
│                                         │
│  ┌────────────┐  ┌────────────┐        │
│  │   MySQL    │  │   Redis    │        │
│  │   (3307)   │  │   (6379)   │        │
│  └────────────┘  └────────────┘        │
│                                         │
│  ┌────────────┐                        │
│  │ Queue Worker│                       │
│  └────────────┘                        │
└─────────────────────────────────────────┘
```

**啟動指令**:
```bash
docker-compose up -d
php artisan queue:work
npm run dev
```

---

### 8.2 生產環境（建議）

```
┌─────────────────────────────────────────────────────────────┐
│                        Cloud (AWS/GCP)                      │
│                                                             │
│  ┌──────────────────────────────────────────────────────┐  │
│  │                Load Balancer (ALB/GCLB)              │  │
│  │  - HTTPS Termination                                 │  │
│  │  - Rate Limiting                                     │  │
│  └───────────────────────┬──────────────────────────────┘  │
│                          │                                  │
│       ┌──────────────────┴───────────────────┐              │
│       │                                      │              │
│  ┌────▼────────┐                   ┌─────────▼──────┐      │
│  │ Laravel App │                   │   Next.js App  │      │
│  │   (ECS/GKE) │                   │   (Vercel/GKE) │      │
│  │  - Auto Scale│                  │   - CDN        │      │
│  └────┬────────┘                   └────────────────┘      │
│       │                                                     │
│       │                                                     │
│  ┌────▼────────────────────────────────┐                   │
│  │         Managed Services            │                   │
│  │  ┌────────────┐  ┌────────────┐    │                   │
│  │  │  RDS/Cloud │  │ElastiCache/│    │                   │
│  │  │    SQL     │  │MemoryStore │    │                   │
│  │  │  (MySQL)   │  │  (Redis)   │    │                   │
│  │  └────────────┘  └────────────┘    │                   │
│  └─────────────────────────────────────┘                   │
│                                                             │
│  ┌─────────────────────────────────────┐                   │
│  │         Queue Workers (ECS/GKE)     │                   │
│  │  - Auto Scale                       │                   │
│  │  - Health Check                     │                   │
│  └─────────────────────────────────────┘                   │
│                                                             │
│  ┌─────────────────────────────────────┐                   │
│  │      External Services              │                   │
│  │  - SendGrid (Email)                 │                   │
│  │  - Sentry (Error Tracking)          │                   │
│  │  - Datadog (Monitoring)             │                   │
│  └─────────────────────────────────────┘                   │
└─────────────────────────────────────────────────────────────┘
```

---

### 8.3 CI/CD Pipeline

```
┌──────────┐     ┌──────────┐     ┌──────────┐     ┌──────────┐
│   Push   │────>│  GitHub  │────>│   CI/CD  │────>│  Deploy  │
│   Code   │     │  Actions │     │  Tests   │     │  (AWS)   │
└──────────┘     └──────────┘     └──────────┘     └──────────┘
                                       │
                                       ├─> PHPUnit Tests
                                       ├─> PHPStan Analysis
                                       ├─> Database Migrations
                                       ├─> Build Docker Image
                                       └─> Deploy to ECS/GKE
```

**GitHub Actions 範例**:
```yaml
name: Deploy to Production

on:
  push:
    branches: [main]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Run Tests
        run: |
          composer install
          php artisan test
          vendor/bin/phpstan analyse

  deploy:
    needs: test
    runs-on: ubuntu-latest
    steps:
      - name: Deploy to AWS ECS
        run: |
          # Deploy Laravel
          # Deploy Queue Worker
          # Run Database Migrations
```

---

## 9. 監控與告警

### 9.1 監控指標

| 指標類別 | 監控項目 | 工具 | 告警閾值 |
|---------|---------|------|---------|
| **API 效能** | P95 回應時間 | Datadog | > 500ms |
| **Queue** | Queue Size | Laravel Horizon | > 100 jobs |
| **Queue** | Failed Jobs | Laravel Horizon | > 0 jobs |
| **Email** | 發送成功率 | SendGrid | < 95% |
| **Database** | Query Time | Datadog | > 100ms |
| **Database** | Connection Pool | Datadog | > 80% |
| **Redis** | Memory Usage | Datadog | > 80% |
| **Error** | 5xx Error Rate | Sentry | > 1% |

---

### 9.2 日誌收集

**Laravel Log Channels**:
```php
'channels' => [
    'stack' => [
        'driver' => 'stack',
        'channels' => ['daily', 'sentry'],
    ],

    'daily' => [
        'driver' => 'daily',
        'path' => storage_path('logs/laravel.log'),
        'level' => 'debug',
        'days' => 14,
    ],

    'sentry' => [
        'driver' => 'sentry',
    ],
],
```

**重要事件日誌**:
```php
Log::info('Contact request created', [
    'contact_request_id' => $contactRequest->id,
    'salesperson_id' => $salespersonId,
    'user_id' => $userId,
]);

Log::error('Failed to send email', [
    'contact_request_id' => $contactRequest->id,
    'error' => $exception->getMessage(),
]);
```

---

## 10. 災難恢復

### 10.1 備份策略

| 資料類型 | 備份頻率 | 保留期限 | 儲存位置 |
|---------|---------|---------|---------|
| **Database** | 每天 (自動) | 30 天 | AWS S3 |
| **Redis** | 每 6 小時 | 7 天 | AWS S3 |
| **Application Logs** | 即時 | 14 天 | CloudWatch |
| **Email Logs** | 即時 | 90 天 | SendGrid |

---

### 10.2 RTO/RPO

| 項目 | 目標 |
|------|------|
| **RTO** (Recovery Time Objective) | < 2 hours |
| **RPO** (Recovery Point Objective) | < 1 hour |

---

## Changelog

| 版本 | 日期 | 變更內容 |
|------|------|---------|
| 1.0 | 2026-01-23 | 初版系統架構規格 |

---

**完成**: 所有技術規格文檔已完成，可進入 Implementation 階段
